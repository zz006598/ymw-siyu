<?php
/**
 * +----------------------------------------------------------------------
 * | 自定义标签
 * +----------------------------------------------------------------------
 *                      .::::.
 *                    .::::::::.            | AUTHOR: siyu
 *                    :::::::::::           | EMAIL: 407593529@qq.com
 *                 ..:::::::::::'           | QQ: 407593529
 *             '::::::::::::'               | DATETIME: 2019/03/28
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
namespace app\common\taglib;

use think\facade\Db;
use think\template\TagLib;

class Tp extends TagLib {

    protected $tags = array(
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'close'     => ['attr' => 'time,format', 'close' => 0],                                             // 闭合标签，默认为不闭合
        'open'      => ['attr' => 'name,type', 'close' => 1],
        'nav'       => ['attr' => 'id,limit,name,key,stat,mod', 'close' => 1],                                           // 通用导航信息
        'cate'      => ['attr' => 'id,type,anchor','close' => 0],                                           // 通用栏目信息
        'position'  => ['attr' => 'name','close' => 1],                                                     // 通用位置信息
        'link'      => ['attr' => 'name','close' => 1],                                                     // 获取友情链接
        'ad'        => ['attr' => 'name,id','close' => 1],                                                  // 获取广告信息
        'debris'    => ['attr' => 'name,type','close' => 0],                                                // 获取碎片信息
        'list'      => ['attr' => 'id,name,pagesize,where,search,limit,order,key','close' => 1],                // 通用列表
        'search'    => ['attr' => 'search,table,name,pagesize,where,order','close' => 1],                   // 通用搜索
        'tag'       => ['attr' => 'name,pagesize,order','close' => 1],                                      // 通用标签
        'tagcloud'  => ['attr' => 'name,table,limit','close' => 1],                                         // 标签云
        'prev'	    => ['attr' => 'len','close' => 0],                                                      // 上一篇
        'next'	    => ['attr' => 'len','close' => 0],                                                      // 下一篇
        'dict'      => ['attr' => 'name,dict_type,field,all','close' => 1],                                 // 获取字典类型
        'mod'       => ['attr' => 'table,key,name,limit,where,field,order,pagesize,cid,search,is_cate_info','close' => 1],   // 获取模型数据
        'payments'  => ['attr' => 'name','close' => 1],   // 获取模型数据
        'orders'    => ['attr' => 'uid,status,business,limit,name,key','close' => 1],   // 获取订单数据
        'stat'      => ['attr' => 'method,table,where,field,filter','close' => 0],   // 获取数据统计
    );

    /**
     * 获取数据统计
     * @param $tag
     * @return int
     */
    public function tagStat ($tag) {
        $table        = $tag['table'] ?? null;
        $where        = $tag['where'] ?? '';
        $method       = $tag['method'] ?? 'count';
        $field        = $tag['field'] ?? 'id';
        $filter       = $tag['filter'] ?? '';

        if (empty($table)) return 0;

        $parse  = '<?php ';
        $parse .= '
            $__STAT_TABLE__      = ucfirst(toCamelCase(\''.$table.'\'));
            $__STAT_MODEL__      = \'\\app\\common\\model\\\\\'.$__STAT_TABLE__;
            $__STAT_INSTACE__    = new $__STAT_MODEL__;
            $__STAT_WHERE__      = "'.$where.'";
            $__STAT_METHOD__     = \''.$method.'\';
            $__STAT_FIELD__      = \''.$field.'\';
            if ($__STAT_WHERE__) {
                $__STAT_INSTACE__ = $__STAT_INSTACE__->where($__STAT_WHERE__);
            }
            $__STAT_RES__ =  $__STAT_INSTACE__->$__STAT_METHOD__($__STAT_FIELD__);
        ';

        if ($filter) {
            $filter = explode('|', $filter);
            foreach ($filter as $row) {
                if (empty($row)) continue;
                $row = str_replace('###','$__STAT_RES__', $row);
                if (strstr($row, '=') !== false) {
                    $row = str_replace('=','(', $row);
                } else {
                    $row .= $row .'(';
                }
                $parse .= '$__STAT_RES__ = ' . $row .');';
            }
        }
        $parse .= 'echo $__STAT_RES__;';
        $parse .= '?>';
        return $parse;
    }

    /**
     * 获取订单记录
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagOrders ($tag, $content) {
        $uid        = $tag['uid'] ?? null;
        $status     = $tag['status'] ?? null;
        $business   = $tag['business'] ?? null;
        $limit      = $tag['limit'] ?? 10;
        $name       = $tag['name'] ?? 'list';
        $key        = $tag['key'] ?? 'key';

        $where  = '';

        if ($uid) {
            $where.= '->where(\'uid\', intval('.$uid.'))';
        }

        if ($status) {
            $where.= '->where(\'pay_status\', \''.$status.'\')';
        }

        if ($business) {
            $where.= '->where(\'business\', \''.$business.'\')';
        }

        $parse  = '<?php ';
        $parse .= '$__LIST__ = \app\common\model\orders::where(\'status\',1)'.$where.'->order(\'sort ASC,id DESC\')->limit('.$limit.')->select();';
        $parse .= ' ?>';

        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$key.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    // 获取支付方式
    public function tagPayments ($tag, $content) {
        $name     = $tag['name'] ?? 'list';
        $key      = $tag['key']?? 'key';
        $cateStr  = '<?php ';
        $cateStr .= '$__LIST__ = \app\common\model\Payments::where(\'status\',1)->order(\'sort ASC,id DESC\')->select();';
        $cateStr .= '?>';
        $cateStr .= '{volist name="__LIST__" id="' . $name . '" key="'.$key.'"}';
        $cateStr .= $content;
        $cateStr .= '{/volist}';
        return $cateStr;
    }

    // 这是一个闭合标签的简单演示
    public function tagClose($tag)
    {
        $format = empty($tag['format']) ? 'Y-m-d H:i:s' : $tag['format'];
        $time   = empty($tag['time'])   ? time()        : $tag['time'];
        $parse  = '<?php ';
        $parse .= 'echo date("' . $format . '",' . $time . ');';
        $parse .= ' ?>';
        return $parse;
    }

    // 这是一个非闭合标签的简单演示
    public function tagOpen($tag, $content)
    {
        $type   = empty($tag['type']) ? 0 : 1; // 这个type目的是为了区分类型，一般来源是数据库
        $name   = $tag['name'];                // name是必填项，这里不做判断了
        $parse  = '<?php ';
        $parse .= '$test_arr=[[1,3,5,7,9],[2,4,6,8,10]];'; // 这里是模拟数据
        $parse .= '$__LIST__ = $test_arr[' . $type . '];';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    // 获取模型数据
    public function tagMod ($tag, $content) {
        $table      = $tag['table'] ?? 'article';
        $limit      = $tag['limit'] ?? '0'; // 传递时不进行分页
        $cid        = $tag['cid'] ?? '';
        $order      = $tag['order'] ?? 'sort ASC,id DESC';
        $pageSize   = $tag['pagesize'] ?? config('app.page_size');
        $name       = $tag['name']  ?? 'nav';
        $search     = $tag['search']   ?? '';                      // 分类筛选字段,通过,或|传递多个
        $simple     = $tag['simple']   ?? 'false';
        $isCate     = $tag['is_cate']   ?? 'false';
        $isCates    = $tag['is_cates']   ?? 'false';
        $key        = $tag['key']   ?? 'index';

        $where      = isset($tag['where']) ? $tag['where'] . ' AND status = 1 ' : ' status = 1 '; // 查询条件
        $parse = '';

        if (empty($tag['table'])) {
            return $parse;
        }

        $extra = '';

        if ($cid) {
            $extra .= '->where(\'cate_id\', \'in\', '.$cid.')';
        }

        $table = ucfirst(toCamelCase($table));


        $parse  .= '<?php ';
        $parse  .= '
            $list              = [];
            $__IS_CATE__  = \''.$isCate.'\';
            $__IS_CATES__  = \''.$isCates.'\';
            $__SEARCH__ = getSearchField(\'' . $search . '\');
            $__TABLE_MODEL__   = \app\common\model\Module::where(\'model_name\', \''.$table.'\')->find();
            if ($__TABLE_MODEL__) {
                // 表名称为空时（id不存在）直接返回空数组
                $__MODEL__ = \'\app\common\model\\'.$table.'\';
                // 当传递limit时，不再进行分页
                if(' . $limit . ' != 0){
                    $__LIST__ = $__MODEL__::where("' . $where . '" . $__SEARCH__)
                        '.$extra.'
                        ->field($__TABLE_MODEL__->getData(\'list_fields\'))
                        ->order(\'' . $order . '\')
                        ->limit(\'' . $limit . '\')
                        ->select();
                    $page = \'\';
                }else{
                    $__PAGESIZE__ = ' . $pageSize . ';
                    $__LIST__ =  $__MODEL__::where("' . $where . '" . $__SEARCH__)
                        '.$extra.'
                        ->field($__TABLE_MODEL__->getData(\'list_fields\'))
                        ->order(\'' . $order . '\')
                        ->paginate([
                            \'query\'     => request()->param(),
                            \'list_rows\' => $__PAGESIZE__,
                        ], ' . $simple . ');
                    $page = $__LIST__->render();
                }
                // 处理数据（把列表中需要处理的字段转换成数组和对应的值）
                $__LIST__ = changeFields($__LIST__, $__TABLE_MODEL__->id);
            } else {
                $__LIST__ = [];
            }
            
            if ($__IS_CATE__ === \'true\') {
                $__LIST__ = listAttachCate($__LIST__);
            }
            
            if ($__IS_CATES__ === \'true\') {
                $__LIST__ = listAttachCates($__LIST__);
            }
            
            ';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$key.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    // 通用导航信息
    public function tagNav($tag, $content)
    {
        $tag['limit']   = $tag['limit']  ?? '0';
        $tag['pid']     = $tag['pid']    ?? '0';
        $tag['id']      = $tag['id']    ?? '';
        $tag['key']     = $tag['key']    ?? 'index';
        $name           = $tag['name']  ?? 'nav';
        $stat           = $tag['stat']  ?? 'false';
        $mod            = $tag['mod']  ?? '';

        $cateStr  = '$__CATE__ = \app\common\model\Cate::where(\'is_menu\',1)->order(\'sort ASC,id DESC\')->select();';

        $cateStr .= '
            $__STAT__ = \''.$stat.'\';
            if ($__STAT__ === \'true\') {
                if ($__CATE__) {
                    $__CIDS__ = $__CATE__->column(\'id\');
                    $__CATE_STATS__ = cache(\'cate_stats\');
                    if (! $__CATE_STATS__) {
                        $__MODULES__ = getModules()->column(null,\'id\');
                        $__TABLES__ = [];
                        $__PREFIX__ = config(\'database.connections.mysql.prefix\');
                        foreach($__CATE__ as $vo) {
                           $module = $__MODULES__[$vo[\'module_id\']] ?: null;
                           if ($module && ($__TABLE_NAME__ =  $__PREFIX__.$module[\'table_name\']) && !in_array($__TABLE_NAME__,$__TABLES__)) {
                            $__TABLES__[] = $__TABLE_NAME__;
                            $data = \think\facade\Db::table($__TABLE_NAME__)->where(\'cate_id\',\'in\',$__CIDS__)->group(\'cate_id\')->column(\'count(*) as count\',\'cate_id\');
                            foreach($data as $cid => $count) {
                                $__CATE_STATS__[$cid] = ($__CATE_STATS__[$cid] ?? 0) + $count;
                            }
                           }
                        }
                        cache(\'cate_stats\', $__CATE_STATS__, 3600 * 24);
                    }
                    
                    foreach ($__CATE__ as $key => $____cate) {
                        $__SELF_CATES__ = getChildsId($__CATE__,$____cate->id);
                        $__SELF_CATES__[] = $____cate;
                        foreach ($__SELF_CATES__ as $sc) {
                            $__CATE__[$key]->stats = ($__CATE__[$key]->stats ?? 0) + ($__CATE_STATS__[$sc->id] ?? 0);
                        }
                    }
                }
            }
        ';

        if (!empty($tag['id'])) {
            $cateStr .= '
                $__CATE_IDS__ = array_filter(explode(\',\', '.$tag['id'].'));
                $__REPLACE_PID__ = \'\';
                $__CATE__ = array_filter($__CATE__->toArray(), function ($vo) use ($__CATE_IDS__, &$__REPLACE_PID__) {
                    $exists = in_array($vo[\'id\'], $__CATE_IDS__);
                    if ($exists) {
                        $__REPLACE_PID__ .= \',\'.$vo[\'parent_id\'];
                    }
                    return $exists;
                });
                
                $__REPLACE_PID__ = substr($__REPLACE_PID__, 1);
            ';
        }

        $cateStr .= '$__LIST__ = unlimitedForLayer($__CATE__, \'sub\', $__REPLACE_PID__ ?? ' . $tag['pid'] . ');$__REPLACE_PID__ = null;';



        // 提取前N条数据,因为sql的LIMIT避免不了子栏目的问题
        if (!empty($tag['limit'])) {
            $cateStr .= '$__LIST__ = array_slice($__LIST__, 0,' . $tag['limit'] . ');';
        }

        $parse  = '<?php ';
        $parse .= $cateStr;
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$tag['key'].'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    // 通用栏目信息
    public function tagCate($tag)
    {
        $id     = $tag['id']     ?? getCateId();
        $type   = $tag['type']   ?? 'cate_name';
        $anchor = $tag['anchor'] ?? '';

        $str  = '<?php ';
        $str .= '$__CATE__ = \app\common\model\Cate::where("id",' . $id . ')->find();';
        $str .= 'if ($__CATE__) { ';
        $str .= '$__CATE__->url = getUrl($__CATE__->toArray());';
        $str .= '
            if (!empty(\'' . $anchor . '\')) {
              $__CATE__->url = $__CATE__->url . \'' . $anchor . '\';
            }';
        $str .= 'echo $__CATE__[\'' . $type . '\'];';
        $str .= '}';
        $str .= '?>';
        return $str;
    }

    // 通用位置信息
    public function tagPosition($tag, $content)
    {
        $name = $tag['name'] ? $tag['name'] : 'position';
        $parse  = '<?php ';
        $parse .= '$__CATE__   = \app\common\model\Cate::select();';
        $parse .= '$__CATEID__ = getCateId();';
        $parse .= '$__LIST__   = getParents($__CATE__,$__CATEID__);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= '<?php $' . $name . '[\'url\']=getUrl( $' . $name . '); ?>';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    // 获取友情链接
    public function tagLink($tag, $content)
    {
        $name = $tag['name'] ? $tag['name'] : 'link';
        $parse = '<?php ';
        $parse .= '$__LIST__ = \app\common\model\Link::where(\'status\',1)->order(\'sort asc,id desc\')->select();';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    // 获取广告信息
    public function tagAd($tag, $content)
    {
        $name = $tag['name'] ?? 'ad';
        $id   = $tag['id']   ?? '';
        $parse = '<?php ';
        $parse .= '
            $__WHERE__ = [];
            $__WHERE__[] = [\'status\', \'=\', 1];
            if (!empty(\'' . $id . '\')) {
                $__WHERE__[] = [\'type_id\', \'=\', ' . $id . '];
            }';
        $parse .= '
            $__LIST__ = \app\common\model\Ad::where($__WHERE__)->order(\'sort asc,id desc\')->select();';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    // 通用碎片信息
    public function tagDebris($tag)
    {
        $name = $tag['name'] ?? '';
        $type = $tag['type'] ?? '';
        $str  = '<?php ';
        $str .= 'echo \app\common\model\Debris::where("name",\'' . $name . '\')->where("status", 1)->value("' . $type . '");';
        $str .= '?>';
        return $str;
    }

    // 通用列表
    public function tagList($tag, $content)
    {
        $id       = $tag['id']       ?? '0';                     // 可以为空
        $name     = $tag['name']     ?? "list";                  // 不可为空
        $order    = $tag['order']    ?? 'sort ASC,id DESC';      // 排序
        $limit    = $tag['limit']    ?? '0';                     // 多少条数据,传递时不再进行分页
        $simple   = $tag['simple']   ?? 'false';                 // 是否简洁模式
        $isCate   = $tag['is_cate']   ?? 'false';
        $isCates  = $tag['is_cates']   ?? 'false';
        $search   = $tag['search']   ?? '';                      // 分类筛选字段,通过,或|传递多个
        $key      = $tag['key']   ?? 'k';                      // 分类筛选字段,通过,或|传递多个

        if (strpos($order, '$') !== 0) {
            $order = "'{$order}'";
        }

        $where    = isset($tag['where']) ? $tag['where'] . ' AND status = 1 ' : ' status = 1 '; // 查询条件
        $pageSize = $tag['pagesize'] ?? config('app.page_size'); // 每页数量
        $parse  = '<?php ';
        $parse .= '
            $__IS_CATE__  = \''.$isCate.'\';
            $__IS_CATES__  = \''.$isCates.'\';
            $list       = [];
            $__CATEID__ = '.$id.' ? '.$id.' : getCateId();
            $__CATE__   = \app\common\model\Cate::find($__CATEID__);
            $__SEARCH__ = getSearchField(\'' . $search . '\');
            // 查询子分类,列表要包含子分类内容
            $__ALLCATE__ = \app\common\model\Cate::field(\'id,parent_id\')->select()->toArray();
            $__IDS__ = getChildsIdStr(getChildsId($__ALLCATE__,$__CATEID__),$__CATEID__);
            // 表名称为空时（id不存在）直接返回空数组
            if ($__CATE__ && !empty($__CATE__->module->getData(\'table_name\'))) {
                $__MODEL__ = \'\app\common\model\\\\\' . $__CATE__->module->getData(\'model_name\');
                // 当传递limit时，不再进行分页
                if(' . $limit . ' != 0){
                    $__LIST__ = $__MODEL__::where("' . $where . '" . $__SEARCH__)
                        ->where(\'cate_id\', \'in\', $__IDS__)
                        ->field($__CATE__->module->getData(\'list_fields\'))
                        ->order(' . $order . ')
                        ->limit(\'' . $limit . '\')
                        ->select();
                    $page = \'\';
                }else{
                    $__PAGESIZE__ = empty($__CATE__[\'page_size\'])?' . $pageSize . ':$__CATE__->page_size;
                    $__LIST__ =  $__MODEL__::where("' . $where . '" . $__SEARCH__)
                        ->where(\'cate_id\', \'in\', $__IDS__)
                        ->field($__CATE__->module->getData(\'list_fields\'))
                        ->order(' . $order . ')
                        ->paginate([
                            \'query\'     => request()->param(),
                            \'list_rows\' => $__PAGESIZE__,
                        ], ' . $simple . ');
                    $page = $__LIST__->render();
                }
                // 处理数据（把列表中需要处理的字段转换成数组和对应的值）
                $__LIST__ = changeFields($__LIST__, $__CATE__->module_id);
            }else{
                $__LIST__ = [];
            }
            if ($__IS_CATE__ === \'true\') {
                $__LIST__ = listAttachCate($__LIST__);
            }
            
            if ($__IS_CATES__ === \'true\') {
                $__LIST__ = listAttachCates($__LIST__);
            }
            ';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$key.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    // 通用搜索
    public function tagSearch($tag, $content)
    {
        $search   = $tag['search']   ?? "";                      // 关键字
        $table    = $tag['table']    ?? "article";               // 表名称
        $name     = $tag['name']     ?? "list";                  // 不可为空
        $order    = $tag['order']    ?? 'sort ASC,id DESC';      // 排序
        $where    = isset($tag['where'])    ? $tag['where'] . ' AND status = 1 ' : 'status = 1'; // 查询条件
        $pagesize = $tag['pagesize'] ?? config('app.page_size'); // 分页条数

        $parse  = '<?php ';
        $parse .= '
                $__MODULE__ = \app\common\model\Module::where("table_name","' . strtolower($table) . '")->find();
                $__MODEL__ = \'\app\common\model\\\\\' . $__MODULE__->model_name;

                $__LIST__ = $__MODEL__::where("' . $where . '")
                    ->where("title", "like", "%' . $search . '%")
                    ->order("' . $order . '")
                    ->paginate([
                        \'query\'     => request()->param(),
                        \'list_rows\' => "' . $pagesize . '",
                    ]);
                $page = $__LIST__->render();

                //处理数据（把列表中需要处理的字段转换成数组和对应的值）
                $__LIST__ = changeFields($__LIST__,$__MODULE__->id);
            ';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    // 通用TAG标签
    public function tagTag($tag, $content)
    {
        $name     = $tag['name']     ?? "list";                  //不可为空
        $order    = $tag['order']    ?? 'sort ASC,id DESC';      //排序
        $pagesize = $tag['pagesize'] ?? config('app.page_size'); //分页条数

        $parse = '<?php ';
        $parse .= '
                // 获取模型ID
                $__MODULEID__ = request()->param(\'module\');
                // 获取搜索词
                $__T__ = request()->param(\'t\');
                // 查询模型
                $__MODULE__ = \app\common\model\Module::find($__MODULEID__);
                // 查询tag字段名称
                $__TAGFIELD__ = \app\common\model\Field::where(\'module_id\', $__MODULEID__)
                    ->where(\'type\', \'tag\')
                    ->value(\'field\');
                // 当前模型
                $__MODEL__ = \'\app\common\model\\\\\' . $__MODULE__->model_name;
                $__LIST__ = $__MODEL__::where($__TAGFIELD__, "find in set", $__T__)
                ->order("' . $order . '")
                ->paginate("' . $pagesize . '");
                $page = $__LIST__->render();

                // 处理数据（把列表中需要处理的字段转换成数组和对应的值）
                $__LIST__ = changeFields($__LIST__,$__MODULEID__);
            ';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    // 标签云标签
    public function tagTagcloud($tag, $content)
    {
        $name  = $tag['name']  ?? "list";    // 不可为空
        $table = $tag['table'] ?? 'article'; // 表
        $limit = $tag['limit'] ?? '10';      // 条数
        $parse  = '<?php ';
        $parse .= '
                $__MODULE__ = \app\common\model\Module::where("table_name","' . strtolower($table) . '")->find();
                $__MODEL__ = \'\app\common\model\\\\\' . $__MODULE__->model_name;
                $__LIST__ = $__MODEL__::where("tags", "<>", "")
                    ->field(\'tags\')
                    ->select()
                    ->toArray();
                // 处理数据
                $__LIST__ = get_tagcloud($__LIST__, $__MODULE__->id, ' . $limit . ');
            ';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    // 详情上一篇
    public function tagPrev($tag)
    {
        $len = $tag['len'] ?: '500';
        $str = '<?php ';
        $str .= '
                $__CATEID__ = getCateId();
                $__CATE__   = \app\common\model\Cate::find($__CATEID__);
                $__MODEL__  = \'\app\common\model\\\\\' . $__CATE__->module->getData(\'model_name\');
                $__PREV__   = $__MODEL__::where(\'cate_id\',$__CATEID__)
                    ->where(\'id\',\'<\',input(\'id\'))
                    ->where(\'status\',\'=\',1)
                    ->field(\'id,cate_id,title\')
                    ->order(\'sort ASC,id DESC\')
                    ->find();
                if($__PREV__){
                    //处理字数
                    if(' . $len . '<>500){
                       $__PREV__[\'title\'] = mb_substr($__PREV__[\'title\'],0,' . $len . ');
                    }
                    //处理上一篇中的URL
                    $__PREV__[\'url\'] = getShowUrl($__PREV__);
                    $__PREV__ = "<a class=\"prev\" title=\" ".$__PREV__[\'title\']." \" href=\" ".$__PREV__[\'url\']." \">".$__PREV__[\'title\']."</a>";
                }else{
                    $__PREV__ = "<a class=\"prev_no\" href=\"javascript:;\">暂无数据</a>";
                }
                echo $__PREV__;
                ';
        $str .= '?>';
        return $str;
    }

    // 详情下一篇
    public function tagNext($tag)
    {
        $len = $tag['len'] ?: '500';
        $str = '<?php ';
        $str .= '
                $__CATEID__ = getCateId();
                $__CATE__   = \app\common\model\Cate::find($__CATEID__);
                $__MODEL__  = \'\app\common\model\\\\\' . $__CATE__->module->getData(\'model_name\');
                $__PREV__   = $__MODEL__::where(\'cate_id\',$__CATEID__)
                    ->where(\'id\',\'>\',input(\'id\'))
                    ->where(\'status\',\'=\',1)
                    ->field(\'id,cate_id,title\')
                    ->order(\'sort ASC,id DESC\')
                    ->find();
                if($__PREV__){
                    //处理字数
                    if(' . $len . '<>500){
                       $__PREV__[\'title\'] = mb_substr($__PREV__[\'title\'],0,' . $len . ');
                    }
                    //处理上一篇中的URL
                    $__PREV__[\'url\'] = getShowUrl($__PREV__);
                    $__PREV__ = "<a class=\"prev\" title=\" ".$__PREV__[\'title\']." \" href=\" ".$__PREV__[\'url\']." \">".$__PREV__[\'title\']."</a>";
                }else{
                    $__PREV__ = "<a class=\"prev_no\" href=\"javascript:;\">暂无数据</a>";
                }
                echo $__PREV__;
                ';
        $str .= '?>';
        return $str;
    }

    // 字典类型
    public function tagDict($tag, $content)
    {
        $name     = $tag['name'] ?? 'dictionary';
        $dictType = $tag['dict_type'] ?? 0;
        $field    = $tag['field'] ?? 'type';
        $all      = $tag['all'] ?? '全部';
        $parse = '<?php ';
        $parse .= '$__DICTS__ = \app\common\model\Dictionary::where(\'status\',1)->where(\'dict_type\',' . $dictType . ')->order(\'sort ASC,id desc\')->select()->toArray();
                   $__DICTS__ = changeDict($__DICTS__, \'' . $field . '\', \'' . $all . '\');
        ';
        $parse .= ' ?>';
        $parse .= '{volist name="__DICTS__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }
}