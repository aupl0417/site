<?php
/**
 * 判断是否已经添加过满包邮，唐宝支付有折扣提示内容
 * return msg
 * @param int $type
 * @return string
 */
 /*
function activityIsUseTypeMsg($type) {
    $typeInfo   =   getActivityType($type);
    return '您已发布过' . $typeInfo['activity_name'] . '活动，请直接编辑即可。';
}
*/

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