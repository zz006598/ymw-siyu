<?php
return array(
    'tpl_index' => 'sucaihu',
    'attr' => array(
        'index_hot_cate_id' => [
            'group' => '首页',
            'type' => 'input',
            'label' => '首页热门专区栏目ID',
            'placeholder' => '多个请用逗号隔开',
            'default' => '1,3,5'
        ],
        'index_vip_cate_id' => [
            'group' => '首页',
            'type' => 'input',
            'label' => '首页会员专享栏目ID',
            'placeholder' => '多个请用逗号隔开',
            'default' => '1,4,7,8'
        ],
        'index_cms_cate_id' => [
            'group' => '首页',
            'type' => 'input',
            'label' => '首页CMS栏目ID',
            'placeholder' => '多个请用逗号隔开',
            'default' => '5'
        ],
        'index_notice_cate_id' => [
            'group' => '首页',
            'type' => 'input',
            'label' => '首页公告栏目ID',
            'default' => 6
        ],
        'index_floor_cate_id' => [
            'group' => '首页',
            'type' => 'input',
            'label' => '首页楼层栏目ID',
            'placeholder' => '多个请用逗号隔开'
        ],
        'index_bottom_list_id' => [
            'group' => '首页',
            'type' => 'input',
            'label' => '首页底部列表栏目ID',
            'placeholder' => '多个请用逗号隔开,最多3个'
        ],
        'common_footer_nav_cate_id' => [
            'group' => '公共页脚',
            'type' => 'input',
            'label' => '本站导航栏目ID',
            'placeholder' => '多个请用逗号隔开,最多3个',
            'default' => '1,2,3'
        ],
        'common_footer_more_cate_id' => [
            'group' => '公共页脚',
            'type' => 'input',
            'label' => '更多介绍栏目ID',
            'placeholder' => '多个请用逗号隔开,最多3个',
            'default' => '5,6'
        ],
        'other_start_date' => [
            'group' => '其他设置',
            'type'  => 'input',
            'label' => '本站开启时间',
            'placeholder' => '日期格式如 2020/12/12',
            'default' => '2020/10/01'
        ]
    )
);