<?php

namespace app\admin\controller;

use think\db;
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
     * 添加门票
     */
    public function ticket_add()
    {
        $post_data = ['ticket_name', 'scenic_name', 'city', 'delivery_num', 'price', 'introduce'];
        //字段检查
        $post_error = parameter_check($post_data, 1);    //1：不能为空
        if ($post_error['code'] != 200) {
            return $post_error;
        }
        $data = $post_error['data'];
        $data['real_num'] = $data['delivery_num'];
        if ($this->model_ticket->save($data)) {
            return return_info(200, '操作成功');
        } else {
            return return_info(300, '操作失败');
        }
    }
    /**
     * 门票列表
     */
    public function ticket_list(){
        $is_outexcel = input('get.is_outexcel');   //1：导出excel表
        $condition = [];
        $join = [];
        $field = 'a.ticket_name, a.scenic_name, a.introduce, a.city';
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
        if (db('ticket')->delete($ticket_id)) {
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
        $ticket_id = input('post.ticket_id');
        if (empty($ticket_id)) {
            return return_info();
        }
        if (!$this->model_ticket->get($ticket_id)) {
            return return_info(300, '找不到该门票');
        }

    }
    
}
