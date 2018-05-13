<?php
namespace app\member\controller;

class Wxchat
{
    private $appid = "wx4387dbacb5cca0ad";
    private $appsecret = "993f25dbe1826dbe9eef56a52207093a";

    //get_snsapi_userinfo 获取code后回调地址
    private $redirect_uri = 'http://api.jztule.com/member/wxchat/get_snsapi_userinfo';
    private $transfer_url = 'http://api.jztule.com/transfer';

    public function get_snsapi_userinfo(){
        //保存最终回跳地址
        if (isset($_GET['next'])) {
            $next = base64_encode($_GET['next']);
        }else{
            $next = base64_encode('http://fxshop.28yun.cn/');
        }
        if (!isset($_GET['code'])) {
            //静默授权
            //$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_base&state=$next#wechat_redirect";
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
}
