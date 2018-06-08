<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\ViewModel;
class Couponrecom191ViewModel extends ViewModel {
    public $viewFields = array(
'coupon_recom' => ['*', '_type' => 'LEFT'],
'coupon_recom_category' => ['name', '_on' => 'coupon_recom_category.id = coupon_recom.category_id'],
'coupon_batch' => ['shop_id', 'b_no', 'price', 'num' => 'batch_num', 'get_num', 'max_num', 'get_num', 'use_num', 'min_price', 'sday', 'eday', '_on' => 'coupon_recom.coupon_id = coupon_batch.id'],
    );
}
?>