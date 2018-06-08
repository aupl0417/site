<?php
namespace Common\Model;
use Think\Model\ViewModel;
class CouponRecom1ViewModel extends ViewModel {
    protected $tableName = 'coupon_recom';
    
    public $viewFields = [
        'coupon_recom'  => ['id', 'category_id', 'sort', 'coupon_id', 'status', 'num'],
        'coupon_batch'  => ['b_no', 'status' => 'batch_status', 'price', 'num' => 'batch_num', 'min_price', 'shop_id', 'atime', 'sday', 'eday', '_on' => 'coupon_recom.coupon_id = coupon_batch.id AND coupon_batch.status = 1'],
        'shop'          => ['shop_name', 'domain', 'shop_logo', '_on' => 'coupon_batch.shop_id = shop.id'],
    ];
}