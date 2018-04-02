<?php
namespace app\admin\controller;

use app\admin\model\EquipModel;

class Equip extends AdminController
{
    private $model_equip = [];

    public function __construct()
    {
        parent::__construct();
        $this->model_equip = new EquipModel();
    }
    /**
     * 添加装备
     */
    public function equip_add()
    {
        $post_data = ['equip_name'];
        //字段检查
        $post_error = parameter_check($post_data, 1);    //1：不能为空
        if ($post_error['code'] != 200) {
            return $post_error;
        }
        $data = $post_error['data'];
        $data['real_num'] = $data['delivery_num'];
        if ($this->model_equip->save($data)) {
            return return_info(200, '操作成功');
        } else {
            return return_info(300, '操作失败');
        }
    }
    /**
     * 装备列表
     */
    public function equip_list(){
        $is_outexcel = input('get.is_outexcel');   //1：导出excel表
        $condition = [];
        $join = [];
        $field = 'a.equip_name, a.scenic_name, a.introduce, a.city';
        $order = 'a.equip_id desc';
        if ($is_outexcel == 1) {  //导出
            $arr['list'] = $this->model_equip->getListInfo($condition, $join, $field, $order);
        } else {
            $field .= ', a.equip_id';
            $list = $this->model_equip->getListPageTotalInfo($condition, $join, $field, $order);
            $arr['list'] = $list->all();
            $arr['total'] = $list->total();  //获取最后一页数据
            $arr['last_page'] = (int)ceil($list->total() / 20);  //获取最后一页数据
        }
        if (count($arr['list']) < 1) {
            return return_info('300', '没有更多数据了');
        }
        foreach ($arr['list'] as $k => &$v) {

        }
        if ($is_outexcel == 1) {  //导出
            $arr1[] = '';
            createExcel($arr1, $arr['list']->toArray(), '装备列表');
        } else {
            return return_info(200, '装备列表', $arr);
        }
    }
    /**
     * 删除装备
     */
    public function equip_del()
    {
        $equip_id = input('post.equip_id');
        if (empty($equip_id)) {
            return return_info();
        }
        if ($this->model_equip->save(['status'=>70], ['equip_id'=>$equip_id])) {
//            echo Db::getLastSql();
            return return_info(200, '删除成功');
        } else {
            return return_info(200, '删除失败');
        }
    }
    /**
     * 装备详情
     */
    public function equip_detail()
    {
        $equip_id = input('post.equip_id');
        if (empty($equip_id)) {
            return return_info();
        }
        if (!$this->model_equip->get($equip_id)) {
            return return_info(300, '找不到该装备');
        }

    }
}
