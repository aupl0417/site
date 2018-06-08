<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\ViewModel;
class Goodsadvisory189ViewModel extends ViewModel {
    public $viewFields = array(
'goods_advisory'  => ['*', '_type' => 'LEFT'],
        'goods' => ['goods_name', 'images', 'price', '_on' => 'goods_advisory.goods_id = goods.id', '_type' => 'LEFT'],
        'shop'  => ['shop_name', 'domain', '_on' => 'goods_advisory.shop_id = shop.id', '_type' => 'LEFT'],
        'user'  => ['nick', '_on' => 'goods_advisory.uid = user.id', '_type' => 'LEFT'],
        'ruser' => ['nick' => 'reply_nick', '_type' => 'LEFT', '_table' => '__USER__', '_as'=>'ruser', '_on' => 'goods_advisory.reply_uid = ruser.id'],
    );
}
?>