<?php
namespace Common\Model;
use Think\Model\ViewModel;
class IsNotSpikeViewModel extends ViewModel {
    protected $tableName = 'orders_goods';
    
    public $viewFields = [
        'orders_goods' => ['*'],
        'orders_shop'  => ['_on' => 'orders_goods.s_id = orders_shop.id'], 
    ];
}