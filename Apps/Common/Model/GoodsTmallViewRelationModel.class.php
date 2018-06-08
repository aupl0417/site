<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/16
 * Time: 20:26
 */

namespace Common\Model;


use Think\Model\RelationModel;

class GoodsTmallViewRelationModel extends RelationModel
{
    protected $tableName = 'orders_shop';

    protected $_link     = [
        'orders'    =>  [
            'mapping_type'  =>  self::BELONGS_TO,
            'class_name'    =>  'orders',
            'foreign_key'   =>  'o_id',
            'mapping_key'   =>  'id',
            'mapping_name'  =>  'orders',
            'as_fields'     =>  'province,district,city,town,street,mobile,tel,linkname,postcode'
        ],
        'orders_goods'  =>  [
            'mapping_type'  =>  self::HAS_MANY,
            'class_name'    =>  'orders_goods',
            'foreign_key'   =>  's_id',
            'mapping_key'   =>  'id',
            'mapping_name'  =>  'goods',
        ],
        'user'          =>  [
            'mapping_type'  =>  self::HAS_ONE,
            'class_name'    =>  'user',
            'foreign_key'   =>  'id',
            'mapping_key'   =>  'uid',
            'mapping_name'  =>  'user',
            'as_fields'     =>  'nick',
        ],
    ];
}