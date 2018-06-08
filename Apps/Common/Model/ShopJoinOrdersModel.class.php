<?php
namespace Common\Model;
use Think\Model;
class ShopJoinOrdersModel extends Model {
    protected $tableName    =   'shop_join_orders';
    protected $_auto        =   [
        ['uid', 'getUid', 1, 'function'],
        ['pay_time', 'getNowTime', 2, 'callback'],
        ['sign', 'getSign', 2, 'callback'],
        ['o_no', 'createOrdersNumber', 1, 'function'],
    ];
    
    protected function getNowTime() {
        return date('Y-m-d H:i:s', NOW_TIME);
    }
    
    protected function getSign() {
        return md5(getUid() . date('Y-m-d H:i:s', NOW_TIME) . C('CRYPT_PREFIX'));
    }
}