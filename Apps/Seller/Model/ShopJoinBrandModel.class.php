<?php
namespace Seller\Model;
use Think\Model;
class ShopJoinBrandModel extends Model {
    protected $tableName    =   'shop_join_brand';
    
    protected $_validate    =   [
        ['b_name', 'require', '品牌中文名称不能为空', 1],
        ['b_name', 'checkBrandNum', 'shopOpensCheckBrandNum', 1, 'callback'],
        ['b_logo', 'require', '品牌logo不能为空', 1],
        ['b_master', 'require', '品牌所有人不能为空', 1],
        ['b_master', 'checkImages', '品牌商标证书照片或商标授理书照片必填其中一项', 1, 'callback'],
    ];
    
    protected $_auto        =   [
        ['uid', 'getUid', 1, 'function'],
        ['ip', 'get_client_ip', 3, 'function'],
    ];
    
    /**
     * 品牌商标证书或商标授理书其中必须一项不能为空
     */
    protected function checkImages() {
        if (!empty(I('post.b_images')) || !empty(I('post.b_images2'))) {
            return true;
        }
        return false;
    }
    
    /**
     * 判断品牌数量
     */
    protected function checkBrandNum() {
        $num    =   $this->where(['uid' => getUid()])->count();
        $typeId   =   M('shop_join_contact')->where(['uid' => getUid()])->getField(['type_id']);
        $shopType   =   getShopType($typeId);
        if ($shopType['max_brand'] == 0 || $shopType['max_brand'] >= $num) {
            return true;
        }
        return false;
    }
}