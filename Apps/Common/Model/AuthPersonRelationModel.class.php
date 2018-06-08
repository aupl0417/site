<?php
namespace Common\Model;
use Think\Model\RelationModel;
class AuthPersonRelationModel extends RelationModel {
	protected $tableName='auth_person';
	protected $_link = array(
			'auth_person_logs'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'auth_person_logs',
					'foreign_key'	=>'auth_id',
					'mapping_key'	=>'id',
					'mapping_name'	=>'logs',
					'mapping_fields'=>'id,atime,a_uid,status,reason,remark',
					'mapping_order' =>'id desc',
				),
			'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user',
					'foreign_key'	=>'id',
					'mapping_key'	=>'uid',
					'mapping_name'	=>'user',
					'mapping_fields'=>'id,nick,is_auth',
				),
		);

}
?>