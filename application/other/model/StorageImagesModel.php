<?php
namespace app\other\model;

use think\db;

class StorageImagesModel extends TuleModel
{
    protected $pk = 'id';
    protected $table = 'way_storage_images';
    protected $createTime = false;

    /**
     * 绑定图片(添加 更新)
     * @param array $id_arr  需要绑定的图片id  例：[1,2,3]
     * @param array $data   需要绑定的数据     例：['member_id'=>1,'order_id'=>2]
     * @param $type     1:门票轮播图,2:装备轮播图,3:装备介绍图
     * @return array
     */
    public function handle_images(array $id_arr,array $data, $type){
        try{
            //设置原有图片失效
            $where = [];
            $where['type'] = $type;
            switch($type){
                case 1:
                    //门票图片
                    $where['ticket_id'] =  $data['ticket_id'];
                    break;
                case 2:
                case 3:
                    //装备图片
                    $where['ticket_id'] =  $data['ticket_id'];
                    break;
                default :
                    throw new \Exception('图片类型错误');
            }

            Db::startTrans();//开启事务
            //将原有图片解除关系
            self::save(['status'=>0],$where);
            //绑定新的图片
            //绑定关系
            $return_data = [];
            foreach ($id_arr as $k=>&$v){
                $img = $this->where(['id'=>$v, 'type'=>$type])->field('image')->find();
                $return_data[] = $img;
                if(!$img){
                    throw new \Exception('第'.($k+1).'张图片不存在');
                }
                $img->order = $k + 1; //多图片的情况下 处理图片排序
                foreach ($data as $k1 => $v2){
                    $img->$k1 = $v2;
                }
                $img->status = 1;
                if(!$img->save()){
                    throw new \Exception('绑定第'.($k+1).'张新图片失败');
                }
            }
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            return ['code'=>300,'message'=>$e->getMessage().'---'.$e->getLine()];
        }
        return ['code'=>200,'message'=>'保存图片成功','data'=>$return_data];
    }
    /**
     * 展示图片
     * @param array $data
     * @param $type
     * @param array $data
     * @param $type
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function show_img(array $data, $type){
        $data['type'] = $type;
        $arr = $this->where($data)->field('id,image')->select();
        foreach ($arr as $k=>&$v){
            $v['image'] = BASE_DATA_PATH.$v['image'];
        }
        return $arr;
    }



}