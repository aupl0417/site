<?php
namespace Seller\Model;
use Think\Model;
class ShopModel extends Model {
    protected $tableName    =   'shop';
    protected $_validate    =   [
        ['shop_name', 'require', '店铺名称不能为空', 1],
        ['about', 'require', '店铺介绍不能为空', 1],
        ['qq', 'require', '腾讯QQ不能为空', 1],
        ['province', 'require', '省份不能为空', 1],
        ['city', 'require', '城市不能为空', 1],
        ['district', 'require', '地区不能为空', 1],
        ['street', 'require', '详细地址不能为空', 1],
        //['mobile', 'require', '手机号码不能为空', 1],
    ];
    
    protected $_auto    =   [
        ['uid', 'getUid', 1, 'function'],
        ['ip', 'get_client_ip', 3, 'function'],
        ['category_id', 'getCategory', 3, 'callback'],
    ];
    
    protected $insertFields =   ['shop_name', 'about', 'qq', 'province', 'city', 'district', 'town', 'street', 'uid', 'ip', 'type_id', 'category_id'];
    protected $updateFields =   ['shop_name', 'about', 'qq', 'province', 'city', 'district', 'town', 'street', 'ip', 'type_id', 'category_id'];
    
    /**
     * 获取店铺主营类目
     */
    protected function getCategory() {
        $cates  =   M('shop_join_category')->where(['uid' => getUid()])->getField('cates');
        if ($cates) {
            $cates .=   ',100845547';   //绑定其他类目
        } else {
            $cates  =   '100845547';    //绑定其他类目
        }
        return $cates;
    }
}