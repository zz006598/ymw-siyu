<?php
/**
 * +----------------------------------------------------------------------
 * | 广告管理模型
 * +----------------------------------------------------------------------
 *                      .::::.
 *                    .::::::::.            | AUTHOR: siyu
 *                    :::::::::::           | EMAIL: 407593529@qq.com
 *                 ..:::::::::::'           | DATETIME: 2020/07/10
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

class TplData extends Base
{
    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $table = 'tp_tpldata';

    public static function selectToArray()
    {
        $data = self::select();
        $newData = [];
        foreach ($data as $row) {
            $newData[$row['key']] = $row['value'];
        }
        return $newData;
    }

}