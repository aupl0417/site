<?php
namespace Home\Model;
use Think\Model\ViewModel;
class AdGoodsAttrListViewModel extends ViewModel {
    protected $tableName = 'goods_attr_list';
    
    public $viewFields = [
        'goods_attr_list'   =>  ['*'],
        'goods'             =>  ['goods_name', 'sub_name', '_on' => 'goods_attr_list.goods_id = goods.id'],
    ];
}