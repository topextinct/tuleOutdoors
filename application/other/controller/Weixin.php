<?php

namespace app\other\controller;

use think\Db;

use app\member\logic\Token;
use app\member\model\MemberModel;
use app\member\model\MemberTokenModel;
use app\other\logic\ShareProfit;
use think\model\concern\TimeStamp;

class Weixin {

    private $appid = "wx4387dbacb5cca0ad";
    private $appsecret = "993f25dbe1826dbe9eef56a52207093a";

    //get_snsapi_userinfo 获取code后回调地址
    private $redirect_uri = API_URL.'other/weixin/get_snsapi_userinfo';
    private $transfer_url = API_URL.'transfer';

    public function get_snsapi_userinfo(){
        //保存最终回跳地址
        if (isset($_GET['next'])) {
            $next = base64_encode($_GET['next']);
        }else{
            $next = base64_encode(API_URL);
        }
        if (!isset($_GET['code'])) {
            //主动授权
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$this->appid&redirect_uri=$this->redirect_uri&response_type=code&scope=snsapi_userinfo&state=$next#wechat_redirect";
            header('Location: ' . $url);
            exit;
        }else{
            //通过code换取网页授权access_token和openid
            $code = $_GET['code'];
            $state = $_GET['state'];
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->appid&secret=$this->appsecret&code=$code&grant_type=authorization_code";
            $output = http_client($url);
            $jsoninfo = json_decode($output, true);
            $openid = $jsoninfo['openid'];
            //$unionid = $jsoninfo['unionid'];  存在不返回unionid的情况
            $next = base64_decode($state);
            //微信网页登录  需要提供openid unionid
            if($next == 'http://fxshop.28yun.cn/login'){
                header('Location: '.$next.'?openid='.$openid);
                exit;
            }
        }
        $res = $this->check_openid(0,0,$openid);
        if($res['code'] == 200){
            //已经绑定 跳转保存用户信息
            $url = $this->transfer_url."?member_id=".$res['data']['member_id']."&token=".$res['data']['token']."&next=".$next."&openid=".$openid;
            header('Location: '.$url);
            exit;
        }else{
            //未绑定 获取用户信息  此处会有授权确认
            $info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$jsoninfo['access_token'].'&openid='.$openid.'&lang=zh_CN';
            //微信的用户信息
            $info = file_get_contents($info_url);
            //注册会员
            $data['info'] = $info;
            $res = $this->get_member($info);
            if($res['code'] == 200){
                //注册成功
                $url = $this->transfer_url."?member_id=".$res['data']['member_id']."&token=".$res['data']['token']."&next=".$next."&openid=".$openid;

                header('Location: '.$url);
                exit;
            }else{
                //注册失败
                header('Location: '.$next);
                exit;
            }
        }
    }


     //微信js-sdk  access_token（有效期7200秒，开发者必须在自己的服务全局缓存access_token）
    public function get_access_token() {
        $now_access_token = Db::name('weixin_token')->where('type = 1')->find();

        if ($now_access_token['lost_time'] < time()) {
            $access_token = $this->https_request($this->get_access_token);
            $access_token = json_decode($access_token, true);
            $access_token['token'] = $access_token['access_token'];
            unset($access_token['expires_in']);
            unset($access_token['access_token']);
            $access_token['lost_time'] = time() + 5000;
            $access_token['id'] = $now_access_token['id'];
            Db::name('weixin_token')->update($access_token);
        }

        if (!$now_access_token) {
            $access_token = $this->https_request($this->get_access_token);
            $access_token = json_decode($access_token, true);
            $access_token['token'] = $access_token['access_token'];
            unset($access_token['expires_in']);
            unset($access_token['access_token']);
            $access_token['type'] = 1;
            $access_token['lost_time'] = time() + 5000;
            Db::name('weixin_token')->insert($access_token);
        } else {
            if ($now_access_token['lost_time'] > time()) {
                $access_token = $this->https_request($this->get_access_token);
                $access_token = json_decode($access_token, true);
                $access_token['token'] = $access_token['access_token'];
                unset($access_token['expires_in']);
                unset($access_token['access_token']);
                $access_token['lost_time'] = time() + 5000;
                $access_token['id'] = $now_access_token['id'];
                Db::name('weixin_token')->update($access_token);
            }
        }

        return ['code' => 200, 'message' => '成功', 'data' => $now_access_token];
    }





    /**微信jsadk配置
     * @return array
     */
    public function js_config() {


        $logic_share_profit = new ShareProfit;

        //获取access_token
        $res  = $logic_share_profit->check_update('wx_access_token');
        if(isset($res['code']) && $res['code'] == 200){
            $wx_access_token = $res['data']['wx_access_token'];
        }else{
            return return_info(300,$res['message']);
        }
        //获取jsapi_ticket
        $data = [];
        $data['access_token'] = $wx_access_token;
        $res  = $logic_share_profit->check_update('ws_jsapi_ticket',$data);
        if(isset($res['code']) && $res['code'] == 200){
            $ws_jsapi_ticket = $res['data']['ws_jsapi_ticket'];
        }else{
            return return_info(300,$res['message']);
        }
        $length = 32;
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $nonceStr = '';
        //产生随机字符串
        for ( $i = 0; $i < $length; $i++ )
        {
        // 这里提供两种字符获取方式
        // 第一种是使用 substr 截取$chars中的任意一位字符；
        // 第二种是取字符数组 $chars 的任意元素
        // $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);
            $nonceStr .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        //对参数进行签名
        $timestamp = TIMESTAMP;
        $nonceStr = $nonceStr; //生成签名的随机串
        $jsapi_ticket = $ws_jsapi_ticket;
        $url = input('post.url');
        //返回结果wxjssdk配置参数
        if (!empty($timestamp) && !empty($nonceStr) && !empty($jsapi_ticket) && !empty($url)) {
            $string1 = "jsapi_ticket=$jsapi_ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
            $signature = sha1($string1);
            $data = [];
            $data['appId'] = $this->appid;
            $data['Timestamp'] = $timestamp;
            $data['nonceStr'] = $nonceStr;
            $data['signature'] = $signature;
            return ['code' => 200, 'message' => '成功', 'signature' => $data];
        } else {
            return ['code' => 300, 'message' => '缺少参数'];
        }
    }

    /**
     * 判断微信是否已经绑定
     * @param int $member_id
     * @param int $openid
     * @param int $unionid
     * @return array
     */
    public function check_openid($member_id = 0,$openid = 0,$unionid = 0){
        $model_member = new MemberModel();
        if($member_id){
            $member = $model_member->getInfo(['member_id'=>$member_id],[],'member_id,openid,unionid');
            if($member){
                if($member['openid'] == ''){
                    return ['code' => 300, 'message' => '该账户未绑定微信'];
                }else{
                    return ['code' => 200, 'message' => '已绑定'];
                }
            }else{
                return ['code' => 300, 'message' => '账户不存在'];
            }
        }
        if($openid){
            $where = [];
            if(!empty($openid)){
                $where['openid'] = $openid;
            }
            if(!empty($openid)){
                $where['unionid'] = $unionid;
            }
            $member = $model_member->getInfo(['openid'=>$openid],[],'member_id,openid,unionid');
            if($member){
                if($member['unionid'] == ''){
                    return ['code' => 300, 'message' => '未获取用户信息授权'];
                }else{
                    $model_app_user_token = new AppUserTokenModel();
                    $token = $model_app_user_token->getInfo(['member_id'=>$member['member_id']],[],'token');
                    if($token){
                        return ['code' => 200, 'message' => '该微信已经进行了账号绑定','data'=>['member_id'=>$member['member_id'],'token'=>$token['token']]];
                    }else{
                        return ['code' => 300, 'message' => 'token不存在'];
                    }
                }
            }else{
                return ['code' => 300, 'message' => '未获取用户信息授权'];
            }
        }else{
            return ['code' => 300, 'message' => '未绑定'];
        }
    }


    /**
     * 通过openid获取账号信息
     * @return array
     */
    public function get_openid(){
        $member_id = input('post.member_id');
        $model_member = new MemberModel();
        $member = $model_member->getInfo(['member_id'=>$member_id],[],'openid,unionid');
        return ['code' => 200, 'message' => $member];
    }

    /**
     * 微信自动注册
     * @param string $json_info
     * @return mixed
     */
    public function get_member($json_info = '') {
        var_dump($json_info);
        $model_member = new MemberModel();
        $model_member_token = new MemberTokenModel();
        $json = input('post.info') ? input('post.info') : $json_info;
        if(empty($json)){
            return return_info();
        }
        $info = json_decode($json,1);
        $unionid = $info['unionid'];
        $openid = $info['openid'];
        //检查是否已经有注册会员
        $sel = $model_member->where([['unionid','=',$unionid]])->field('member_id,member_name,member_mobile,member_passwd')->find();

        if (!$sel) {
            //未注册
            $message = '未注册,注册后获取会员信息';
            $res = $this->wx_reg($info);
            if($res){
                $sel = $model_member->getInfo([['member_id','=',$res['member_id']]], [], 'member_id,member_name,member_mobile,member_passwd');
                $res = array_merge($sel->toArray(), $res);
            }else{
                return return_info('300', '注册失败');
            }
        }else{
            //已注册
            $message = '已注册,直接获取会员信息';
            $member_id = $sel['member_id'];
            $logic_token = new Token;
            $token = $logic_token->save_token($sel->member_name,$sel->member_passwd,$sel->member_id,3,1);
            if(strlen($token) != 32 ){
                return return_info('300', '更新token失败');
            }
            $res = $sel;
            $res->token = $token;
        }
        if($res){
            unset($res['member_passwd']);
            return return_info('200', $message, $res);
        }else{
            return return_info('300', '会员信息获取失败');
        }
    }
    //微信注册
    public function wx_reg($info){

        $member_nickname = $info['nickname'];
        $member_sex = $info['sex'];
        $member_avatar = $info['headimgurl'];
        $unionid = $info['unionid'];
        $openid = $info['openid'];

        $member_name = 'wx_'.TIMESTAMP;//与  组成 member_name
        $member_password = md5('wx666666');

        $con['member_name'] = $member_name;
        $con['member_passwd'] = $member_password;
        $con['member_nickname'] = $member_nickname;
        $con['member_sex'] = $member_sex;
        $con['member_avatar'] = $member_avatar;
        $con['unionid'] = $unionid;
        $con['openid'] = $openid;

        $member = MemberModel::create($con);
        $member_id = $member->member_id;
        $member_info['member_id'] = $member_id;
        $member_info['unionid'] = $unionid;
        //创建token
        $logic_token = new Token;
        $token = $logic_token->save_token($member->member_name, $member->member_passwd, $member->member_id, 3, 1, AppTokenLostTime);
        return ['member_id'=>$member_id, 'token'=>$token];
    }


    public function add_share() {
        $goods_id = input('post.goods_id');
        $share_id = input('post.share_id');
        $member_id = input('post.member_id');
        $url = input('post.url');
        if (!empty($share_id)) {
            $data['fid'] = $share_id;
        } else {
            $data['fid'] = 0;
        }
        $data['goods_id'] = $goods_id;
        $data['member_id'] = $member_id;
        $data['url'] = $url;
        $data['addtime'] = TIMESTAMP;
        $share_id = Db::name('member_share')->insert($data, 0, 1);
        if ($share_id) {
            return ['code' => 200, 'message' => '分享成功', 'share_id' => $share_id];
        } else {
            return ['code' => 300, 'message' => '分享失败'];
        }
    }

    public function check_code() {
        $code = input('post.code');
        $store_id = input('post.store_id');
        $model_order = new \app\webapi_v2\model\OrderModel;
        $condition['check_number'] = ['eq', $code];
        $condition['store_id'] = ['eq', $store_id];
        $condition['order_state'] = ['>=', 20];

        //$check_order = $model_order->getOneOrderInfo($condition,"check_number");
        $check_order = $model_order->editOrder(['order_state' => '40'], $condition);
        if ($check_order) {
            return ['code' => 200, 'message' => '验证成功'];
        } else {
            return ['code' => 300, 'message' => '验证出错'];
        }
    }

    private function _get_access_token() {
        // 
        $now_access_token = $this->get_access_token();
        return $now_access_token['data']['token'];
    }

    private function https_request($url, $data = null) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

}
