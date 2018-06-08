<?php
namespace Common\Model;
use Think\Model\RelationModel;

class WorkorderLogsRelationModel extends RelationModel
{

	protected $tableName = 'workorder_logs';

	protected $_link = array(
        'user' => array(
            'mapping_type'         => self::HAS_ONE,//映射的类型
            'mapping_name'         => 'user', //映射的名称
            'class_name'        => 'user',  //关联的表名
            'foreign_key'       => 'id', 
            'mapping_key'       => 'uid', 
            'mapping_fields'     => 'nick as user_nick,face as user_face',
            'as_fields'         => 'user_nick,user_face',
        ),
        'work' => array(
            'mapping_type'         => self::HAS_ONE,//映射的类型
            'mapping_name'         => 'user', //映射的名称
            'class_name'        => 'user',  //关联的表名
            'foreign_key'       => 'id', 
            'mapping_key'       => 'work_id', 
            'mapping_fields'     => 'nick as work_nick,face as work_face',
            'as_fields'         => 'work_nick,work_face',
        ),
    );

}