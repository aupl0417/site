<?php
namespace Common\Model;
use Think\Model\ViewModel;
class CouponBatchViewModel extends ViewModel {
    protected $tableName    =   'coupon_batch';
    public $viewFields      =   [
        'coupon_batch'      =>  ['*'],
        'shop'              =>  ['shop_name', 'type_id', 'category_id', 'domain', 'shop_logo', '_on' => 'coupon_batch.shop_id = shop.id'],
    ];
}