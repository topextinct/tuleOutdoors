<?php
namespace app\home\controller;

use app\ticket\model\TicketModel;
use think\Controller;

class Home extends Controller
{
    private $model_ticket = [];

    public function __construct()
    {
        parent::__construct();
        $this->model_ticket = new TicketModel();
    }

    /**
     * 首页
     */
    public function home_index()
    {
        //轮播图
        $res['banner_image'] = [];
        //景区列表
        $condition['status'] = 1;
        $condition['is_hot'] = 1;
        $field = 'a.ticket_id, a.ticket_name, a.leave_type, a.leave_date, a.real_num, a.ticket_img';
        $order = 'a.ticket_id desc';
        $res['list'] = $this->model_ticket->getListPageInfo($condition, [], $field, $order);
        if(count($res['list']) < 1){
            return return_info(300, '没有更多数据了');
        }
        foreach ($res['list'] as $k => &$v) {
            if($v['leave_type'] == 1)$v['leave_date'] = date('Y-m-d', strtotime('+1day', TIMESTAMP));
            unset($v['leave_type']);
        }
        return return_info(200, '景区门票列表', $res);
    }

}
