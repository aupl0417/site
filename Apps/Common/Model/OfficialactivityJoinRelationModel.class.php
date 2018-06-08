<?php
namespace Common\Model;
use Think\Model\RelationModel;
class OfficialactivityJoinRelationModel extends RelationModel {
    protected $tableName    =   'officialactivity_join';
    protected $_link        =   [
        'goods_attr_list'    =>  [
            'mapping_type'  =>  self::HAS_ONE,
            'foreign_key'   =>  'goods_id',
            'mapping_key'   =>  'goods_id',
            'mapping_fields'=>  'id,attr_name,images,price,num',
            //'condition'     =>  'num>0' ,
        ],
        'goods'    =>  [
            'mapping_type'  =>  self::HAS_ONE,
            'foreign_key'   =>  'id',
            'mapping_key'   =>  'goods_id',
            'mapping_fields'=>  'goods_name,images,price,num,score_ratio',
        ],
    ];
}