<?php
namespace Seller\Model;
use Think\Model;
class ShopJoinContactModel extends Model {
    protected $tableName    =   'shop_join_contact';
    
    protected $_validate    =   [
        ['linkname', 'require', '店铺联系人不能为空', 1], 
        ['mobile', 'require', '店铺联系人手机号码不能为空', 1],
        //['tel', 'require', '店铺联系人电话号码不能为空', 1],
        ['email', 'require', '店铺联系人邮箱不能为空', 1],
        ['type_id', 'require', '店铺类型不能为空', 1],
        ['type_id', 'checkType', '店铺类型不正确', 1, 'callback'],
        ['rf_linkname', 'require', '退货联系人不能为空', 1],
        ['rf_mobile', 'require', '退货联系人手机不能为空', 1],
        ['rf_province', 'require', '退货省份不能为空', 1],
        ['rf_city', 'require', '退货城市不能为空', 1],
        ['rf_district', 'require', '退货地区不能为空', 1],
        ['rf_street', 'require', '退货详细地址不能为空', 1],
    ];
    
    protected $_auto        =   [
        ['uid', 'getUid', 1, 'function'],    
    ];
    
    protected function checkType($var) {
        if (M('shop_type')->where(['id' => $var, 'status' => 1])->find()) {
            return true;
        }
        return false;
    }
}