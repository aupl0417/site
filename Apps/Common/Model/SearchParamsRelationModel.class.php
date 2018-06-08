<?php
namespace Common\Model;
use Think\Model\RelationModel;
class SearchParamsRelationModel extends RelationModel {
    protected $tableName = 'goods_param_group';
    
    protected $_link = [
        'goods_param_group_option' => [
            'mapping_type'  =>  self::HAS_MANY,
            'class_name'    =>  'goods_param_group_option',
            'mapping_name'  =>  'option',
            'foreign_key'   =>  'group_id',
            'mapping_fields'=>  'param_name,options,id',
            'condition'     =>  'status = 1',
            'mapping_order' =>  'sort asc',
        ],
    ];
}