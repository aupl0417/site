<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/21
 * Time: 11:14
 */

namespace Common\Model;


use Think\Model\ViewModel;

class GoodsComplaintsViewModel extends ViewModel
{
    protected $tableName = 'goods_complaints';

    public $viewFields   = [
        'goods_complaints'  =>  ['*'],
        'goods'             =>  ['goods_name', 'images' => 'goods_images', 'price', 'status' => 'goods_status', '_on' => 'goods_complaints.goods_id = goods.id'],
        'shop'              =>  ['shop_name', 'domain', '_on' => 'goods_complaints.shop_id = shop.id']
        //'goods_attr_list'   =>  ['id' => 'attr_id', 'price', '_on' => 'goods_attr_list.goods_id = goods_complaints.goods_id'],
    ];
}