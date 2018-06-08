<?php
namespace Common\Model;
use Think\Model\ViewModel;
class ActivityOrdersShopGoodsViewModel extends ViewModel {
    protected $tableName    =   'orders_shop';
    
    public $viewFields      =   [
        'orders_shop'       =>  ['*'],
        'orders_goods'      =>  ['num', 'goods_id', '_on' => 'orders_shop.id = orders_goods.s_id'],
    ];
}