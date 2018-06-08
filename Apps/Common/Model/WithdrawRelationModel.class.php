<?php
namespace Common\Model;
use Think\Model\RelationModel;
class WithdrawRelationModel extends RelationModel {
	protected $tableName='withdraw';
	protected $_link = array(
			'withdraw_account'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'withdraw_account',
					'foreign_key'	=>'id',
					'mapping_key'	=>'card_id',
					'mapping_name'	=>'card',
					'mapping_fields'=>'province,city,address',
				),
			'withdraw_logs'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'withdraw_logs',
					'foreign_key'	=>'w_id',
					'mapping_key'	=>'id',
					'mapping_name'	=>'logs',
					'mapping_fields'=>'id,atime,a_uid,status,reason,remark',
					'mapping_order' =>'id desc',
				),	
			'bank_name'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'bank_name',
					'foreign_key'	=>'id',
					'mapping_key'	=>'bank_id',
					'mapping_name'	=>'bank',
					'mapping_fields'=>'logo,bank_code,bank_name',
				),
		);

}
?>