<?php
namespace Seller\Model;
use Think\Model\ViewModel;
class ShopJoinTrademarkViewModel extends ViewModel {
    protected $tableName    =   'shop_join_cert';
    public $viewFields      =   [
        'shop_join_cert'    =>  ['*', '_type' => 'RIGHT'],
        'shop_join_brand'   =>  ['b_name', 'id' => 'brand_id', '_on' => 'shop_join_cert.brand_id = shop_join_brand.id AND shop_join_brand.uid = shop_join_cert.uid', '_type' => 'LEFT'],
    ];
}