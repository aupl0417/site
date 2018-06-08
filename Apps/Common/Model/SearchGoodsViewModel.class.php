<?php
namespace Common\Model;
use Think\Model\ViewModel;
class SearchGoodsViewModel extends ViewModel {
    protected $tableName = 'goods';
    
    public $viewFields = [
        'goods'             =>  ['*'],
        //'goods_attr_list'   =>  ['id' => 'attr_list_id', '_on' => 'goods.id = goods_attr_list.goods_id','_type'=>'LEFT'],
        'goods_content'     =>  ['content', '_on' => 'goods.id = goods_content.goods_id'],
        'brand'             =>  ['b_name', '_on' => 'goods.brand_id = brand.id','_type'=>'LEFT'],
        'shop'              =>  ['shop_name', 'type_id', 'qq', '_on' => 'goods.shop_id = shop.id'],
        'goods_category'    =>  ['category_name', 'sid' => 'ssid', '_on' => 'goods_category.id = goods.category_id'],
        'cate'              =>  ['sid' => 'cate_id', '_table' => 'ylh_goods_category', '_on' => 'goods_category.sid = cate.id', '_type' => 'LEFT']
    ];
}