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
        $arr[] = ['title' => '门票管理('.$ticket.')','url'=>'/ticket/index'];
        $arr[] = ['title' => '装备管理('.$equip.')','url'=>'/equip/index'];
        $arr[] = ['title' => '订单管理(0)','url'=>'/order/index'];
        $arr[] = ['title' => '会员管理('.$member.')','url'=>'/user/index'];
        $arr[] = ['title' => 'banner图管理(0)','url'=>'/banner/index'];
        return return_info('200', '首页', $arr);
    }
}
