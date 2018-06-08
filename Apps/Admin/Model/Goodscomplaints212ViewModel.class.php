<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\ViewModel;
class Goodscomplaints212ViewModel extends ViewModel {
    public $viewFields = array(
'goods_complaints'  =>  ['*'],
'goods'             =>  ['goods_name', 'images' => 'goods_images', 'price', 'status' => 'goods_status', '_on' => 'goods_complaints.goods_id = goods.id'],
'shop'              =>  ['shop_name', 'domain', '_on' => 'goods_complaints.shop_id = shop.id'],
'user'              =>  ['nick', '_on' => 'goods_complaints.uid= user.id'],
    );
}
?>