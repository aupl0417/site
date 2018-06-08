<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class AccountRelationModel extends RelationModel {
	protected $tableName='account';
	protected $_link = array(
			'account_logs'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'account_logs',
					'foreign_key'	=>'uid',
					'mapping_key'	=>'uid',
					'mapping_name'	=>'logs',
					'mapping_fields'=>'id,atime,a_uid,uid,money,flag,type,remark,account',
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