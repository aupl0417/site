<?php
namespace Common\Model;
use Think\Model\ViewModel;
class CouponRecomViewModel extends ViewModel {
    protected $tableName = 'coupon_batch';
    
    public $viewFields = [
        'coupon_batch' => ['*', '_type' => 'LEFT'],
        'coupon_recom' => ['category_id', 'atime' => 'addtime', 'status' => 'recom_status', '_as' => 'recom', '_on' => 'recom.coupon_id = coupon_batch.id'],
        'shop'         => ['shop_name', 'domain', 'shop_logo', '_on' => 'coupon_batch.shop_id = shop.id'],
    ];
}
