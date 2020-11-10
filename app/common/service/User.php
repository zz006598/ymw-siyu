<?php
/**
 * +----------------------------------------------------------------------
 * | CMS前台会员相关业务处理
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
namespace app\common\service;

use think\facade\Request;
use think\facade\Session;

class User
{
    /**
     * 获取用户信息
     * @param int $userId 用户ID
     * @return array|null|\think\Model
     */
    public function getUser(int $userId)
    {
        $user = \app\common\model\Users::find($userId);
        if ($user) {
            $user->type_name = $user->UsersType['name'];
            $user->type_level = $user->UsersType['level'];
            $user->type_overdue = $user->validity ? $user->validity < time() : false;
        }
        return $user;
    }

    /**
     * 登录校验
     * @param string $username 用户名或邮箱
     * @param string $password 密码
     * @return array
     */
    public function login(string $username, string $password)
    {
        $user = \app\common\model\Users::where('username|email',$username)
            ->where('password',md5($password))
            ->find();
        if (empty($user)) {
            return [
                'error' => 1,
                'msg'   => '帐号或密码错误',
            ];
        } else {
            if ($user['status'] == 1) {
                Session::set('user',[
                    'id'      =>$user['id'],
                    'email'   => $user['email'],
                    'username'   => $user['username'],
                    'type_id' => $user['type_id'],
                    'status'  => $user['status'],
                ]);
                // 更新信息
                $user->last_login_time = time();
                $user->last_login_ip = Request::ip();
                $user->save();
                return [
                    'error' => 0,
                    'msg'   => '登录成功',
                ];
            } else {
                return [
                    'error' => 1,
                    'msg'   => '用户已被禁用',
                ];
            }
        }
    }

    /**
     * 注册用户
     * @param string $username 邮箱
     * @param string $password 密码
     * @param string $password2 确认密码
     * @param null $email
     * @return array
     */
    public function register(string $username, string $password, string $password2, $email = null)
    {
        // 密码长度不能低于6位
        if (strlen($password) < 6) {
            return [
                'error' => 1,
                'msg'   => '密码长度不能低于6位',
            ];
        }

        // 邮箱合法性判断
        if ($email && !is_email($email)) {
            return [
                'error' => 1,
                'msg'   => '邮箱格式错误',
            ];
        }

        // 确认密码
        if ($password != $password2) {
            return [
                'error' => 1,
                'msg'   => '两次密码输入不一致',
            ];
        }

        // 防止重复
        $count = \app\common\model\Users::where('username|email','=',$username)->count();
        if ($count) {
            return [
                'error' => 1,
                'msg'   => '用户名或邮箱已被注册',
            ];
        }

        // 注册
        $data = [];
        $data['username']           = $username;
        if ($email) $data['email']  = $email;
        $data['password']           = md5($password);
        $data['last_login_time']    = $data['create_time'] = time();
        $data['create_ip']          = $data['last_login_ip'] = Request::ip();
        $data['status']             = 1;
        $data['type_id']            = 1;
        $data['sex']                = Request::post('sex') ? Request::post('sex') : 0;
        $user = \app\common\model\Users::create($data);
        if ($user->id) {
            return [
                'error' => 0,
                'msg'   => '注册成功',
            ];
        } else {
            return [
                'error' => 1,
                'msg'   => '注册失败',
            ];
        }
    }

    /**
     * 修改密码
     * @param int $userId             用户ID
     * @param string $oldPassword     原密码
     * @param string $newPassword     新密码
     * @param string $confirmPassword 确认密码
     * @return array
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword, string $confirmPassword)
    {

        // 密码长度不能低于6位
        if (strlen($newPassword)<6) {
            return [
                'error' => 1,
                'msg'   => '密码长度不能低于6位',
            ];
        }

        $oldPassword = trim($oldPassword);

        if (empty($oldPassword)) {
            return [
                'error' => 1,
                'msg'   => '原密码不能为空',
            ];
        }

        // 校验原密码
        $user = \app\common\model\Users::where('id', $userId)
            ->find();

        if (!$user) {
            return [
                'error' => 1,
                'msg'   => '非法请求',
            ];
        }

        if (strstr($user->password, 'oauth+') !== false ) {
            if (strpos($user->password, $oldPassword) !== 0) {
                return [
                    'error' => 1,
                    'msg'   => '原密码输入有误',
                ];
            }
        }
        else if ($user->password !== md5($oldPassword)) {
            return [
                'error' => 1,
                'msg'   => '原密码输入有误',
            ];
        }

        // 确认密码
        if ($newPassword != $confirmPassword) {
            return [
                'error' => 1,
                'msg'   => '两次输入的密码不一致',
            ];
        }

        // 更改密码
        $user->password = md5($newPassword);
        $user->save();
        return [
            'error' => 0,
            'msg'   => '密码修改成功',
        ];

    }

    /**
     * 修改用户信息
     * @param int $userId
     * @param null $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function changeInfo(int $userId , $data = null)
    {
        if (!$data) {
            $data = [
                'sex' => Request::param('sex'),
                'qq'  => Request::param('qq'),
                'mobile' => Request::param('mobile'),
            ];
        }

        $mobile = $data['mobile'] ?? null;

        // 手机唯一性校验
        if ($mobile) {
            $count = \app\common\model\Users::where('mobile', $mobile)
                ->where('id', '<>', $userId)
                ->find();
            if ($count) {
                return [
                    'error' => 1,
                    'msg'   => '手机号已存在',
                ];
            }
        }

        $email = $data['email'] ?? null;
        if ($email) {
            $count = \app\common\model\Users::where('email', $email)
                ->where('id', '<>', $userId)
                ->find();
            if ($count) {
                return [
                    'error' => 1,
                    'msg'   => '邮箱已经存在',
                ];
            }
        }

        $username = $data['username'] ?? null;

        if ($username) {
            $count = \app\common\model\Users::where('username', $username)
                ->where('id', '<>', $userId)
                ->find();
            if ($count) {
                return [
                    'error' => 1,
                    'msg'   => '用户名已经存在',
                ];
            }
        }

        \app\common\model\Users::update($data, ['id' => $userId]);
        return [
            'error' => 0,
            'msg'   => '修改成功',
        ];
    }
}
