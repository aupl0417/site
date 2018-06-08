<?php
namespace Seller\Model;
use Think\Model;
class ShopInventoryModel extends Model {
    protected $tableName    =   'shop_join_info';
    
    protected $_validate    =   [
        ['inventory_type', 'require', '结算方式不能为空.', 1],
        ['inventory_type', [0,1], '结算方式不正确', 1, 'in'],
        ['mobile', 'require', '手机号码不能为空', 1],
        ['mobile', 'isRegMobile', '手机号码非注册手机号', 1, 'callback'],
    ];
    
    protected $insertFields =   ['inventory_type', 'mobile'];
    protected $updateFields =   ['inventory_type', 'mobile'];
    
    /**
     * 判断手机号是否为注册号码
     * @param string $var
     */
    protected function isRegMobile($var) {
        if (M('user')->where(['id' => getUid(), 'mobile' => $var])->getField('id')) {
            return true;
        }
        return false;
    }
}