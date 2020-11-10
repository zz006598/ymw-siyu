<?php

namespace addons\payment\service;

use app\common\model\orders;
use app\common\model\Payments;
use app\common\model\Users;
use app\common\model\UsersCapitalDetails;
use QRcode as QRcodeAlias;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Request;
use think\facade\View;
use Yurun\PaySDK\AlipayApp\FTF\Params\ExtendParams;

class Handel
{

    /**
     * 统一支付方式
     * @param $method
     * @param $trade_no
     * @param $amount
     * @param $subject
     * @param $extend
     * @return
     */
    static public function submit ($method,$trade_no,$amount, $subject = '商品购买',$extend = []) {
        return static::$method($trade_no,$amount,$subject,$extend);
    }

    /**
     * 支付宝当面付二维码
     * @param $trade_no
     * @param $amount
     * @param $subject
     * @param $extend
     */
    static public function alipay ($trade_no,$amount,$subject,$extend) {

        $params = new \Yurun\PaySDK\AlipayApp\Params\PublicParams();
        $params->appID = '2021001197665835';
        $params->appPrivateKey = 'MIIEpAIBAAKCAQEA5fWVPPnUVjSoDQLgIJ3Qwt3LvchSvT86rw8/nm2r2OD5ThinEK/uyjoVaQUR6m0k/OaYlA4BUO2YO0Yxm9krIRUzJp2t0B+w1VCyhwn3Pm/WWoqH84RNrKgpl60t3RecRcAjLiEiEX4lwlHg6Hu6FNgNPq8poph6oGBOe6/Xm+VieRw7RSS1LCEAaVHzi8DfOIpTgNOmYItg3iwam01ReLtxqy4qhyI02EzDUtzXRfHYjeR8ek7FrBJWHlx9rJ+JPAqqYjAaZd70XDQRXniq4p0dGWwRt+cB94CblG8plN2QSJeyTkINITnE0m8Kl8coGFvdBj0HYJB1xwnQgcNaywIDAQABAoIBACWAp5YKKFbmv4Fftq2bDzC4e0G4KcYzSZ7DHdz4hc4Y4o+Z9aUDDC5uyo8WBJX2ttGHydpbbluEZA9Go1CHWkFK88yYaoBGqtAfGP5s4aWNF6gsb3+HVCOUSTQzSgeEkjfN3e7n+GZh7EaEk+lWREKh+Yb/igq6U0VUb3g8dS03LHNg6MwM10sRYHOiB8BVQtfVrapheMH0fJuDvT8rI7RfP+miiUnaNA50DLOrmzy3vw17Z0f8TkEs3XxYWf/o1wcD3k5SvXMaV2G/LS0tGa0T6ErCCKCv4lJz15gOPWlYuKzI6kIWq9YzAQq6XaFdoGoiYNNxOS0RdNxFD+CoIgECgYEA9RyL44UXH9cODKZ1oNa/zQge2+UIwMrB3O4eTC7kgYJdjBE89chJ7HIC61//9BBUTuon+jJf4rncWYGhb3CMgDQE5WB/Iy1lMwyppaP5NvXIARozKH1A9BQYTVCHJYWF0c0fvbvku8nTWoIUhZRt7prLpxSZ5sBtdFpJvOeFS48CgYEA8Cy5DqAcEO1ksDEsJW3LV5+8lLufIg3OJgujcNhHyRNr+VERtftP5tnUsKyIOBaAg2Hz9rPrSbaLGMA7SXAgSESyGOHbmxrHrwJaituX0M6daXEtt1f0KWzXM+ufdCpnyp0nsNvsUEREIlH/HBv9NLSum1yCojMLMuqYsh/MjwUCgYAyUJrSqIZXreCfbiglTQ/wOaOEBh7m3HgxLtwfTVzwzN8BGqTF20h5dentgTZcVmHIFT8BmAeg1gBKi1alNphQ1NzQbR+MDAyDDy1f6CoHQyq8NzGbNSL5N4rJjCdB54fRymainwhUGBj/skYeKZrraPE3Kf5xpLyTLtmGIox1NwKBgQCxyzm3RPlh4oruD4ixsISeOia9J+NWr0eTTHxSdhk2FgWRS91DjWYJ6+mSXDZ+5tF98Q5L68bbC6IO0YdSBwou1YPN/ay3Nmzp9mEWeBb7wWgv/VOtbRPXcrYgvmWM9jNdf1c5iqR2iwKxMWgb4/Beiv7TDfm6nvTEXXxqKDYjDQKBgQDfzcbnerxJKKjzlLi4iFZKvl51O6W6f8Q6i0DKMenmrhJam6Kt9TcxH+7Y1x2296QmIF6yFX8ZKPLFJ1vx09kdsQq1CMM7lg6O45zgICUaoREcaKCjPniozliv22FbnL6FUfnhj9H8NSaf+qFrL41V89fd7mGSo2KCSkf059WSHw==';

        // SDK实例化，传入公共配置
        $pay = new \Yurun\PaySDK\AlipayApp\SDK($params);

        $extend_params = new ExtendParams();

        foreach ($extend as $key => $value) {
            $extend_params->$key = $value;
        }

        // 支付接口
        $request = new \Yurun\PaySDK\AlipayApp\FTF\Params\QR\Request;
        $request->notify_url = ''; // 支付后通知地址（作为支付成功回调，这个可靠）
        $request->return_url = ''; // 支付后跳转返回地址
        $request->businessParams->out_trade_no = $trade_no; // 商户订单号
        $request->businessParams->total_amount = $amount; // 价格
        $request->businessParams->subject = $subject; // 商品标题
        $request->businessParams->extend_params = $extend_params;

        // 跳转到支付宝页面
        $pay->prepareExecute($request,$url, $data);

        $data = json_decode(file_get_contents($url));

        $url = '';

        if (!is_null($data) && ($response = $data->alipay_trade_precreate_response) && strtolower($response->msg) === 'success') {
            require "../vendor/phpqrcode/phpqrcode.php";
            ob_start();//开启缓冲区
            QRcodeAlias::png($response->qr_code, false, 'L', 10, 1);//生成二维码
            $img = ob_get_contents();//获取缓冲区内容
            ob_end_clean();//清除缓冲区内容
            $url = 'data:png;base64,' . chunk_split(base64_encode($img));//转base64
            ob_flush();
            View::assign([
                'money' => $amount,
                'url' => $url
            ]);

            $html =  View::fetch('addons/payment/alipay');

            return [
                'type' => 'html',
                'html' => $html
            ];
        } else {
            return [
                'type' => 'json',
                'error' => '初始化支付失败'
            ];
        }


    }

    /**
     * 余额支付
     * @param $trade_no
     * @param $amount
     * @param $subject
     * @param $extend
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    static public function yecpay ($trade_no,$amount,$subject,$extend) {
        $session = session('user');
        $user = Users::find($session['id']);
        if ($user['balance'] < $amount) {
            return [
                'type' => 'json',
                'error' => '余额不足，请充值'
            ];
        }

        $user->balance -= $amount;

        if ($user->save()) {
            // 添加账变记录
            UsersCapitalDetails::create([
                'uid' => $session['id'],
                'change_money' => -$amount,
                'remark' => $subject
            ]);

            return [
                'type' => 'json',
                'error' => null
            ];
        } else {
            return [
                'type' => 'json',
                'error' => '写入失败，请重试'
            ];
        }


    }

}