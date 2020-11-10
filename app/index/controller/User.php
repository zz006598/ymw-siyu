<?php
/**
 * +----------------------------------------------------------------------
 * | 用户中心控制器
 * +----------------------------------------------------------------------
 *                      .::::.
 *                    .::::::::.            | AUTHOR: siyu
 *                    :::::::::::           | EMAIL: 407593529@qq.com
 *                 ..:::::::::::'           | DATETIME: 2019/03/28
 *             '::::::::::::'
 *                .::::::::::
 *           '::::::::::::::..
 *                ..::::::::::::.
 *              ``::::::::::::::::
 *               ::::``:::::::::'        .:::.
 *              ::::'   ':::::'       .::::::::.
 *            .::::'      ::::     .:::::::'::::.
 *           .:::'       :::::  .:::::::::' ':::::.
 *          .::'        :::::.:::::::::'      ':::::.
 *         .::'         ::::::::::::::'         ``::::.
 *     ...:::           ::::::::::::'              ``::.
 *   ```` ':.          ':::::::::'                  ::::..
 *                      '.:::::'                    ':'````..
 * +----------------------------------------------------------------------
 */
namespace app\index\controller;

use addons\payment\service\Handel;
use app\common\model\orders;
use app\common\model\Payments;
use app\common\model\Users;
use app\common\model\UsersCollect;
use app\common\model\UsersRecharges;
use app\common\model\UsersType;
use GuzzleHttp\Client;
use think\db\exception\DbException as DbExceptionAlias;
use think\facade\Request;
use think\facade\Session;
use think\facade\View;

class User extends Base
{
    // 初始化
    public function initialize()
    {
        parent::initialize();
        View::assign([
            'cate'        => null,
            'system'      => $this->system, //系统信息
            'logined'      => $this->logined, //系统信息
            'public'      => $this->public, //公共目录
            'tplData'     => $this->tplData, //公共目录
            'title'       => $this->system['title'] ? $this->system['title'] : $this->system['name'], //seo信息
            'keywords'    => $this->system['key'],   //seo信息
            'description' => $this->system['des'],   //seo信息
            'action'      => Request::action()
        ]);
    }

    // 用户中心首页
    public function index()
    {
        if (!$this->userId) {
            return redirect('/user/login');
        }

        $view = [
            'logined' => $this->logined,
        ];
        View::assign($view);
        return View::fetch();
    }

    public function noLoginJump () {
        if (empty($this->userId)) {
            $this->error('您尚未登录或登录超时',url('login'));
        }
    }

    // 登录
    public function login(){
        if (Session::has('user.id')) {
            return redirect('/user');
        }
        // 登录提交
        if (Request::isPost()) {
            return $this->checkLogin();
        }
        return View::fetch();
    }

    // 注册
    public function register(){
        if (Session::has('user.id')) {
            return redirect('index');
        }
        if (Request::isPost()) {
            return $this->checkRegister();
        }
        return View::fetch();
    }

    // 用户中心设置页
    public function set(){
        $this->noLoginJump();
        if (Request::isPost()) {
            if (Request::post("password") && Request::post("password2")) {
                // 修改密码
                return $this->changePassword();
            }else{
                // 修改信息
                return $this->changeInfo();
            }
        } else {
            $user = \app\common\facade\User::getUser($this->userId);
            $view = [
                'user'=>$user,
            ];
            View::assign($view);
            return View::fetch();
        }

    }

    // 退出
    public function logout(){
        Session::delete('user');
        $r = strtolower(Request::get('redirect') ?: $_SERVER['HTTP_REFERER']);
        if (strstr($r, '/user') !== false) $r = '/user/login';
        return redirect($r);
    }

    // ==========================

    // 校验登录
    private function checkLogin()
    {
        $username = trim(Request::post('username'));
        $password = trim(Request::post('password'));
        // 检查是否开启了验证码
        $message_code = $this->system['message_code'];
        if ($message_code) {
            if (!captcha_check(Request::post("message_code"))) {
                $this->error('验证码错误');
            }
        }
        // 校验用户名密码
        $result = \app\common\facade\User::login($username, $password);
        if ($result['error'] == 1) {
            $this->error($result['msg']);
        }else{
            $this->success($result['msg'], 'index');
        }
    }

    // 校验注册
    private function checkRegister()
    {
        $email     = trim(Request::post("username"));
        $password  = trim(Request::post("password"));
        $password2 = trim(Request::post("password2"));

        // 非空判断
        if (empty($email) || empty($password) || empty($password2)) {
            $this->error('请输入用户名、密码和确认密码');
        }

        // 验证码
        $message_code = $this->system['message_code'];
        if ($message_code) {
            if (!captcha_check(input("post.message_code"))) {
                $this->error('验证码错误');
            }
        }
        $result = \app\common\facade\User::register($email, $password, $password2);
        if ($result['error'] == 1) {
            $this->error($result['msg']);
        } else {
            $this->success($result['msg'], 'index');
        }
    }

    // 修改密码
    private function changePassword()
    {
        $oldPassword = trim(Request::post('nowpassword'));
        $newPassword = trim(Request::post('password'));
        $confirmPassword = trim(Request::post('password2'));
        $result = \app\common\facade\User::changePassword($this->userId, $oldPassword, $newPassword, $confirmPassword);
        if ($result['error'] == 1) {
            $this->error($result['msg']);
        } else {
            $this->success($result['msg'], 'index');
        }
    }

    // 修改信息
    private function changeInfo(){
        $username = trim(Request::post('username'));
        $data = [
            'nickname' => Request::post('nickname'),
            'email' => Request::post('email'),
            'mobile' => Request::post('mobile'),
            'qq' => Request::post('qq'),
            'description' => Request::post('description')
        ];

        if ($this->logined['username'] ==='' &&  $username !== '') {
            if (strlen($username) < 5) {
                $this->error('用户名不能少于5位');
            } else {
                $data['username'] = $username;
            }
        }
        $result = \app\common\facade\User::changeInfo($this->userId, $data);
        if ($result['error'] == 1) {
            $this->error($result['msg']);
        } else {
            $this->success($result['msg'], 'index');
        }
    }

    /**
     * 修改密码
     */
    public function password () {
        $this->noLoginJump();
        if (Request::isPost()) {
            $this->changePassword();
        } else {
            return View::fetch();
        }
    }

    // 金币充值
    public function charge () {
        $this->noLoginJump();
        if (Request::isPost()) {

            $pay_type   = Request::post('pay_type');
            $charge_num = Request::post('charge_num');
            // 查询支付方式
            $payment = Payments::find($pay_type);

            if ($charge_num <= 0) {
                $this->error('充值金额不能小于或等于0');
            }

            if (!$payment) {
                $this->error('支付方式不存在');
            }

            // 创建订单
            $orderId = createOrderId();
            $order   = UsersRecharges::create([
                'order_no'  => $orderId,
                'money'     => $charge_num,
                'payment'   => $pay_type,
                'uid'       => $this->userId,
                'status'    => 1
            ]);

            $payment = Payments::find($pay_type);

            if (!$payment) {
                $this->error('未开通此支付方式');
            }


            $res = Handel::submit($payment->identify,$orderId,$charge_num, '会员充值');

            if ($res['type'] === 'json' && $res['error']) {
                $this->error($res['error']);
            }


            if ($res['type'] === 'html') {
                $this->success([
                    'html' => $res['html'],
                    'order_id' => $orderId
                ]);
            } else {
                $this->success([
                    'html' => '恭喜您，充值成功',
                    'order_id' => $orderId
                ]);
            }
            exit();
        } else {
            return View::fetch();
        }
    }

    /**
     * 获取订单记录
     * @return string
     */
    public function orders () {
        $this->noLoginJump();
        return View::fetch();
    }

    /**
     * 获取已购记录
     * @return string
     */
    public function mypay () {
        $this->noLoginJump();

        $list = orders::with('resource')->where('pay_status',1)->where('business',3)->where('uid',$this->userId)->order('id', 'desc')->paginate(10,false);
        $res = [];
        foreach ($list as $item) {
            $res[] = $item->resource;
        }

        View::assign([
            'paylist' => listAttachCates($res)
        ]);

        return View::fetch();
    }

    /**
     * 获取收藏文章记录
     * @return string
     * @throws DbExceptionAlias
     */
    public function myfav () {
        $this->noLoginJump();

        $list = UsersCollect::with('polym')->where('uid',$this->userId)->order('id', 'desc')->paginate(10,false);

        $res = [];
        foreach ($list as $item) {
            if ($item->polym) {
                $res[] = $item->polym;
            }
        }

        View::assign([
            'favlist' => listAttachCates($res)
        ]);

        return View::fetch();

    }

    /**
     * vip套餐购买
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws DbExceptionAlias
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function vip () {
        $this->noLoginJump();
        if (Request::isPost()) {
            $pay_id = Request::post('pay_id');
            $data_id = Request::post('data_id');
            $data_type_id = Request::post('data_type_id');
            $price = Request::post('price');
            if (empty($data_id)) {
                $this->error('请选择升级套餐');
            }

            $type = UsersType::find($data_id);

            if (!$type || empty($type->upgrade_price)) {
                $this->error('套餐不存在');
            }

            if ($this->logined['type_level'] > $type['level']) {
                $this->error('您已经是【'.$this->logined['type_name'].'】,不能购买【'.$type['name'].'】');
            }

            $payment = Payments::find($pay_id);

            if (!$payment) {
                $this->error('未开通此支付方式');
            }

            $order_no = createOrderId();
            $price    = $type->upgrade_price;
            $title    = 'VIP【'.$type->name.'】';

            // 创建统一订单
            orders::create([
                'order_id' => $order_no,
                'price' => $price,
                'discounts_price' => $price,
                'title' => $title,
                'payment' => $pay_id,
                'uid' => $this->userId
            ]);

            $res = Handel::submit($payment->identify,$order_no,$price, $title);

            if ($res['type'] === 'json' && $res['error']) {
                $this->error($res['error']);
            }

            // 更新用户表
            Users::update([
                'type_id'  => $data_id,
                'validity' => $type->duration ? (strtotime('+'.$type->duration.' day',$this->logined['validity'] ?: time())) : 0
            ], ['id' => $this->userId]);

            if ($res['type'] === 'html') {
                $this->success([
                    'html' => $res['html'],
                    'order_id' => $order_no
                ]);
            } else {
                $this->success([
                    'html' => '恭喜您，购买成功',
                    'order_id' => $order_no
                ]);
            }
        }
        return View::fetch();
    }
}
