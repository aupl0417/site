<?php
namespace Home\Model;
use Think\Model\RelationModel;
class AdPositionRelationModel extends RelationModel {
    protected $tableName = 'ad_position';
    
    protected $_link = [
        'ad' => [
            'mapping_type'  =>  self::HAS_MANY,
            'foreign_key'   =>  'position_id',
            'mapping_order' =>  'sort asc',
            'condition'     =>  'status = 1',
            'mapping_fields'=>  'id,name,subcontent,sort,images,background_images,url,type,goods_id,shop_id',
        ],
    ];
}