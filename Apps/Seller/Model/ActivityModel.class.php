<?php
namespace Seller\Model;
use Think\Model;
class ActivityModel extends Model {
    protected $tableName    =   'activity';
    protected $_validate    =   [
        ['type_id', 'require', '活动类型不能为空', 1],
        ['type_id', 'checkType', '活动类型不正确', 1, 'callback'],
        ['type_id', 'isUseType', 'activityIsUseTypeMsg', 1, 'callback', 1],
        ['start_time', 'require', '活动开始时间不能为空', 1],
        ['end_time', 'require', '活动结束时间不能为空', 1],
        ['shop_id', 'require', '商家不能为空', 1],
        ['full_money', 'isNumber', 'fullMoneyCheckMinPrice', 1, 'callback'],
        ['full_money', 'typeEq3AndEq2', '最低消费不能为空', 1, 'callback'],
        ['full_value', 'isNull', 'activityIsNullMsg', 1, 'callback'],
        ['full_value', 'countGoodsNum', "activityCountGoodsNumFunc", 1, 'callback'],
        ['full_value', 'valueNotCanMaxMoney', '优惠金额不能大于最低消费金额', 1, 'callback'],
        ['full_value', 'isFreePress', '不包邮商品才能够参与此活动', 1, 'callback'],
        ['full_value', 'isSellerGoods', '必须是自己的商品', 1, 'callback'],
        ['max_num', 'isNumber', '限购必须为数字类型且不能小于0', 1, 'callback'],
        ['sku_num', 'isNumber', '库存数量必须为数字类型且不能小于0', 1, 'callback'],
        ['start_time', 'isNow', '活动开始时间必须大于当前时间', 1, 'callback'],
        ['end_time', 'isMaxStartTime', '活动结束时间必须大于活动开始时间1个小时以上且两者相差不能超过90天', 1, 'callback'],
    ];
    
    protected $_auto        =   [
        ['uid', 'getUid', 1, 'function'],
    ];
    
    /**
     * 判断商家提交的活动类型是否正确
     * @param unknown $var
     */
    protected function checkType($var) {
        if (!empty(getActivityType($var))) {
            return true;
        }
        return false;
    }
    
    /**
     * 判断值不能为负数及不能为非阿拉伯数字
     * @param unknown $var
     * @return boolean
     */
    protected function isNumber($var) {
        if (empty($var)) {
            return true;
        }
        //如果当前促销类型为秒杀，并且值小于0.1的时候返回false
        if (I('post.type_id') == 6 && ($var < 0.1)) {
            return false;
        }
        if (!is_numeric($var) || ($var < 0)) {
            return false;
        }
        return true;
    }
    
    /**
     * 判断活动开始时间是否为时间格式，及是否大于当前时间
     * @param unknown $var
     */
    protected function isNow($var) {
        $time   =   strtotime($var);
        if (!$time) {
            return false;
        }
        /**
         * 如果是编辑的话则可以不更改开始时间
         */
        if (I('post.id', 0, 'int') > 0) return true;
        if ($time < NOW_TIME) {
            return false;
        }
        return true;
    }
    
    /**
     * 判断结束时间是否大于开始时间
     * @param unknown $var
     */
    protected function isMaxStartTime($var) {
        $etime   =   strtotime($var);
        $stime   =   strtotime(I('post.start_time'));
        if (!$etime || !$stime) {
            return false;
        }
        if (($stime + 3600) > $etime || ($stime + (90 * 24 * 3600) < $etime)) {
            return false;
        }
        return true;
    }
    
    /**
     * 判断值
     * @param unknown $var
     */
    protected function isNull($var) {
        $type   =   I('post.type_id');
        if ($type == 1 || $type == 7) {//活动为包邮或升级的时候full_value字段可不填写
            return true;
        }
        if (empty($var)) {
            return false;
        }
        if (($type == 2 || $type == 5 || $type == 6) && !empty($var)) {
            return true;
        } elseif ($type == 3 && is_numeric($var)) {
            return true;
        } elseif ($type == 4 && is_numeric($var) && ($var < 10) && ($var >= 0.1)) {
            return true;
        }
        return false;
    }
    
    /**
     * 判断是否已经发布过免邮促销或唐宝支付或可升级
     * @param unknown $var
     */
    protected function isUseType($var) {
        $inArr  =   [1,4,7];
        if (in_array($var, $inArr) && $this->where(['uid' => getUid(), 'type_id' => $var, 'status' => ['in', '0,1']])->find()) {
            return false;
        }
        return true;
    }
    
    /**
     * 赠送礼品最多只能选5款
     * @param unknown $var
     */
    protected function countGoodsNum($var) {
        $typeArr    =   [2,5,6];
        $type       =   I('post.type_id');
        if (in_array($type, $typeArr)) {
            $config =   getSiteConfig();
            $max    =   $type == 2 ? $config['activity']['activity_get_max_num'] : $config['activity']['activity_spike_max_num'];
            $cnt    =   count(explode(',', trim($var, ',')));
            if ($cnt > $max) { //最多可赠送
                return false;
            }
        }
        return true;
    }
    
    /**
     * 满就减优惠活动判断优惠金额不能大于消费金额
     * @param unknown $var
     */
    protected function valueNotCanMaxMoney($var) {
        if (I('post.type_id') == 3) {
            if ($var >= I('post.full_money')) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * 如果活动类型为满就减或满就送的时候，则需消费金额full_money不能为空
     * @param unknown $var
     */
    protected function typeEq3AndEq2($var) {
        $inArr  =   [2,3];
        if (in_array(I('post.type_id'), $inArr)) {
            if (empty($var)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * 不包邮的商品才能够参与0元购、秒杀等活动
     * @param string $var
     */
    protected function isFreePress($var) {
        $type   =   I('post.type_id');
        $typeArr=   [5];  //0元购、秒杀才会做限制
        if (!in_array($type, $typeArr)) return true;
        $cnt    =   count(explode(',', trim($var, ',')));
        $count  =   M('goods')->where(['seller_id' => getUid(), 'id' => ['in', trim($var, ',')], 'free_express' => 0])->count();
        if ($cnt != $count) {
            return false;
        }
        return true;
    }
    
    /**
     * 判断商品是否为当前用户的商品
     * @param unknown $var
     */
    protected function isSellerGoods($var) {
        $type   =   I('post.type_id');
        $typeArr=   [5,6,2];  //0元购、秒杀才会做限制，赠送
        if (!in_array($type, $typeArr)) return true;
        $cnt    =   count(explode(',', trim($var, ',')));
        $count  =   M('goods')->where(['seller_id' => getUid(), 'id' => ['in', trim($var, ',')]])->count();
        if ($cnt != $count) {
            return false;
        }
        return true;
    }
}