<?php
namespace Seller\Model;
use Think\Model;
class ShopJoinProductsModel extends Model {
    protected $tableName    =   'shop_join_products';
    
    protected $_validate    =   [
        ['pro_images', 'require', '产品图片不能为空', 1],
        ['pro_images', 'count5', '产品图片最多可上传5张', 1, 'callback'],
        ['cert_images', 'require', '相关资质图片不能为空', 1],
        ['cert_images', 'count5', '相关资质图片最多可上传5张', 1, 'callback'],
        ['intro', 'require', '产品说明不能为空', 1],
        ['intro', '10,300', '产品说明应保持在10-300个字符之间', 1, 'length'],
        ['uid', 'unique', '您已提交过，请直接编辑', 1, 'function', 1],
    ];
    
    protected $_auto        =   [
        ['uid', 'getUid', 1, 'function'],
    ];
    
    /**
     * 最多上传5张图片
     * @param unknown $var
     */
    protected function count5($var) {
        $var    =   trim($var, ',');
        $varArr =   explode(',', $var);
        if (count($varArr) < 6) {
            return true;
        }
        return false;
    }
}