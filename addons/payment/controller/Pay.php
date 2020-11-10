<?php
namespace addons\payment\controller;

use app\common\model\orders;
use think\facade\Request;

class Pay
{
    public function check()
    {
        $order_id = Request::param('order_id');
        if ($order_id) {
            $one = orders::where('order_id', $order_id)->find();
            if ($one && $one->pay_status == 1) {
                return json([
                    'code' => 1,
                    'msg'  => 'success'
                ]);
            }
        }
        return json([
            'code' => 0,
            'msg'  => 'fail'
        ]);
    }
}