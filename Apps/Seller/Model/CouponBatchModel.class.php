<?php
namespace Seller\Model;
use Think\Model;
class CouponBatchModel extends Model {
    protected $tableName    =   'coupon_batch';
    
    protected $_validate    =   [
        ['price', 'isNumber', '面值金额必须为数字类型', 1, 'callback'],
        ['price', 'min1', '面值金额不能小于1元', 1, 'callback'],
        ['price', 'couponMaxPriceCheck', 'couponMaxPriceCheck', 1, 'callback'],
        ['num', 'isNumber', '发行数量必须为数字类型', 2, 'callback'],
        ['num', 'min1', '发行数量不能小于1', 2, 'callback'],
        ['min_price', 'isNumber', '消费金额必须为数字类型', 1, 'callback'],
        ['min_price', 'maxPrice', '消费金额必须大于面值金额', 1, 'callback'],
        ['max_num', 'isNumber', '每人最多领取数量必须为数字类型', 1, 'callback'],
        ['max_num', 'min1', '每人最多领取数量不能小于1', 1, 'callback'],
        ['max_num', 'minNum', '每人最多领取数量不能大于发行数量', 1, 'callback'],
        ['sday', 'require', '生效时间不能为空', 1],
        ['sday', 'isDate', '生效时间不正确', 1, 'callback'],
        ['sday', 'sdayLowNowTime', '生效时间不能小于当前时间', 1, 'callback'],
        ['eday', 'require', '失效时间不能为空', 1],
        ['eday', 'isDate', '失效时间不正确', 1, 'callback'],
        ['eday', 'isMaxSday', '失效时间必须大于生效时间一天以上且两者不能相差30天以上', 1, 'callback'],
    ];

    protected $_auto        =   [
        ['uid', 'getUid', 1, 'function'],
        ['shop_id', 'getShopId', 1, 'function'],
        //['b_no', 'createOrdersNumber', 1, 'function'],
        ['ip', 'get_client_ip', 3, 'function'],
        //['cate_id', 'getCategory', 1, 'callback'],
    ];
    
    /**
     * 是否为数字类型
     * @param unknown $var
     */
    protected function isNumber($var) {
        if (is_numeric($var) && intval($var) > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 变量不能小于1
     * @param unknown $var
     */
    protected function min1($var) {
        if (intval($var) >= 1) {
            return true;
        }
        return false;
    }
    
    /**
     * 每人领取数量不能大于发行数量
     * @param unknown $var
     */
    protected function minNum($var) {
        $num = !empty(I('post.num')) ? intval(I('post.num')) : 0;
        if($num == 0) return true;
        if (intval($var) > $num) return false;
    }
    
    /**
     * 消费金额不能小于面值金额
     * @param unknown $var
     */
    protected function maxPrice($var) {
        if ($var > I('post.price')) {
            return true;
        }
        return false;
    }
    
    /**
     * 结束时间必须大于开始时间
     * @param unknown $var
     */
    protected function isMaxSday($var) {
        $eTime  =   strtotime($var);
        $sTime  =   strtotime(I('post.sday'));
        if ($sTime + (3600 * 24) <= $eTime && $sTime + (24 * 3600 * 30) >= $eTime) {//间隔不能超过30天
            return true;
        }
        return false;
    }
    
    /**
     * 必须为时间类型
     * @param unknown $var
     */
    protected function isDate($var) {
        $time   =   strtotime($var);
        if ($time) {
            return true;
        }
        return false;
    }
    
    /**
     * 优惠券生效时间不能小于当前时间
     * @param unknown $var
     */
    protected function sdayLowNowTime($var) {
        $sday   =   strtotime($var);
        if ($sday >= strtotime(date('Y-m-d', NOW_TIME))) {
            return true;
        }
        return false;
    }


    protected function getCategory() {
        $cates = M('shop')->where(['id' => getShopId()])->getField('category_id');
    }


    /**
     * 判断面额是否在限定金额内
     *
     * @param $var
     * @return bool
     */
    protected function couponMaxPriceCheck($var) {
        if ($var > C('cfg.activity')['coupon_max']) {
            return false;
        }
        return true;
    }
}