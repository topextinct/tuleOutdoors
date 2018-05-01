<?php
namespace app\admin\controller;

use app\member\model\MemberModel;

class Member extends AdminController
{
    private $model_member = [];

    public function __construct()
    {
        parent::__construct();
        $this->model_member = new MemberModel();
    }
    /**
     * 会员列表
     */
    public function member_list(){
        $search_field_name = input('search_field_name');    //member_name 账号；member_truename 姓名；member_mobile 手机号
        $search_field_value = input('search_field_value');
        $is_outexcel = input('get.is_outexcel');   //1：导出excel表
        $condition = [];
        if (!empty($search_field_name) && !empty($search_field_value)) {   //搜索类型及值
            $condition['a.'.$search_field_name] = ['like','%'.$search_field_value.'%'];
        }
//        var_dump($condition);
        $join = [];
        $field = 'a.member_name, a.member_mobile, a.member_truename, a.member_nickname, a.create_time';
        $order = 'a.member_id desc';
        if ($is_outexcel == 1) {  //导出
            $arr['list'] = $this->model_member->getListInfo($condition, $join, $field, $order);
        } else {
            $field .= ', a.member_id, a.member_avatar';
            $list = $this->model_member->getListPageTotalInfo($condition, $join, $field, $order);
            $arr['list'] = $list->all();
            $arr['total'] = $list->total(); //总数
            $arr['last_page'] = (int)ceil($list->total() / 20);  //获取最后一页数据
        }
        if (count($arr['list']) < 1) {
            return return_info('300', '没有更多数据了');
        }
        foreach ($arr['list'] as $k => &$v) {
//        $res['images'] = $model_storage_images->show_img($data, 1);

        }
        if ($is_outexcel == 1) {  //导出
            $arr1[] = '账号';
            $arr1[] = '手机号';
            $arr1[] = '真实姓名';
            $arr1[] = '昵称';
            $arr1[] = '注册时间';
            createExcel($arr1, $arr['list'], '会员列表');
        } else {
            return return_info(200, '会员列表', $arr);
        }
    }











}
