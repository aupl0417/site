<?php
namespace Seller\Model;
use Think\Model\ViewModel;
class ShopJoinInfoLogsViewModel extends ViewModel {
    protected $tableName    =   'shop_join_info';
    public $viewFields      =   [
            'shop_join_info'=>  ['*'],
            'shop_join_logs'=>  ['shop_join_id', 'reason', '_on' => 'shop_join_logs.shop_join_id = shop_join_info.id'],
    ];
}