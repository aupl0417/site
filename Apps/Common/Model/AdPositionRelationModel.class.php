<?php
namespace Common\Model;
use Think\Model\RelationModel;
class AdPositionRelationModel extends RelationModel {
    protected $tableName    =   'ad_position';
    protected $_link        =   [
        'ad'    =>  [
            'mapping_type'  =>  self::HAS_MANY,
            'foreign_key'   =>  'position_id',
            'parent_key'    =>  'id',
            'mapping_fields'=>  'name,images,url,id,sort',
            'condition'     =>  "status = 1 AND find_in_set(DATE_FORMAT(NOW(),'%Y-%m-%d'), days)" ,
            'mapping_name'  =>  'ads',
            'mapping_order' =>  'sort asc',
        ],
    ];
}