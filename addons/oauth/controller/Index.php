<?php
namespace addons\oauth\controller;

use app\common\model\Users;
use think\facade\Request;
use think\facade\Session;
use Yurun\OAuthLogin\QQ\OAuth2;

class Index
{

    public function __construct()
    {
        $this->config = require(dirname(__FILE__).'/../config.php');
        $this->qqOAuth = new OAuth2($this->config['appid']['value'], $this->config['appkey']['value'], $this->config['callbackUrl']['value']);
    }

    public function qq()
    {
        $url = $this->qqOAuth->getAuthUrl();
        session('YURUN_QQ_STATE', $this->qqOAuth->state);
        header('location:' . $url);
    }

    public function callBack () {

        // 令牌交换
        $this->qqOAuth->getAccessToken(session('YURUN_QQ_STATE'));

        // 用户资料
        $userInfo = $this->qqOAuth->getUserInfo();

        // 用户唯一标识
        $openid = $this->qqOAuth->openid;


        // 查询用户
        $user = Users::where('openid',$openid)->find();

        if ($user) {
            // 更新信息
            $user->last_login_time = time();
            $user->last_login_ip = Request::ip();
            $user->save();
        } else {

            $last_id  = Users::where('1=1')->order('id','desc')->find()->getData('id');
            $username = 'U'.sprintf("1%06d", ++$last_id);
            $ip       = Request::ip();

            // 创建用户
            $user     = Users::create([
                'username'          => $username,
                'email'             => $username.'@email.com',
                'password'          => md5(time().createRandStr(8)),
                'sex'               => $userInfo['gender_type'],
                'last_login_time'   => time(),
                'last_login_ip'     => $ip,
                'type_id'           => 1,
                'create_ip'         => $ip,
                'nickname'          => $userInfo['nickname'],
                'profile'           => $userInfo['figureurl_2'],
                'openid'            => $openid
            ]);
        }

        // 保存登录状态
        Session::set('user',[
            'id'       => $user['id'],
            'email'    => $user['email'],
            'username' => $user['username'],
            'type_id'  => $user['type_id'],
            'status'   => $user['status'],
        ]);

        // 跳转到会员中心
        return redirect(url('/user', [], false));
    }
}