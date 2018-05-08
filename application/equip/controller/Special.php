<?php
namespace app\equip\controller;

use app\equip\model\ClassModel;
use app\equip\model\EquipModel;
use app\other\model\StorageImagesModel;
use app\ticket\model\TicketModel;
use think\Controller;

class Special extends Controller
{
    private $model_equip = [];
    private $model_ticket = [];

    public function __construct()
    {
        parent::__construct();
        $this->model_equip = new EquipModel();
        $this->model_ticket = new TicketModel();
    }

    /**
     * 装备专区首页
     */
    public function special_index()
    {
        $model_class = new ClassModel();
        $page_size = input('page_size') ? input('page_size') : WX_PAGE_SIZE;    //每页多少条
        //读取轮播
        $res['banner_image'] = [];
        //读取分类
        $res['class'] = $model_class->getListInfo([], [], 'class_id, class_name', 'class_id desc');
        //读取列表
        $list = $this->equip_list();
        if ($list['code'] == 200) {
            $res['list'] = $list['data'];
        } else {
            $res['list'] = [];
        }

        return return_info('200', '装备专区首页', $res);
    }

    /**
     * 装备列表
     */
    public function equip_list()
    {
        $page_size = input('page_size') ? input('page_size') : WX_PAGE_SIZE;    //每页多少条

        $con['status'] = 1;
        $field = 'equip_id, equip_name, equip_img, price, sale_price, is_hot, purpose';
        $order = 'a.equip_id desc';
        $res = $this->model_equip->getListPageInfo($con, [], $field, $order, $page_size);
        if (count($res) < 1) {
            return return_info(300, '没有更多数据了');
        }
        return return_info(200, '装备列表', $res);
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
        $con['equip_id'] = $equip_id;
        $con['status'] = 1;
        $res = $this->model_equip->getInfo($con, [], 'equip_id, equip_name, price, sale_price, purpose');
        if (!$res) {
            return return_info(300, '找不到该装备');
        }
        $res['images'] = $model_storage_images->show_img(['equip_id' => $equip_id], 2);
        $res['introduce_images'] = $model_storage_images->show_img(['equip_id' => $equip_id], 3);

        return return_info(200, '装备详情', $res);
    }

    /**
     * 特卖专区
     */
    public function special_selling()
    {
        $page_size = input('page_size') ? input('page_size') : WX_PAGE_SIZE;    //每页多少条
        //读取轮播
        $res['banner_image'] = [];
        //门票特卖
        $t_list = $this->ticket_special_list();
        if ($t_list['code'] == 200) {
            $res['t_list'] = $t_list['data'];
        } else {
            $res['t_list'] = [];
        }
        //装备特卖
        $e_list = $this->equip_special_list();
        if ($e_list['code'] == 200) {
            $res['e_list'] = $e_list['data'];
        } else {
            $res['e_list'] = [];
        }
        return return_info('200', '特卖专区', $res);
    }

    /**
     * 门票特卖
     */
    public function ticket_special_list()
    {
        $page_size = input('page_size') ? input('page_size') : WX_PAGE_SIZE;    //每页多少条

        $con['status'] = 1;
        $con['is_sale'] = 1;
        $field = 'ticket_id, ticket_name, ticket_img, price, sale_price, leave_type, leave_date';
        $order = 'a.ticket_id desc';
        $res = $this->model_ticket->getListPageInfo($con, [], $field, $order, $page_size);
        if (count($res) < 1) {
            return return_info(300, '没有更多数据了');
        }
        foreach ($res as $k => &$v) {
            if($v['leave_type'] == 1)$v['leave_date'] = date('Y-m-d', strtotime('+1day', TIMESTAMP));
            unset($v['leave_type']);
            $v = $v->toArray();
        }
        return return_info(200, '门票特卖列表', $res);
    }

    /**
     * 装备特卖
     */
    public function equip_special_list()
    {
        $page_size = input('page_size') ? input('page_size') : WX_PAGE_SIZE;    //每页多少条

        $con['status'] = 1;
        $con['is_sale'] = 1;
        $field = 'equip_id, equip_name, equip_img, price, sale_price';
        $order = 'a.equip_id desc';
        $res = $this->model_equip->getListPageInfo($con, [], $field, $order, $page_size);
        if (count($res) < 1) {
            return return_info(300, '没有更多数据了');
        }
        return return_info(200, '装备特卖列表', $res);
    }





}