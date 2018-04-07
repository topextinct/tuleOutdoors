<?php
namespace app\equip\model;

use app\other\model\TuleModel;

class ClassModel extends TuleModel
{
    protected $pk = 'class_id';
    protected $table = 'way_class';
    // 关闭自动写入时间戳
    protected $autoWriteTimestamp = false;

}