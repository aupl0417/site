<?php
namespace Common\Model;
use Think\Model\ViewModel;
class AdminRefundViewModel extends ViewModel {
    protected $tableName = 'orders_goods';
    
    public $viewFields = [
        'orders_goods'  => ['*'],
        'user' => ['nick' => 'buy_nick', '_on' => 'orders_goods.uid = user.id'],
        'seller' => ['nick' => 'seller_nick', '_table' => '__USER__', '_on' => 'orders_goods.seller_id = seller.id'],
        'shop' => ['shop_name', 'shop_logo', '_on' => 'orders_goods.shop_id = shop.id'],
        'goods' => ['goods_name', 'images', '_on' => 'orders_goods.goods_id = goods.id'],
        'orders_shop' => ['status' => 'orders_status', 'refund_price' => 'orders_refund_price', 'refund_score' => 'orders_refund_score', 'refund_express' => 'orders_refund_express', 'refund_num' => 'orders_refund_num', 'service_num' => 'orders_service_num', '_on' => 'orders_goods.s_id = orders_shop.id'],
    ];
}