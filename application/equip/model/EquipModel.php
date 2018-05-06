<?php
namespace app\equip\model;

use app\other\model\TuleModel;

class EquipModel extends TuleModel
{
    protected $pk = 'equip_id';
    protected $table = 'way_equip';

    public function getEquipImgAttr($value){
        return tule_img($value, 2);
    }
}