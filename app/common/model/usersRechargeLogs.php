<?php
/**
 * +----------------------------------------------------------------------
 * | 模型
 * +----------------------------------------------------------------------
 *                      .::::.
 *                    .::::::::.            | AUTHOR: siyu
 *                    :::::::::::           | EMAIL: 407593529@qq.com
 *                 ..:::::::::::'           | DATETIME: 2020/10/31
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
namespace app\common\model;

// 引入框架内置类
use think\facade\Request;

// 引入构建器
use app\common\facade\MakeBuilder;

class usersRechargeLogs extends Base
{
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $table = 'tp_users_recharge_logs';
    public function users()
    {
        return $this->belongsTo('users', 'uid');
    }public function payments()
    {
        return $this->belongsTo('payments', 'payment');
    }

    // 获取列表
    public static function getList($where = array(), $pageSize, $order = ['sort', 'id' => 'desc'])
    {
        $list = self::where($where)
            ->order($order)
            ->paginate([
                'query'     => Request::get(),
                'list_rows' => $pageSize,
            ]);
        foreach ($list as $k => $v) {
            if ($list[$k]['uid']) {
                $v['uid'] = $v->users->getData('username');
            }if ($list[$k]['payment']) {
                $v['payment'] = $v->payments->getData('name');
            }
        }
        return MakeBuilder::changeTableData($list, 'usersRechargeLogs');
    }

    // 导出列表
    public static function getExport($where = array(), $order = ['sort', 'id' => 'desc'])
    {
        $list = self::where($where)
            ->order($order)
            ->select();
        foreach ($list as $k => $v) {
            if ($list[$k]['uid']) {
                $v['uid'] = $v->users->getData('username');
            }if ($list[$k]['payment']) {
                $v['payment'] = $v->payments->getData('name');
            }
        }
        return MakeBuilder::changeTableData($list, 'usersRechargeLogs');
    }

}