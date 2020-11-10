<?php
/**
 * +----------------------------------------------------------------------
 * | 首页控制器
 * +----------------------------------------------------------------------
 *                      .::::.
 *                    .::::::::.            | AUTHOR: siyu
 *                    :::::::::::           | EMAIL: 407593529@qq.com
 *                 ..:::::::::::'           | QQ: 407593529
 *             '::::::::::::'               | DATETIME: 2019/04/12
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

use app\common\facade\Cms;
use app\common\model\Module;
use think\captcha\facade\Captcha;
use think\facade\Db;
use think\facade\Request;
use think\facade\View;

class Search extends Base
{
    /**
     * 搜索页面
     */
//    public function search () {
//        $keyword = $_GET['keyword'];
//        $cate_id = $_GET['cid'];
//
//        if (empty($keyword)) {
//            $this->error('关键词不能为空');
//        }
//
//        if (empty($cate_id)) {
//            $this->error('非法请求');
//        }
//
//        // 获取所有模型
//        $modules = $this->modules->column(null, 'id');
//        if (empty($modules[$cate_id])) {
//            $this->error('未找到栏目模型');
//        }
//
//        // 获取栏目信息
//        $cate = Cms::getCateInfo($cate_id);
//
//
//
//        if (empty($cate)) {
//            $this->error('未找到对应栏目');
//        }
//
//        $table = $modules['table_name'];
//
//        $list = Db::table($table)->where('title', 'like', "%{$keyword}%")->select();
//
//        View::assign('list', $list);
//
//        return View::fetch('search');
//    }

    // 搜索
    public function index(){
        $search = Request::param('keyword'); // 关键字
        $table  = 'resource';
        $cid = Request::param('cid'); // 栏目ID

        $cate   = getCate($cid ?: null);
        $module = getModule($cate['module_id'] ?? null);

        if ($cate && $module) {
            $table = $module['table_name'];
        }

        $view = [
            'cate'          => ['topid' => 0], // 栏目信息
            'keyword'       => $search,       // 关键字
            'cid'           => $cid,       // 关键字
            'table'         => $table,       // 关键字
            'system'        => $this->system, // 系统信息
            'logined'       => $this->logined, // 系统信息
            'tplData'       => $this->tplData, // 系统信息
            'public'        => $this->public, // 公共目录
            'title'         => $this->system['title'] ? $this->system['title'] : $this->system['name'], //seo信息
            'keywords'      => $this->system['key'],   //seo信息
            'description'   => $this->system['des'],   //seo信息
        ];

        $template = $this->template.'search.html';
        View::assign($view);
        return View::fetch($template);
    }
}
