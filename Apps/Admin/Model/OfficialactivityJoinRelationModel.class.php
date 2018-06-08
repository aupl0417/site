<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class OfficialactivityJoinRelationModel extends RelationModel {
    protected $tableName    =   'officialactivity_join';
    protected $_link        =   [
        'goods_attr_list'    =>  [
            'mapping_type'  =>  self::HAS_ONE,
            'foreign_key'   =>  'goods_id',
            'mapping_key'   =>  'goods_id',
            'mapping_fields'=>  'id,attr_name,images,price,num',
        ],
        'goods'    =>  [
            'mapping_type'  =>  self::HAS_ONE,
            'foreign_key'   =>  'id',
            'mapping_key'   =>  'goods_id',
            'mapping_fields'=>  'goods_name,price',
        ],
        'user'    =>  [
            'mapping_type'  =>  self::HAS_ONE,
            'foreign_key'   =>  'id',
            'mapping_key'   =>  'uid',
            'mapping_fields'=>  'id,nick',
        ],
        'shop'    =>  [
            'mapping_type'  =>  self::HAS_ONE,
            'foreign_key'   =>  'uid',
            'mapping_key'   =>  'uid',
            'mapping_fields'=>  'id,shop_name,domain',
        ],
        'officialactivity_contact'    =>  [
            'mapping_type'  =>  self::HAS_ONE,
            'foreign_key'   =>  'uid',
            'mapping_key'   =>  'uid',
            'mapping_name'  =>  'contact',
        ],

    ];
}