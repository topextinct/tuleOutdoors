<?php
namespace app\ticket\model;

use app\other\model\TuleModel;

class TicketModel extends TuleModel
{
    protected $pk = 'ticket_id';
    protected $table = 'way_ticket';

    public function getTicketImgAttr($value){
        return tule_img($value, 1);
    }

}