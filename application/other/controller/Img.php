<?php
namespace app\other\controller;

use app\other\model\AttrModel;
use app\other\model\StorageImagesModel;

class Img
{
    /**
     * 上传图片
     */
    public function upload() {
        $model_attr = new AttrModel();
        $model_storage_images = new StorageImagesModel();

        $request = request();
        $file = $request->file('image');
        $type = intval($request->param('type'));
        if(empty($file) || empty($type)){
            return ['code' => 300, 'message' => '信息错误'];
        }
        //获取文件保存部分路径
        $attr_path = $model_attr->where(['attr_name'=>'img_type_'.$type])->value('attr_value');
        if(empty($attr_path)){
            return ['code' => 300, 'message' => '文件类型不存在'];
        }
        //创建文件保存的目录
        $date = date('Y').'/'.date('m').'/'.date('d');
        if (!is_dir(BASE_DATA_PATH)) {
            mkdir(BASE_DATA_PATH, 0775);
        }
        $this->img_path(BASE_DATA_PATH . $attr_path);
        //保存文件
        $path = BASE_DATA_PATH . $attr_path.$date;
        $info = $file->rule('md5')->move($path);
        if ($info) {
            // 成功上传后 获取上传信息
            $con['image']= $attr_path . $date .'/'. $info->getFilename();
            $url = IMG_DOMAIN . $attr_path . $date .'/'. $info->getFilename();
            $con['abs_image']= $url;
            $con['type']=$type;
            $id = $model_storage_images->save($con);
            if(!$id){
                return ['code' => 300, 'message' => '图片数据入库失败'];
            }
            $data['id'] = $model_storage_images->id;
            $data['url'] = $url;
            return ['code' => 200, 'message' => '上传成功', 'data' => $data];
        } else {
            // 上传失败获取错误信息
            $data = $file->getError();
            return ['code' => 300, 'message' => '保存文件失败', 'data' => $data];
        }
    }
    /**
     * 按日期创建文件夹
     */
    public function img_path($path) {
        $date = date('Y/m/d');
        $y = date('Y');
        $m = date('m');
        //相对路径应以入口文件为准
        $path1 = $path . $y;
        $path2 = $path . $y . '/' . $m;
        $path3 = $path . $date;
        //检测目录是否存在,不存在就创建,并赋予权限
        if (!is_dir($path)) {
            mkdir($path, 0775);
        }
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
