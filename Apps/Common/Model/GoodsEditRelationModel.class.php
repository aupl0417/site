<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/3
 * Time: 10:39
 */

namespace Common\Model;


use Think\Model\RelationModel;

class GoodsEditRelationModel extends RelationModel
{
    protected $tableName='goods';
    protected $_link = array(
        'goods_attr_list'	=>array(
            'mapping_type'		=>self::HAS_MANY,
            'class_name'		=>'goods_attr_list',
            'foreign_key'		=>'goods_id',
            'mapping_key'		=>'id',
            'mapping_name'		=>'attr_list',
        ),
        'goods_attr_value'	=>array(
            'mapping_type'		=>self::HAS_MANY,
            'class_name'		=>'GoodsAttrValue',
            'foreign_key'		=>'goods_id',
            'mapping_key'		=>'id',
            'mapping_name'		=>'attr_value',
        ),
        'goods_content'     =>array(
            'mapping_type'		=>self::HAS_ONE,
            'class_name'		=>'goods_content',
            'foreign_key'		=>'goods_id',
            'mapping_key'		=>'id',
            'as_fields'         =>'content',
        ),
        'goods_param'	    =>array(
            'mapping_type'		=>self::HAS_MANY,
            'class_name'		=>'goods_param',
            'foreign_key'		=>'goods_id',
            'mapping_key'		=>'id',
            'mapping_name'		=>'params',
            'mapping_fields'    =>'option_id,param_value,id',
        ),
        'goods_collocation' =>array(    //搭配商品
            'mapping_type'		=>self::HAS_ONE,
            'class_name'		=>'goods_collocation',
            'foreign_key'		=>'goods_id',
            'mapping_key'		=>'id',
            'as_fields'         =>'collocations',
        ),
    );
}