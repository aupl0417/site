<?php
namespace Common\Model;
use Think\Model\RelationModel;
class TuanJoinRelationModel extends RelationModel
{


	protected $tableName = 'tuan_join';

	protected $_link = array(
        
        'user' => array(
            'mapping_type'         	=> self::HAS_ONE,
            'mapping_name'         	=> 'user',
            'class_name'        	=> 'user',
            'foreign_key'       	=> 'id',
            'mapping_key'       	=> 'uid',
            'mapping_fields'     	=> 'nick,face',
        ),
        'tuan_start' => array(
            'mapping_type'          => self::HAS_ONE,
            'mapping_name'          => 'tuan_start',
            'class_name'            => 'tuan_start',
            'foreign_key'           => 'id',
            'mapping_key'           => 'tuan_start_id',
            'mapping_fields'        => '*',
        ),
        'goods_attr_list' => array(
            'mapping_type'          => self::HAS_ONE,
            'mapping_name'          => 'goods_attr_list',
            'class_name'            => 'goods_attr_list',
            'foreign_key'           => 'id',
            'mapping_key'           => 'goods_attr_list_id',
            'mapping_fields'        => 'id,images,goods_id,price,num,attr,attr_id,attr_name',
        ),
    );













}