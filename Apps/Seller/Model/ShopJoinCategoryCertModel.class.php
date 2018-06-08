<?php
namespace Seller\Model;
use Think\Model;
class ShopJoinCategoryCertModel extends Model {
    protected $tableName    =   'shop_join_category_cert';
    
    protected $_validate    =   [
        ['cert_images', 'require', '证书照片不能为空', 1],
        ['cert_id', 'isInsert', '您已添加过当前证书', 1, 'callback', 1],
        ['cert_id', 'inCert', '当前证书不存在', 1, 'callback'],
    ];
    protected $_auto        =   [
        ['uid', 'getUid', 1, 'function'],
    ];
    
    /**
     * 判断当前用户是否已经添加过此证书
     * @param unknown $var
     */
    protected function isInsert($var) {
        if ($this->where(['uid' => getUid(), 'cert_id' => $var])->find()) {
            return false;
        }
        return true;
    }
    
    /**
     * 判断当前证书是否存在
     * @param unknown $var
     */
    protected function inCert($var) {
        $data   =   getCategoryCert();
        if (array_key_exists($var, $data)) {
            return true;
        }
        return false;
    }
}