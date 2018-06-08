<?php
/**
 * 判断是否已经添加过满包邮，唐宝支付有折扣提示内容
 * return msg
 * @param int $type
 * @return string
 */
function activityIsUseTypeMsg($type) {
    $typeInfo   =   getActivityType($type);
    return '您已发布过' . $typeInfo['activity_name'] . '活动，请直接编辑即可。';
}

/**
 * 判断满就减，满就送，唐宝支付有折扣不能为空提示内容
 * @param int $type
 * @return string
 */
function activityIsNullMsg($type) {
    switch ($type) {
        case 2:
            $str    =   '礼品不能为空';
            break;
        case 3:
            $str    =   '优惠金额不能为空';
            break;
        case 4:
            $str    =   '优惠折扣不能为空';
            break;
        case 5:
            $str    =   '0元购商品不能为空';
            break;
        case 6:
            $str    =   '秒杀商品不能为空';
            break;
    }
    return $str;
}

/**
 * 最大数量
 * @param unknown $type
 */
function activityCountGoodsNumFunc($type) {
    $config =   getSiteConfig();
    $max    =   $type == 2 ? $config['activity']['activity_get_max_num'] : $config['activity']['activity_spike_max_num'];
    switch ($type) {
        case 2:
            $str    =   '赠送礼品数量不能小于1件且不能大于' . $max . '件';
            break;
        case 5:
            $str    =   '商品数量不能小于1件且不能大于' . $max . '件';
            break;
        case 6:
            $str    =   '商品数量不能小于1件且不能大于' . $max . '件';
            break;
    }
    return $str;
}

//消费金额必须为数字类型且不能小于0
function fullMoneyCheckMinPrice($type) {
    switch ($type) {
        case 6:
            $str    =   '秒杀价不能小于0.1元';
            break;
        default:
            $str    =   '消费金额必须为数字类型且不能小于0';
    }
    return $str;
}

/**
 * 优惠券最大面额 error message
 *
 * @param $price
 * @return string
 */
function couponMaxPriceCheck() {
    $config =   getSiteConfig('activity');
    return '优惠券面额不能大于' . $config['coupon_max'] . '元';
}

/**
 * listToTree
 *
 * @param $list             数据集合
 * @param string $pk        数据主键
 * @param int $root         顶级父id
 * @param string $pid       数据的pid
 * @param string $child     下级键
 * @return array            返回数据
 */
function listToTree($list, $pk = 'id', $root = 0, $pid = 'sid', $child = 'child') {
    $tmpData = [];
    $tree = [];
    foreach ($list as $k => $v) {
        $tmpData[$v[$pk]] =& $list[$k];
    }
    unset($k,$v);
    foreach ($list as $k => $v) {
        $parentId = $v[$pid];
        if ($root == $parentId) {
            $tree[] =& $list[$k];
        } else {
            if (isset($tmpData[$parentId])) {
                $parent =& $tmpData[$parentId];
                $parent[$child][] =& $list[$k];
            }
        }
    }
    return ($tree);
}

/**
 * subject: 获取商家menu数据列表
 * api: getShopMenu
 * author: Mercury
 * day: 2017-03-28 15:47
 * [字段名,类型,是否必传,说明]
 * @return array
 */
function getShopMenu() {
    $menu = M('shop_menu')->where(['status' => 1])->order('sort asc, id asc')->select();
    return listToTree($menu);
}