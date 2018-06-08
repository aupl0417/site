<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/14
 * Time: 11:35
 */

namespace Common\Model;


use Think\Model\ViewModel;

class GoodsAttrListByCollocationViewModel extends ViewModel
{
    protected $tableName = 'goods';

    public $viewFields = [
        'goods'             =>  ['id', 'goods_name', 'images', 'status', 'num'],
        'goods_attr_list'   =>  ['price', 'id' => 'attr_id', '_on' => 'goods.id = goods_attr_list.goods_id'],
    ];
}