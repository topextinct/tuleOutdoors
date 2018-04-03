<?php

namespace app\admin\controller;

use think\db;
use app\other\model\StorageImagesModel;
use app\admin\model\TicketModel;

class Ticket extends AdminController
{
    private $model_ticket = [];

    public function __construct()
    {
        parent::__construct();
        $this->model_ticket = new TicketModel;
    }
    /**
     * 添加/修改门票
     */
    public function ticket_add_update()
    {
        $model_storage_images = new StorageImagesModel();
        $post_data = ['ticket_name', 'scenic_name', 'narea', 'delivery_num', 'price', 'leave_type', 'introduce', 'attention', 'is_hot', 'is_sale', 'sale_price'];
        //字段检查
        $post_error = parameter_check($post_data, 1);    //1：不能为空
        if ($post_error['code'] != 200) {
            return $post_error;
        }
        $images = json_decode(input('post.images'),1);
        if(empty($images) || count($images) < 1){
            return return_info(300, '请上传图片');
        }
        $data = $post_error['data'];
        $data['leave_date'] = input('post.leave_date') ? input('post.leave_date') : '';
        $ticket_id = input('post.ticket_id');
        if(empty($ticket_id)){
            $data['real_num'] = $data['delivery_num'];
            if(!$this->model_ticket->save($data)){
                return return_info(300, '添加门票失败');
            }
            $ticket_id = $this->model_ticket->ticket_id;
        }else{  //修改
            $ticket = $this->model_ticket->where(['ticket_id'=>$ticket_id])->value('delivery_num');
            if(!$ticket){
                return return_info(300, '找不到该门票');
            }
            //实时数 = 实时数 + (new - 基础数)
            $data['real_num'] = ['exp', 'real_num + ' . $data['delivery_num'] . '-'. $ticket];
            $this->model_ticket->save($data,['ticket_id'=>$ticket_id]);
//            echo Db::getLastSql();
        }
        //处理门票图片
        $res = $model_storage_images->handle_images($images, ['ticket_id'=>$ticket_id], 1);
        if($res['code'] != 200){
            $this->model_ticket->where(['ticket_id'=>$ticket_id])->delete();
            return $res;
        }
        $this->model_ticket->ticket_img = $res['data'][0]['image'];
        $this->model_ticket->save();
        return return_info(200, '操作成功');
    }
    /**
     * 门票列表
     */
    public function ticket_list(){
        $is_outexcel = input('get.is_outexcel');   //1：导出excel表
        $condition['status'] = 1;
        $join = [];
        $field = 'a.leave_type, a.leave_date, a.ticket_name, a.price, a.scenic_name, a.delivery_num, a.real_num, a.narea';
        $order = 'a.ticket_id desc';
        if ($is_outexcel == 1) {  //导出
            $arr['list'] = $this->model_ticket->getListInfo($condition, $join, $field, $order);
        } else {
            $field .= ', a.ticket_id';
            $list = $this->model_ticket->getListPageTotalInfo($condition, $join, $field, $order);
            $arr['list'] = $list->all();
            $arr['total'] = $list->total();  //获取最后一页数据
            $arr['last_page'] = (int)ceil($list->total() / 20);  //获取最后一页数据
        }
        if (count($arr['list']) < 1) {
            return return_info('300', '没有更多数据了');
        }
        foreach ($arr['list'] as $k => &$v) {
            if($v['leave_type'] == 1)$v['leave_date'] = date('Y-m-d', TIMESTAMP);
        }
        if ($is_outexcel == 1) {  //导出
            $arr1[] = '';
            createExcel($arr1, $arr['list']->toArray(), '门票列表');
        } else {
            return return_info(200, '门票列表', $arr);
        }
    }
    /**
     * 删除门票
     */
    public function ticket_del()
    {
        $ticket_id = input('post.ticket_id');
        if (empty($ticket_id)) {
            return return_info();
        }
        if ($this->model_ticket->save(['status'=>70], ['ticket_id'=>$ticket_id])) {
//            echo Db::getLastSql();
            return return_info(200, '删除成功');
        } else {
            return return_info(200, '删除失败');
        }
    }
    /**
     * 门票详情
     */
    public function ticket_detail()
    {
        $model_storage_images = new StorageImagesModel();
        $ticket_id = input('post.ticket_id');
        if (empty($ticket_id)) {
            return return_info();
        }
        $res = $this->model_ticket->getInfo(['ticket_id'=>$ticket_id, 'status'=>1]);
        if (!$res) {
            return return_info(300, '找不到该门票');
        }
        unset($res['ticket_img']);
        $data['ticket_id'] = $ticket_id;
        $res['images'] = $model_storage_images->show_img($data, 1);
        return return_info(200, '门票详情', $res);
    }











}
