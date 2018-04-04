<?php
namespace app\ticket\model;

use app\other\model\TuleModel;

class TicketModel extends TuleModel
{
    protected $pk = 'ticket_id';
    protected $table = 'way_ticket';


}