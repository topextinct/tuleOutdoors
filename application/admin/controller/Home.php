<?php
namespace app\admin\controller;

use app\equip\model\EquipModel;
use app\ticket\model\TicketModel;

class Home extends AdminController
{
    public function home_index()
    {
        $model_ticket = new TicketModel();
        $model_equip = new EquipModel();
        $ticket = $model_ticket->where(['status'=>1])->count();
        $equip = $model_equip->where(['status'=>1])->count();
        $member = $model_equip->where(['status'=>1])->count();
        $arr[] = ['title' => '门票管理('.$ticket.')'];
        $arr[] = ['title' => '装备管理('.$equip.')'];
        $arr[] = ['title' => '订单管理(0)'];
        $arr[] = ['title' => '装备管理('.$member.')'];
        return return_info('200', '首页', $arr);
    }
}
