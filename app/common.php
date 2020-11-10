<?php
// 应用公共文件

// 获取列表链接地址
function getUrl($v)
{
    // 判断是否外部链接
    if (trim($v['url']) == '') {
        // 判断是否跳转到下级栏目
        if ($v['is_next'] == 1) {
            $is_next = \app\common\model\Cate::where('parent_id', $v['id'])
                ->order('sort asc,id desc')
                ->find();
            if ($is_next) {
                $v['url'] = getUrl($is_next);
            }
        } else {
            if ($v['cate_folder']) {
//                $v['url'] = (string)\think\facade\Route::buildUrl($v['cate_folder'] . '/index')->domain('');
                $v['url'] = url($v['cate_folder'] . '/index',[],false);
            } else {
                $moduleName = \app\common\model\Module::where('id', $v['module_id'])
                    ->value('model_name');
//                $v['url'] = (string)\think\facade\Route::buildUrl($moduleName . '/index', ['cate' => $v['id']])->domain('');
                $v['url'] = url($moduleName . '/index',[],false);
            }
        }
    }
    return $v['url'];
}

// 获取详情URL
function getShowUrl($v)
{
    if ($v) {
        if (isset($v['url']) && !empty($v['url'])) {
            return $v['url'];
        }
        $cate = \app\common\model\Cate::field('id,cate_folder,module_id')
            ->where('id', $v['cate_id'])
            ->find();
        print_r($v['cate_id']);exit();
        if ($cate && $cate['cate_folder']) {
            $url = (string)\think\facade\Route::buildUrl($cate['cate_folder'] . '/info', ['id' => $v['id']])->domain('');
        } else {
            $moduleName = \app\common\model\Module::where('id', $cate['module_id'])
                ->value('model_name');
            $url = (string)\think\facade\Route::buildUrl($moduleName . '/info', ['cate' => $cate['id'], 'id' => $v['id']])->domain('');
        }
    }
    return $url;
}

/***
 * 处理数据（把列表中需要处理的字段转换成数组和对应的值,用于自定义标签文件中）
 * @param $list      列表
 * @param $moduleid  模型ID
 * @return array
 */
function changeFields($list, $moduleid)
{
    $info = [];
    foreach ($list as $k => $v) {
        $url = getShowUrl($v);
        $list[$k] = changeField($v, $moduleid);
        $info[$k] = $list[$k];//定义中间变量防止报错
        $info[$k]['url'] = $url;
    }
    return $info;
}

/***
 * 处理数据（用于详情页中数据转换）
 * @param $info      内容详情
 * @param $moduleid  模型ID
 * @return array
 */
function changefield($info, $moduleId)
{
    $fields = \app\common\model\Field::where('module_id', '=', $moduleId)
        ->select();
    foreach ($fields as $k => $v) {
        $field = $v['field'];
        if ($info[$field]) {
            switch ($v['type']) {
                case 'textarea'://多行文本
                    break;
                case 'editor'://编辑器
                    $info[$field] = $info[$field];
                    break;
                case 'select'://下拉列表
                    break;
                case 'radio'://单选按钮
                    break;
                case 'checkbox'://复选框
                    $info[$field] = explode(',', $info[$field]);
                    break;
                case 'images'://多张图片
                    $info[$field] = json_decode($info[$field], true);
                    break;
                case 'tag'://TAG标签
                    if (!empty($info[$field])) {
                        $tags = explode(',', $info[$field]);
                        foreach ($tags as $k => $tag) {
                            $tags[$k] = [
                                'name' => $tag,
                                'url' => \think\facade\Route::buildUrl('index/tag', ['module' => $moduleId, 't' => $tag])->__toString(),
                            ];
                        }
                        $info[$field] = $tags;
                    }
                default:
            }
        }

    }
    return $info;
}

/**
 * 邮件发送
 * @param $to    接收人
 * @param string $subject 邮件标题
 * @param string $content 邮件内容(html模板渲染后的内容)
 * @throws Exception
 * @throws phpmailerException
 */
function send_email($to, $subject = '', $content = '')
{
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $arr = \think\facade\Db::name('config')
        ->where('inc_type', 'smtp')
        ->select();
    $config = convert_arr_kv($arr, 'name', 'value');

    $mail->CharSet = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    //调试输出格式
    //$mail->Debugoutput = 'html';
    //smtp服务器
    $mail->Host = $config['smtp_server'];
    //端口 - likely to be 25, 465 or 587
    $mail->Port = $config['smtp_port'];

    if ($mail->Port == '465') {
        $mail->SMTPSecure = 'ssl';
    }// 使用安全协议
    //Whether to use SMTP authentication
    $mail->SMTPAuth = true;
    //发送邮箱
    $mail->Username = $config['smtp_user'];
    //密码
    $mail->Password = $config['smtp_pwd'];
    //Set who the message is to be sent from
    $mail->setFrom($config['smtp_user'], $config['email_id']);
    //回复地址
    //$mail->addReplyTo('replyto@example.com', 'First Last');
    //接收邮件方
    if (is_array($to)) {
        foreach ($to as $v) {
            $mail->addAddress($v);
        }
    } else {
        $mail->addAddress($to);
    }

    $mail->isHTML(true);// send as HTML
    //标题
    $mail->Subject = $subject;
    //HTML内容转换
    $mail->msgHTML($content);
    return $mail->send();
}

/**
 * 验证输入的邮件地址是否合法
 * @param $user_email 邮箱
 * @return bool
 */
function is_email($user_email)
{
    $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
    if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false) {
        if (preg_match($chars, $user_email)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * 验证输入的手机号码是否合法
 * @param $mobile_phone 手机号
 * @return bool
 */
function is_mobile_phone($mobile_phone)
{
    $chars = "/^13[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$|17[0-9]{1}[0-9]{8}$/";
    if (preg_match($chars, $mobile_phone)) {
        return true;
    }
    return false;
}

/**
 * 过滤数组元素前后空格 (支持多维数组)
 * @param $array 要过滤的数组
 * @return array|string
 */
function trim_array_element($array)
{
    if (!is_array($array))
        return trim($array);
    return array_map('trim_array_element', $array);
}

/**
 * 将数据库中查出的列表以指定的 值作为数组的键名，并以另一个值作为键值
 * @param $arr
 * @param $key_name
 * @return array
 */
function convert_arr_kv($arr, $key_name, $value)
{
    $arr2 = array();
    foreach ($arr as $key => $val) {
        $arr2[$val[$key_name]] = $val[$value];
    }
    return $arr2;
}

function string2array($info)
{
    if ($info == '') return array();
    eval("\$r = $info;");
    return $r;
}

function array2string($info)
{
    //删除空格，某些情况下字段的设置会出现换行和空格的情况
    if (is_array($info)) {
        if (array_key_exists('options', $info)) {
            $info['options'] = trim($info['options']);
        }
    }
    if ($info == '') return '';
    if (!is_array($info)) {
        //删除反斜杠
        $string = stripslashes($info);
    }
    foreach ($info as $key => $val) {
        $string[$key] = stripslashes($val);
    }
    $setup = var_export($string, TRUE);
    return $setup;
}

/**
 * 文本域中换行标签输出
 * @param $info 内容
 * @return mixed
 */
function textareaBr($info)
{
    $info = str_replace("\r\n", "<br />", $info);
    $info = str_replace("\n", "<br />", $info);
    $info = str_replace("\r", "<br />", $info);
    return $info;
}

/**
 * 无限分类-栏目
 * @param $cate
 * @param string $lefthtml
 * @param int $pid
 * @param int $lvl
 * @return array
 */
function tree_cate($cate, $leftHtml = '|— ', $pid = 0, $lvl = 0)
{
    $arr = array();
    foreach ($cate as $v) {
        if ($v['parent_id'] == $pid) {
            $v['lvl'] = $lvl + 1;
            $v['left_html'] = str_repeat($leftHtml, $lvl);
            $v['l_cate_name'] = $v['left_html'] . $v['cate_name'];
            $arr[] = $v;
            $arr = array_merge($arr, tree_cate($cate, $leftHtml, $v['id'], $lvl + 1));
        }
    }
    return $arr;
}

/**
 * 组合多维数组
 * @param $cate
 * @param string $name
 * @param int $pid
 * @param string $separator
 * @return array
 */
function unlimitedForLayer($cate, $name = 'sub', $pid = 0, $separator = ',')
{

    $arr = array();
    foreach ($cate as $v) {
//        echo $separator.$pid.$separator."-". $separator.$v['parent_id'].$separator."\r\n";
        if (strstr($separator.$pid.$separator,$separator.$v['parent_id'].$separator) !== false ) {
            $v[$name] = unlimitedForLayer($cate, $name, $v['id']);
            $v['url'] = getUrl($v);

            if ($v['ico_image']) {
                if (strstr($v['ico_image'], ":before") !== false) {
                    $v['before_ico'] = str_replace(':before','',$v['ico_image']);
                } else {
                    $v['after_ico'] = str_replace(':after','',$v['ico_image']);
                }
            }

            $arr[] = $v;
        }
    }

//    print_r($arr);exit();
    return $arr;
}

/**
 * 传递一个父级分类ID返回当前子分类
 * @param $cate
 * @param $pid
 * @return array
 */
function getChildsOn($cate, $pid)
{
    $arr = array();
    foreach ($cate as $v) {
        if ($v['parent_id'] == $pid) {
            $v['sub'] = getChilds($cate, $v['id']);
            $v['url'] = getUrl($v);
            $arr[] = $v;
        }
    }
    return $arr;
}

/**
 * 传递一个父级分类ID返回所有子分类
 * @param $cate
 * @param $pid
 * @return array
 */
function getChilds($cate, $pid)
{
    $arr = array();
    foreach ($cate as $v) {
        if ($v['parent_id'] == $pid) {
            $v['url'] = getUrl($v);
            $arr[] = $v;
            $arr = array_merge($arr, getChilds($cate, $v['id']));
        }
    }
    return $arr;
}

/**
 * 传递一个父级分类ID返回所有子分类ID
 * @param $cate
 * @param $pid
 * @return array
 */
function getChildsId($cate, $pid)
{
    $arr = [];
    foreach ($cate as $v) {
        if ($v['parent_id'] == $pid) {
            $arr[] = $v;
            $arr = array_merge($arr, getChildsId($cate, $v['id']));
        }
    }
    return $arr;
}

/**
 * 格式化分类数组为字符串
 * @param $ids
 * @param string $pid
 * @return string
 */
function getChildsIdStr($ids, $pid = '')
{
    $result = '';
    foreach ($ids as $k => $v) {
        $result .= $v['id'] . ',';
    }
    if ($pid) {
        $result = $pid . ',' . $result;
    }
    $result = rtrim($result, ',');
    return $result;
}

/**
 * 传递一个子分类ID返回所有的父级分类[前台栏目]
 * @param $cate
 * @param $id
 * @return array
 */
function getParents($cate, $id)
{
    $arr = array();
    foreach ($cate as $v) {
        if ($v['id'] == $id) {
            $arr[] = $v;
            $arr = array_merge(getParents($cate, $v['parent_id']), $arr);
        }
    }
    return $arr;
}

/**
 * 查找一个分类id的顶级分类id
 * @param $id
 * @return string
 */
function getTopId($id)
{
    $cate = \app\common\model\Cate::field('id,parent_id')->select()->toArray();
    $cateArr = [];
    if ($cate) {
        foreach ($cate as $k => $v) {
            $cateArr[$v['id']] = $v['parent_id'] ?: "0";
        }
    }
    while ($cateArr[$id]) {
        $id = $cateArr[$id];
    }
    return $id;
}

/**
 * 获取文件目录列表
 * @param string $pathname 路径
 * @param integer $fileFlag 文件列表 0所有文件列表,1只读文件夹,2是只读文件(不包含文件夹)
 * @param string $pathname 路径
 * @return array
 */
function get_file_folder_List($pathname, $fileFlag = 0, $pattern = '*')
{
    $fileArray = array();
    $pathname = rtrim($pathname, '/') . '/';
    $list = glob($pathname . $pattern);
    foreach ($list as $i => $file) {
        switch ($fileFlag) {
            case 0:
                $fileArray[] = basename($file);
                break;
            case 1:
                if (is_dir($file)) {
                    $fileArray[] = basename($file);
                }
                break;

            case 2:
                if (is_file($file)) {
                    $fileArray[] = basename($file);
                }
                break;

            default:
                break;
        }
    }

    if (empty($fileArray)) $fileArray = NULL;
    return $fileArray;
}

/**
 * 获取所有模版
 * @return mixed
 */
function getTemplate()
{
    // 查找所有系统设置表数据
    $system = \app\common\model\System::find(1);

    $path = './template/' . $system['template'] . '/index/' . $system['html'] . '/';
    $tpl['list'] = get_file_folder_List($path, 2, '*_list*');
    $tpl['show'] = get_file_folder_List($path, 2, '*_show*');
    return $tpl;
}

/**
 * 传递一个父级分类ID返回所有子分类
 * @param $cate
 * @param $pid
 * @return array
 */
function getChildsRule($rules, $pid)
{
    $arr = [];
    foreach ($rules as $v) {
        if ($v['pid'] == $pid) {
            $arr[] = $v;
            $arr = array_merge($arr, getChildsRule($rules, $v['id']));
        }
    }
    return $arr;
}

/***
 * 对象转数组
 * @param $object
 * @return array
 */
function object2array($object)
{
    $array = array();
    if (is_object($object)) {
        foreach ($object as $key => $value) {
            $array[$key] = $value;
        }
    } else {
        $array = $object;
    }
    return $array;
}

/***
 * 获取当前栏目ID
 * @return mixed
 */
function getCateId()
{
    if (\think\facade\Request::has('cate')) {
        $result = (int)\think\facade\Request::param('cate');
    } else {
        $result = \app\common\model\Cate::where('cate_folder', '=', \think\facade\Request::controller())
            ->value('id');
    }
    return $result;
}

/**
 * 改变前台字典数据标签取得的数据
 * @param array $list
 * @return array
 */
function changeDict(array $list, string $field, string $all="全部")
{
    $get = \think\facade\Request::except(['page'], 'get');
    foreach ($list as $k => $v) {
        $url = $get;
        $url[$field] = $v['dict_value'];
        $list[$k]['url'] = (string)url(\think\facade\Request::controller() . '/' . \think\facade\Request::action(), $url);
        $param = \think\facade\Request::param('', '', 'htmlspecialchars');
        // 高亮显示
        $list[$k]['current'] = 0;
        if (!empty($param)) {
            foreach ($param as $kk => $vv) {
                if ($kk == $field) {
                    if (strpos($vv, '|') !== false) {
                        // 多选
                        $paramArr = explode("|", $vv);
                        foreach ($paramArr as $kkk => $vvv) {
                            if ($vvv == $v['dict_value']) {
                                $list[$k]['current'] = 1;
                                break;
                            }
                        }
                    } else {
                        // 单选
                        if ($vv == $v['dict_value']) {
                            $list[$k]['current'] = 1;
                        }
                    }
                }
            }
        }
        $list[$k]['param'] = $param;
    }

    // 添加[全部]字段在第一位
    if (isset($get[$field])) {
        unset($get[$field]);
    } else {
        $hover = 1;
    }
    $url = (string)url(\think\facade\Request::controller() . '/' . \think\facade\Request::action(), $get);

    $all = [
        'dict_label' => $all,
        'dict_value' => 0,
        'url'        => $url,
        'current'    => $hover ?? 0,
    ];
    array_unshift($list, $all);

    return $list;
}

/**
 * 改变模版标签中分类字段传递
 * @param string $field 需要分类查询的字段，通过,分割或|分割
 * @return string
 */
function getSearchField(string $field)
{
    $sql = '';
    if ($field) {
        $field = str_replace('|', ',', $field);
        $fieldArr = explode(',', $field);
        foreach ($fieldArr as $k => $v) {
            if (!empty($v)) {
                // 查询浏览器参数是否包含此参数
                if (\think\facade\Request::has($v, 'get')) {
                    $str = \think\facade\Request::get($v, '', 'htmlspecialchars');
                    if (strpos($str, '|') !== false) {
                        $sql = ' AND (';
                        $strArr = explode("|", $str);
                        foreach ($strArr as &$strAr) {
                            // 检测是否存在
                            $dictCount = \app\common\model\Dictionary::where('dict_value', $strAr)->count();
                            if ($dictCount) {
                                $sql .= ' FIND_IN_SET(\'' . $strAr . '\', ' . $v . ') OR';
                            }
                        }
                        // 去除最后一个or
                        $sql = substr($sql, 0, strlen($sql) - 2);
                        $sql .= ') ';
                    } else {
                        // 检测是否存在
                        $dictCount = \app\common\model\Dictionary::where('dict_value', $str)->count();
                        if ($dictCount) {
                            $sql .= ' AND FIND_IN_SET(\'' . $str . '\', ' . $v . ') ';
                        }
                    }
                }
            }
        }
    }
    return $sql;
}

/**
 * 无限分类-权限
 * @param $cate            栏目
 * @param string $lefthtml 分隔符
 * @param int $pid         父ID
 * @param int $lvl         层级
 * @return array
 */
function tree($cate , $lefthtml = '|— ' , $pid = 0 , $lvl = 0 ){
    $arr = array();
    foreach ($cate as $v){
        if ($v['pid'] == $pid) {
            $v['lvl']      = $lvl + 1;
            $v['lefthtml'] = str_repeat($lefthtml,$lvl);
            $v['ltitle']   = $v['lefthtml'].$v['title'];
            $arr[] = $v;
            $arr = array_merge($arr, tree($cate, $lefthtml, $v['id'], $lvl+1));
        }
    }
    return $arr;
}

/**
 * 无限分类-权限
 * @param $cate            栏目
 * @param string $lefthtml 分隔符
 * @param int $pid         父ID
 * @param int $lvl         层级
 * @return array
 */
function tree_three($cate , $lefthtml = '|— ' , $pid = 0 , $lvl = 0 ){
    $arr = array();
    foreach ($cate as $v){
        $keys = array_keys($v);
        if (end($v) == $pid) {
            $v['lvl']      = $lvl + 1;
            $v['lefthtml'] = str_repeat($lefthtml,$lvl);
            $v[$keys[1]] = $v['lefthtml'] . $v[$keys[1]];
            $arr[] = $v;
            $arr = array_merge($arr, tree_three($cate, $lefthtml, $v[$keys[0]], $lvl+1));
        }
    }
    return $arr;
}

/**
 * 标签云数据处理
 * @param $list
 * @return array
 */
function get_tagcloud($list, $moduleId, $limit = 10)
{
    $result = [];
    if ($list) {
        foreach ($list as $k => $v) {
            if ($v['tags']) {
                $arr = explode(',', $v['tags']);
                foreach ($arr as $ar) {
                    if (!empty($ar)) {
                        $result[] = $ar;
                    }
                }
            }
        }
    }
    // 截取
    if ($result) {
        $arr = array_count_values($result); // 统计数组中所有的值出现的次数
        arsort($arr); // 降序排序
        $arr = array_slice($arr, 0, $limit); // 截取前N条数据
        $result = [];
        foreach ($arr as $k => $v) {
            $result[] = [
                'name'  => $k,
                'count' => $v,
                'url'   => \think\facade\Route::buildUrl('index/tag', ['module' => $moduleId, 't' => $k])->__toString(),
            ];
        }
    }
    return $result;
}

/**
 * 获取前一页地址中设置的返回url
 * @return array
 */
function get_back_url()
{
    if (isset($_SERVER["HTTP_REFERER"]) && !empty($_SERVER["HTTP_REFERER"])) {
        $queryStr = explode('?', $_SERVER["HTTP_REFERER"]);
        if (count($queryStr) == 2) {
            parse_str($queryStr[1], $queryArr);
            if (isset($queryArr['back_url']) && !empty($queryArr['back_url'])) {
                $backUrl = explode("&", urldecode($queryArr['back_url']));
                foreach ($backUrl as $k => $v) {
                    $v = explode("=", $v);
                    if (isset($v[1]) && !empty($v[1])) {
                        $backArr[$v[0]] = $v[1];
                    }
                }
            }
        }
    }
    return $backArr ?? [];
}

/**
 * 　　* 下划线转驼峰
 * 　　* 思路:
 * 　　* step1.原字符串转小写,原字符串中的分隔符用空格替换,在字符串开头加上分隔符
 * 　　* step2.将字符串中每个单词的首字母转换为大写,再去空格,去字符串首部附加的分隔符.
 * 　　
 * @param $uncamelized_words
 * @param string $separator
 * @return string
 */
function toCamelCase($uncamelized_words,$separator='_')
{
    $uncamelized_words = $separator. str_replace($separator, " ", strtolower($uncamelized_words));
    return ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator );
}

/**
 * 　　* 驼峰命名转下划线命名
 * 　　* 思路:
 * 　　* 小写和大写紧挨一起的地方,加上分隔符,然后全部转小写
 * 　　
 * @param $camelCaps
 * @param string $separator
 * @return string
 */
function toUnderScore($camelCaps,$separator='_')
{
    return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
}

/**
 * 将日期按指定方式格式化
 * @param $date
 * @param string $f
 * @return mixed
 */
function dateFormat ($date, $f = 'Y-m-d H:i:s') {
    $time = strtotime($date);
    return date($f, $time);
}

/**
 * 将数字转换成千位字符串
 * @param $number
 * @return string
 */
function numberToKilobitString ($number) {
    if ($number > 1000) {
        $number = sprintf("%.1f",$number / 1000) . 'K';
    }
    return $number;
}

/**
 * 指定数组的键名
 * @param $arr
 * @param $key
 * @return array
 */
function keyby ($arr , $key = 'id') {
    $res = [];
    foreach ($arr as $k => $value) {
        $res[$value[$key]] = $value;
    }
    return $res;
}

/**
 * 为列表数据附加所属栏目信息
 * @param $list
 * @return mixed
 */
function listAttachCates($list) {
    $data = cache('cates')->column(null, 'id');
    foreach ($list as $k => $v) {
        $cates = [];
        $key = $v['cate_id'];
        
        if (!$v->url) $list[$k]['url'] = getShowUrl($v);

        $is_self_cate = true;

        while(true) {
            $cate = $data[$key] ?? null;

            if ($is_self_cate){
                $list[$k]['cate']   = $cate;
                $is_self_cate       = false;
            }

            if (empty($cate)) break;

            $cate->url      = getUrl($cate);
            $cates[]        = $cate;
            $key            = $cate['parent_id'];

        }
        $list[$k]['cates'] = array_reverse($cates);
    }
    return $list;
}

/**
 ** 截取中文字符串
 * @param $str
 * @param int $start
 * @param $length
 * @param string $charset
 * @param bool $suffix
 * @return false|string
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true){
    if(function_exists("mb_substr")){
        $slice= mb_substr($str, $start, $length, $charset);
    }elseif(function_exists('iconv_substr')) {
        $slice= iconv_substr($str,$start,$length,$charset);
    }else{
        $re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
        $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
        $re['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
        $re['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    $fix='';
    if(strlen($slice) < strlen($str)){
        $fix='...';
    }
    return $suffix ? $slice.$fix : $slice;
}

/**
 * 为列表数据附加所属栏目信息
 * @param $list
 * @return mixed
 */
function listAttachCate($list) {
    $cates = cache('cates')->column(null, 'id');
    foreach ($list as $k => $v) {
        $cate = $cates[$v['cate_id']] ?? null;
        $cate->url = getUrl($cate);
        $list[$k]['cate'] = $cate;
    }
    return $list;
}

/**
 * 获取所有模型
 * @return mixed|object|\think\App
 */
function getModules () {
    return cache('modules');
}

/**
 * 获取模型
 * @param $mid
 * @return mixed|object|\think\App
 */
function getModule ($mid) {
    if (empty($mid)) return null;
    $modules = getModules();
    $column = $modules->column(null,'id');
    if (!$column) return null;
    return $column[$mid] ?? null;
}

/**
 * 获取所有栏目
 * @return mixed|object|\think\App
 */
function getCates () {
    return cache('cates');
}

/**
 * 获取栏目
 * @param $cid
 * @return |null
 */
function getCate ($cid) {
    if (empty($cid)) return null;
    $cates = getCates();
    $column = $cates->column(null,'id');
    if (empty($column)) return null;
    return $column[$cid] ?? null;
}

/**
 *
 * @return string
 */
function createOrderId () {
    return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

/**
 * @param $length
 * @return string
 */
function createRandStr($length){
    //字符组合
    $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $len = strlen($str)-1;
    $randstr = '';
    for ($i=0;$i<$length;$i++) {
        $num=mt_rand(0,$len);
        $randstr .= $str[$num];
    }
    return $randstr;
}

/**
 *
 * @param $url
 * @param $config
 * @return string
 */
function siteUrl ($url, $config) {
    if (strstr($url,'://') === false) {
        return 'http://'.$config->url.$url;
    }
    return $url;
}

/**
 * 解析资源价格表并转换为map数组
 * @param $str
 * @param $dft
 * @return array
 */
function parsePricesToMap ($str, $dft) {
    $list   = explode(',',$str);
    $map    = [];
    foreach ($list as $row) {
        if (empty($row)) continue;
        $arr    = explode(':', $row);
        $tid    = $arr[0] ?? '';
        $value  = floatval($arr[1] ?? $dft);

        if ($tid) {
            $map[$tid] = $value;
        }
    }

    return $map;
}