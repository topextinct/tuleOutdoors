<?php
namespace app\member\model;

use app\other\model\TuleModel;

class MemberModel extends TuleModel
{
    protected $pk = 'member_id';
    protected $table = 'way_member';


}