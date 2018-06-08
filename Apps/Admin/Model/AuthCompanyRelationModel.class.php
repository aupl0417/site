<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class AuthCompanyRelationModel extends RelationModel {
	protected $tableName='auth_company';
	protected $_link = array(
			'auth_company_logs'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'auth_company_logs',
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
			'company_type'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'company_type',
					'foreign_key'	=>'id',
					'mapping_key'	=>'com_type',
					'mapping_name'	=>'comtype',
					'mapping_fields'=>'type_name',
				),
		);

}
?>