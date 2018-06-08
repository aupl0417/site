<?php
namespace Seller\Model;
use Think\Model;
class ShopJoinCategoryModel extends Model {
    protected $tableName    =   'shop_join_category';
    
    protected $_validate    =   [
        ['cates', 'require', '分类不能为空', 1],
        ['cates', 'checkNumber', 'shopOpensCheckCateNum', 1, 'callback'],
        ['cates', 'checkCate', '所选分类不正确', 1, 'callback'],
    ];
    
    protected $_auto        =   [
        ['ip', 'get_client_ip', 3, 'function'],
        ['uid', 'getUid', 1, 'function'],
    ];
    
    /**
     * 判断数量
     * @param unknown $var
     */
    protected function checkNumber($var) {
        $cate   =   explode(',', $var);
        $count  =   count($cate);
        $typeId =   M('shop_join_contact')->where(['uid' => getUid()])->getField('type_id');
        $shopType   =   getShopType($typeId);
        if ($shopType['max_category'] == 0 || $shopType['max_category'] >= $count) {
            return true;
        }
        return false;
    }
    
    /**
     * 判断类目是否正确
     * @param unknown $var
     */
    protected function checkCate($var) {
        return checkOpensShopCategory($var);
    }
}