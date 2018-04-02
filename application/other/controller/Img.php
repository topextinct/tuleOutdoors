<?php
namespace app\other\controller;

class Img
{
    public function upload() {
        $request = request();
        $file = $request->file('image');
        $type = intval($request->param('type'));

        //获取文件保存部分路径
        $attr_path = Db::name('shop_attr')->where(['attr_name'=>'img_type_'.$type])->find();
        if(!$attr_path){
            return ['code' => 300, 'message' => '文件类型不存在'];
        }
        $attr_path = $attr_path['attr_value'];//例如：goods/


        //创建文件保存的目录

        if (!is_dir(BASE_DATA_PATH.'/fx/')) {
            mkdir(BASE_DATA_PATH.'/fx/', 0775);
        }

        if (!is_dir(BASE_DATA_PATH . '/fx/' . $attr_path)) {
            mkdir(BASE_DATA_PATH . '/fx/'. $attr_path, 0775);
        }
        $date = date('Y').DS.date('m').DS.date('d');

        $this->img_path(BASE_DATA_PATH . '/fx/' . $attr_path);
        $path = BASE_DATA_PATH . '/fx/' . $attr_path.$date;

        //保存文件
        $info = $file->rule('md5')->move($path);
        if ($info) {
            // 成功上传后 获取上传信息
            $data = [];
            $data['url'] = 'http://112.74.172.160/data/fx/' . $attr_path.$date .DS. $info->getFilename();


            $con['image']= $attr_path.$date .DS. $info->getFilename();
            $con['abs_image']= 'http://112.74.172.160/data/fx/'.$attr_path.$date  .DS. $info->getFilename();
            $con['type']=$type;

            $id = Db::connect('mysql://fxshop:29091061@112.74.172.160:3306/fxshop#utf8')->name('shop_storage_images')->insert($con,0,1);
            if($id == 0){
                return ['code' => 300, 'message' => '图片数据入库失败'];
            }
            $data['id'] = $id;
            return ['code' => 200, 'message' => '上传成功', 'data' => $data];
        } else {
            // 上传失败获取错误信息
            $data = $file->getError();
            return ['code' => 300, 'message' => '保存文件失败', 'data' => $data];
        }
    }

    //按日期创建文件夹
    public function img_path($path) {
        $date = date('Y/m/d');
        $y = date('Y');
        $m = date('m');
        $d = date('d');
        //相对路径应以入口文件为准
        $path = $path;
        $path1 = $path . $y;
        $path2 = $path . $y . DS . $m;
        $path3 = $path . $date;
        //检测目录是否存在,不存在就创建,并赋予权限
//        if (!is_dir($path)) {
//            mkdir($path, 0775);
//        }
        if (!is_dir($path1)) {
            mkdir($path1, 0775);
        }
        if (!is_dir($path2)) {
            mkdir($path2, 0775);
        }
        if (!is_dir($path3)) {
            mkdir($path3, 0775);
        }
        return $date;
    }
}
