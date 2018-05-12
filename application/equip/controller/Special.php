<?php
namespace app\equip\controller;

use app\equip\model\ClassModel;
use app\equip\model\EquipModel;
use app\other\model\StorageImagesModel;
use think\Controller;

class Special extends Controller
{
    private $model_equip = [];

    public function __construct()
    {
        parent::__construct();
        $this->model_equip = new EquipModel();
    }
    /**
     * 装备专区首页
     */
    public function special_index()
    {
        $model_class = new ClassModel();
        //读取轮播
        $res['banner_image'] = [];
        //读取分类
        $res['class'] = $model_class->getListInfo([], [], 'class_id, class_name', 'class_id desc');
        //读取列表
        $list = $this->equip_list();
        if($list['code'] == 200){
            $res['list'] = $list['data'];
        }else{
            $res['list'] = [];
        }
        return return_info('200', '装备专区首页', $res);
    }
    /**
     * 装备列表
     */
    public function equip_list(){

        $con['status'] = 1;
        $field = 'equip_id, equip_name, equip_img, price, sale_price, is_hot, purpose';
        $order = 'a.equip_id desc';
        $res = $this->model_equip->getListPageInfo($con, [], $field, $order);
        if(count($res) < 1){
            return return_info(300, '没有更多数据了');
        }
        return return_info(200, '装备列表', $res);
    }
    /**
     * 装备详情
     */
    public function equip_detail(){
        $model_storage_images = new StorageImagesModel();
        $equip_id = input('post.equip_id');
        if(empty($equip_id)){
            return return_info();
        }
        $con['equip_id'] = $equip_id;
        $con['status'] = 1;
        $res = $this->model_equip->getInfo($con, [], 'equip_id, equip_name, price, sale_price, purpose');
        if(!$res){
            return return_info(300, '找不到该装备');
        }
        $res['images'] = $model_storage_images->show_img(['equip_id'=>$equip_id], 2);
        $res['introduce_images'] = $model_storage_images->show_img(['equip_id'=>$equip_id], 3);

        return return_info(200, '装备详情', $res);
    }

}