<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class WithdrawRelationModel extends RelationModel {
	protected $tableName='withdraw';
	protected $_link = array(
			'withdraw_logs'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'withdraw_logs',
					'foreign_key'	=>'w_id',
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
			'withdraw_account'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'withdraw_account',
					'foreign_key'	=>'id',
					'mapping_key'	=>'card_id',
					'mapping_name'	=>'card',
					'mapping_fields'=>'bank_code,bank_name,province,city,address',
				),			
		);

}
?>