<?php
namespace Seller\Model;
use Think\Model\ViewModel;
class CouponReceiveViewModel extends ViewModel {
    protected $tableName    =   'coupon';
    
    public $viewFields      =   [
        'coupon'            =>  ['*'],
        'user'              =>  ['nick', '_on' => 'coupon.uid = user.id'],
    ];
}