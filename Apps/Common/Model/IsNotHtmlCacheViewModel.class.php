<?php
namespace Common\Model;
use Think\Model\ViewModel;
class IsNotHtmlCacheViewModel extends ViewModel {
    protected $tableName    =   'goods_attr_list';
    
    public $viewFields      =   [
        'goods_attr_list'   =>  ['*'],
        'goods'             =>  ['status', 'shop_id', 'fav_num', 'officialactivity_join_id', 'num' => 'sku_num', '_on' => 'goods.id = goods_attr_list.goods_id'],
    ];
}