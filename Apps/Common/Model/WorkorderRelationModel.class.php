<?php
namespace Common\Model;
use Think\Model\RelationModel;

class WorkorderRelationModel extends RelationModel
{

	protected $tableName = 'workorder';

	protected $_link = array(
        'type' => array(
            'mapping_type'         => self::HAS_ONE,
            'mapping_name'         => 'workorder_type', 
            'class_name'        => 'workorder_type',  
            'foreign_key'       => 'id', 
            'mapping_key'       => 'type', 
            'mapping_fields'     => 'name as type_name',
            'as_fields'         => 'type_name',
        ),
        'type2' => array(
            'mapping_type'         => self::HAS_ONE,
            'mapping_name'         => 'workorder_type', 
            'class_name'        => 'workorder_type',  
            'foreign_key'       => 'id', 
            'mapping_key'       => 'type2', 
            'mapping_fields'     => 'name as type2_name',
            'as_fields'         => 'type2_name',
        ),
    );



}