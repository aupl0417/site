<?php
namespace Common\Model;
use Think\Model\ViewModel;
class ActivityRestrictionViewModel extends ViewModel {
    protected $tableName    =   'activity_participate';
    
    public $viewFields      =   [
        'activity_participate'  =>  ['*'],
        'orders_goods'          =>  ['num', 'goods_id', '_on' => 'orders_goods.s_no = activity_participate.s_no', '_type' => 'LEFT'],
    ];
}