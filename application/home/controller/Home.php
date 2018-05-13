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
        $list = $this->home_list();
        if($list['code'] == 200){
            $res['list'] = $list['data'];
        }else{
            $res['list'] = [];
        }
        return return_info(200, '首页', $res);
    }
    /**
     * 推荐景点
     */
    public function home_list(){
        //景区列表
        $condition['status'] = 1;
        $condition['is_hot'] = 1;
        $field = 'a.ticket_id, a.ticket_name, a.leave_type, a.leave_date, a.real_num, a.ticket_img';
        $order = 'a.ticket_id desc';
        $res = $this->model_ticket->getListPageInfo($condition, [], $field, $order);
        if(count($res) < 1){
            return return_info(300, '没有更多数据了');
        }
        foreach ($res as $k => &$v) {
            if($v['leave_type'] == 1)$v['leave_date'] = date('Y-m-d', strtotime('+1day', TIMESTAMP));
            unset($v['leave_type']);
        }
        return return_info(200, '推荐景点', $res);
    }

}
