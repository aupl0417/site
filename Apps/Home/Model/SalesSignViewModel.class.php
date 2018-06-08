<?php
namespace Home\Model;
use Think\Model\ViewModel;
class SalesSignViewModel extends ViewModel {
    public $viewFields = array(
        'sales_sign' => array('goods_id','id','active','seller_id'),
        'sales_floor' => array('ads_content', '_on' => 'sales_floor.id = sales_sign.cid'),
        'sales' => array('activity_start_time,activity_end_time', '_on' => 'sales.id = sales_sign.sales_id'),
    );
}