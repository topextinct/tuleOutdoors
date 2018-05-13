<?php
namespace app\admin\controller;


use app\admin\model\AdminModel;

class Login extends AdminController
{
    public function login()
    {
        $member_name = input('post.member_name');
        $member_password = input('post.member_password');

        $model_admin = new AdminModel();
        //内部管理后台登录
        $admin = $model_admin->getInfo(['admin_name' => $member_name], [], 'admin_id, admin_name, admin_password, admin_gid');
        if ($admin['admin_name'] != $member_name) {
            return return_info('300', '用户名不存在');    //用户名不存在
        }
        if ($admin['admin_password'] != $member_password) {
            return return_info('300', '密码错误');     //密码错误
        }
        return return_info(200, '登录成功');
    }
}
