<?php
namespace app\admin\controller;

use app\admin\model\TicketModel;

class Ticket
{
    public function ticket_add()
    {
        $model_ticket = new TicketModel();
        $post_data = ['ticket_name','scenic_spots','city','delivery_num','price','introduce'];
        //字段检查
        $post_error = parameter_check($post_data,1);    //1：不能为空
        if($post_error['code'] != 200){
            return $post_error;
        }
        $data = $post_error['data'];
        $data['real_num'] = $data['delivery_num'];
        if($model_ticket->save($data)){
            return return_info(200, '操作成功');
        }else{
            return return_info(300, '操作失败');
        }
    }
}
