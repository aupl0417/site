<?php
namespace Common\Model;
use Think\Model\RelationModel;

class TuanApplyListRelationModel extends RelationModel
{


	protected $tableName = 'tuan_apply_list';

	protected $_link = array(
        'goods_attr_list' => array(
            'mapping_type'          => self::HAS_ONE,
            'mapping_name'          => 'goods_attr',
            'class_name'            => 'goods_attr_list',
            'foreign_key'           => 'id',
            'mapping_key'           => 'goods_attr_list_id',
            'mapping_fields'        => '*',
        ),
    );





	
}