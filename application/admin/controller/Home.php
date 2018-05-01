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
        $arr[] = ['title' => '��Ʊ����('.$ticket.')'];
        $arr[] = ['title' => 'װ������('.$equip.')'];
        return return_info('200', '��ҳ�ſ�', $arr);
    }
}
