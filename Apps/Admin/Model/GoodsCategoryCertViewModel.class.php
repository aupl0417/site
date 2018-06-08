<?php
namespace Admin\Model;
use Think\Model\ViewModel;
class GoodsCategoryCertViewModel extends ViewModel {
    protected $tableName    =   'goods_category_cert';
    public $viewFields      =   [
        'goods_category_cert'   =>  ['*'],
        'goods_category'        =>  ['category_name', '_on' => 'goods_category_cert.category_id = goods_category.id'],
    ];
}