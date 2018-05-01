<?php
namespace app\admin\controller;

use think\Db;
use app\equip\model\ClassModel;
use app\equip\model\EquipModel;
use app\other\model\StorageImagesModel;

class Equip extends AdminController
{
    private $model_equip = [];

    public function __construct()
    {
        parent::__construct();
        $this->model_equip = new EquipModel();
    }
    /**
     * 添加、修改装备
     */
    public function equip_add_update()
    {
        $equip_id = input('post.equip_id');     //修改专用
        $post_data = ['equip_name', 'price', 'purpose', 'classify_id', 'classify_name', 'is_hot', 'is_sale', 'sale_price'];
        //字段检查
        $post_error = parameter_check($post_data, 1);    //1：不能为空
        if ($post_error['code'] != 200) {
            return $post_error;
        }
        $images = json_decode(input('post.images'),1);  //轮播图
        $introduce_images = json_decode(input('post.introduce_images'),1);  //介绍图
        if(empty($images) || count($images) < 1){
            return return_info(300, '请上传轮播图');
        }
        if(empty($introduce_images) || count($introduce_images) < 1){
            return return_info(300, '请上传产品介绍图');
        }
        $data = $post_error['data'];
        $data['sale_price'] = input('post.sale_price'); //特卖价
        //检查是否有重名装备
        if($equip_id)$whe['equip_id'] = ['neq', $equip_id];
        $whe['equip_name'] = $data['equip_name'];
        if($this->model_equip->getInfo($whe)){
            return return_info(300, '已有该装备');
        }

        try{
            Db::startTrans();//开启事务
            if(empty($equip_id)){
                if(!$this->model_equip->save($data)){
                    return return_info(300, '添加失败');
                }
                $equip_id = $this->model_equip->equip_id;
            }else{  //修改
                $equip = $this->model_equip->where(['equip_id'=>$equip_id])->find();
                if(!$equip){
                    return return_info(300, '找不到该门票');
                }
                $this->model_equip->save($data,['equip_id'=>$equip_id]);
            }
//        echo Db::getLastSql();
            $equip_data['equip_id'] = $equip_id;
            //处理轮播图
            $res = $this->equip_images($images, $equip_data, 2);
            if($res['code'] != 200){
                throw new \Exception($res['message']);
            }
            $this->model_equip->equip_img = $res['data'][0]['image'];
            $this->model_equip->save(); //一样的情况下为0，so不判断是否成功
            //处理介绍图
            $res = $this->equip_images($introduce_images, $equip_data, 3);
            if($res['code'] != 200){
                throw new \Exception($res['message']);
            }
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            return ['code'=>300,'message'=>$e->getMessage().'---'.$e->getLine()];
        }
        return return_info(200, '操作成功');
    }
    /**
     * 处理图片
     * @param $images   图片集合
     * @param $data 条件
     * @param $type 2:装备轮播图,3:装备介绍图
     * @return array|mixed
     */
    public function equip_images($images, $data, $type){
        $model_storage_images = new StorageImagesModel();
        //设置原有图片失效
        $where = [];
        $where['type'] = $type;
        $where['equip_id'] =  $data['equip_id'];

        //将原有图片解除关系
        $model_storage_images->save(['status'=>0],$where);
        //绑定新的图片
        $return_data = [];
        foreach ($images as $k=>&$v){
            $img = $model_storage_images->where(['id'=>$v, 'type'=>$type])->field('image')->find();
            $return_data[] = $img;
            if(!$img){
                return return_info(300, '第'.($k+1).'张图片不存在');
            }
            $img->order = $k + 1; //多图片的情况下 处理图片排序
            foreach ($data as $k1 => $v2){
                $img->$k1 = $v2;
            }
            $img->status = 1;
            if(!$img->save()){
                return return_info(300, '绑定第'.($k+1).'张新图片失败');
            }
        }
        return ['code'=>200,'message'=>'保存图片成功','data'=>$return_data];
    }
    /**
     * 装备列表
     */
    public function equip_list(){
        $equip_name = input('equip_name');    //装备名
        $purpose = input('purpose');     //用途
        $classify_name = input('classify_name');    //分类名
        $is_outexcel = input('get.is_outexcel');   //1：导出excel表
        $condition = [];
        if (!empty($equip_name)) {
            $condition['a.equip_name'] = ['like','%'.$equip_name.'%'];
        }
        if (!empty($purpose)) {
            $condition['a.purpose'] = ['like','%'.$purpose.'%'];
        }
        if (!empty($classify_name)) {
            $condition['a.classify_name'] = ['like','%'.$classify_name.'%'];
        }
        $condition['status'] = 1;
        $join = [];
        $field = 'a.equip_name, a.price, a.purpose, a.classify_name';
        $order = 'a.equip_id desc';
        if ($is_outexcel == 1) {  //导出
            $arr['list'] = $this->model_equip->getListInfo($condition, $join, $field, $order);
        } else {
            $field .= ', a.equip_id';
            $list = $this->model_equip->getListPageTotalInfo($condition, $join, $field, $order);
            $arr['list'] = $list->all();
            $arr['total'] = $list->total();  //总数
            $arr['last_page'] = (int)ceil($list->total() / 20);  //获取最后一页数据
        }
        if (count($arr['list']) < 1) {
            return return_info('300', '没有更多数据了');
        }
        if ($is_outexcel == 1) {  //导出
            $arr1[] = '装备名';
            $arr1[] = '价格';
            $arr1[] = '用途';
            $arr1[] = '分类名';
            createExcel($arr1, $arr['list'], '装备列表');
        } else {
            return return_info(200, '装备列表', $arr);
        }
    }
    /**
     * 删除装备
     */
    public function equip_del()
    {
        $equip_id = input('post.equip_id');
        if (empty($equip_id)) {
            return return_info();
        }
        if ($this->model_equip->save(['status'=>70], ['equip_id'=>$equip_id])) {
//            echo Db::getLastSql();
            return return_info(200, '删除成功');
        } else {
            return return_info(200, '删除失败');
        }
    }
    /**
     * 装备详情
     */
    public function equip_detail()
    {
        $model_storage_images = new StorageImagesModel();
        $equip_id = input('post.equip_id');
        if (empty($equip_id)) {
            return return_info();
        }
        $res = $this->model_equip->getInfo(['equip_id'=>$equip_id, 'status'=>1]);
        if (!$res) {
            return return_info(300, '找不到该门票');
        }
        unset($res['equip_img'], $res['status']);
        $data['equip_id'] = $equip_id;
        $res['images'] = $model_storage_images->show_img($data, 2);
        $res['introduce_images'] = $model_storage_images->show_img($data, 3);
        return return_info(200, '装备详情', $res);
    }
    /**
     * 添加、修改分类
     */
    public function class_add_update(){
        $model_class = new ClassModel();
        $class_id = input('post.class_id');
        $class_name = input('post.class_name');
        $sort = input('post.sort');

        $data['class_name'] = $class_name;
        $data['sort'] = $sort;
        if($class_id){
            $class['class_id'] = ['neq', $class_id];
        }
        $class['class_name'] = $class_name;
        if($model_class->getInfo($class)){
            return return_info(300, '该分类已存在');
        }
        if(empty($class_id)){   //添加
            $res = $model_class->save($data);
        }else{  //修改
            $res = $model_class->save($data, ['class_id'=>$class_id]);
        }
        if($res){
            return return_info(200, '操作成功');
        }else{
            return return_info(300, '操作失败');
        }
    }
    /**
     * 分类列表
     */
    public function class_list(){
        $model_class = new ClassModel();
        $res = $model_class->getListInfo([],[], '*', 'class_id desc');

        return return_info(200, '分类列表', $res);
    }



}
