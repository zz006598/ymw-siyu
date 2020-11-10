<?php
/**
 * +----------------------------------------------------------------------
 * | 订单模块模型
 * +----------------------------------------------------------------------
 *                      .::::.
 *                    .::::::::.            | AUTHOR: siyu
 *                    :::::::::::           | EMAIL: 407593529@qq.com
 *                 ..:::::::::::'           | DATETIME: 2020/11/01
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
use think\model\relation\HasOne;

class orders extends Base
{
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $table = 'tp_orders';
    public function payments()
    {
        return $this->belongsTo('payments', 'payment');
    }public function users()
    {
        return $this->belongsTo('users', 'uid');
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
            if ($list[$k]['payment']) {
                $v['payment'] = $v->payments->getData('name');
            }if ($list[$k]['uid']) {
                $v['uid'] = $v->users->getData('username');
            }
        }
        return MakeBuilder::changeTableData($list, 'orders');
    }

    // 导出列表
    public static function getExport($where = array(), $order = ['sort', 'id' => 'desc'])
    {
        $list = self::where($where)
            ->order($order)
            ->select();
        foreach ($list as $k => $v) {
            if ($list[$k]['payment']) {
                $v['payment'] = $v->payments->getData('name');
            }if ($list[$k]['uid']) {
                $v['uid'] = $v->users->getData('username');
            }
        }
        return MakeBuilder::changeTableData($list, 'orders');
    }

    /**
     * 一对一关联资源表
     * @return HasOne
     */
    public function resource()
    {
        return $this->hasOne('resource','id', 'data_id');
    }

}