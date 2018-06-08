<?php
namespace Seller\Model;
use Think\Model\ViewModel;
class ShopJoinCategoryCertViewModel extends ViewModel {
    protected $tableName    =   'shop_join_category_cert';
    public $viewFields      =   [
        'shop_join_category_cert'   =>  ['id', 'cert_images', 'atime', 'expire', 'uid', '_type' => 'RIGHT'],
        'goods_category_cert'       =>  ['id' => 'cert_id', 'cert_name', '_on' => 'goods_category_cert.id = shop_join_category_cert.cert_id AND goods_category_cert.status = 1'],
    ];
}