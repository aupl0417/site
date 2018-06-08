<?php
namespace Common\Model;
use Think\Model\RelationModel;
class TuanStartRelationModel extends RelationModel
{


	protected $tableName = 'tuan_start';

	protected $_link = array(
        'tuan_join' => array(
            'mapping_type'         	=> self::HAS_MANY,
            'mapping_name'         	=> 'tuan_join',
            'class_name'        	=> 'tuan_join',
            'foreign_key'       	=> 'tuan_start_id',
            'mapping_key'       	=> 'id',
            'mapping_fields'     	=> '*',
        ),
        'user' => array(
            'mapping_type'         	=> self::HAS_ONE,
            'mapping_name'         	=> 'user',
            'class_name'        	=> 'user',
            'foreign_key'       	=> 'id',
            'mapping_key'       	=> 'uid',
            'mapping_fields'     	=> 'nick,face',
        ),
    );













}