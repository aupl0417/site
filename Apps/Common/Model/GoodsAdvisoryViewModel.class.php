<?php
/**
 * 商品咨询视图模型
 */
namespace Common\Model;
use Think\Model\ViewModel;
class GoodsAdvisoryViewModel extends ViewModel {
    protected $tableName = 'goods_advisory';
    
    public $viewFields = [
        'goods_advisory'  => ['*', '_type' => 'LEFT'],
        'goods' => ['goods_name', 'images', 'price', '_on' => 'goods_advisory.goods_id = goods.id', '_type' => 'LEFT'],
        'shop'  => ['shop_name', 'domain', '_on' => 'goods_advisory.shop_id = shop.id', '_type' => 'LEFT'],
        'user'  => ['nick', '_on' => 'goods_advisory.uid = user.id', '_type' => 'LEFT'],
        'seller'=> ['nick' => 'seller_nick', '_table' => '__USER__', '_as' => 'seller', '_on' => 'goods_advisory.shop_id = seller.shop_id', '_type' => 'LEFT'],
        'ruser' => ['nick' => 'reply_nick', '_type' => 'LEFT', '_table' => '__USER__', '_as'=>'ruser', '_on' => 'goods_advisory.reply_uid = ruser.id'],
    ];
}