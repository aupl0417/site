<?php
namespace Seller\Model;
use Think\Model;
class ShopJoinInfoModel extends Model {
    protected $tableName    =   'shop_join_info';
    protected $_validate    =   [
        ['shop_name', 'require', '店铺名称不能为空', 1],
        ['about', 'require', '店铺介绍不能为空', 1],
        //['qq', 'require', '腾讯QQ不能为空', 1],
        ['province', 'require', '省份不能为空', 1],
        ['city', 'require', '城市不能为空', 1],
        ['district', 'require', '地区不能为空', 1],
        ['street', 'require', '详细地址不能为空', 1],
    ];
    
    protected $_auto        =   [
        ['type_id', 'getType', 1, 'callback'],
        ['uid', 'getUid', 1, 'function'],
        ['ip', 'get_client_ip', 3, 'function'],
    ];
    
    protected function getType() {
        return M('shop_join_contact')->where(['uid' => getUid()])->getField('type_id');
    }
}