<?php
namespace app\ticket\controller;

use app\other\model\StorageImagesModel;
use app\ticket\model\TicketModel;
use think\Controller;

class Expert extends Controller
{
    private $model_ticket = [];

    public function __construct()
    {
        parent::__construct();
        $this->model_ticket = new TicketModel();
    }

    /**
     * 景区门票列表
     */
    public function Expert_index()
    {
        $ticket_name = input('post.ticket_name');
        if(!empty($ticket_name)){
            $condition['ticket_name'] = ['like', '%'.$ticket_name.'%'];
        }
        //读取列表
        $condition['status'] = 1;
        $field = 'a.ticket_id, a.ticket_name, a.leave_type, a.leave_date, a.real_num, a.is_hot, a.ticket_img';
        $order = 'a.ticket_id desc';
        $res = $this->model_ticket->getListPageInfo($condition, [], $field, $order);
        if(count($res) < 1){
            return return_info(300, '没有更多数据了');
        }
        foreach ($res as $k => &$v) {
            if($v['leave_type'] == 1)$v['leave_date'] = date('Y-m-d', strtotime('+1day', TIMESTAMP));
            unset($v['leave_type']);
        }
        return return_info(200, '景区门票列表', $res);
    }
    /**
     * 门票详情
     */
    public function ticket_detail(){
        $model_storage_images = new StorageImagesModel();

        $ticket_id = input('post.ticket_id');
        if(empty($ticket_id)){
            return return_info();
        }
        $con['ticket_id'] = $ticket_id;
        $con['status'] = 1;
        //ticket_id, ticket_name, scenic_name, price, sale_price, purpose, leave_type, leave_date, delivery_num, real_num
        $res = $this->model_ticket->getInfo($con, [], 'ticket_id, ticket_name, scenic_name, price, sale_price, leave_type, leave_date, delivery_num, real_num, narea, introduce, attention');
        if(!$res){
            return return_info(300, '找不到该门票');
        }
        if($res['leave_type'] == 1)$res['leave_date'] = date('Y-m-d', strtotime('+1day', TIMESTAMP));
        $res['enrolled'] = $res['delivery_num'] - $res['real_num'];
        //门票轮播图
        $data['ticket_id'] = $ticket_id;
        $res['images'] = $model_storage_images->show_img($data, 1);

        unset($res['leave_type'],$res['delivery_num']);
        return return_info(200, '门票详情', $res);
    }
}
