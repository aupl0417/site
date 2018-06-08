<?php
namespace Common\Model;
use Think\Model\RelationModel;
class WithdrawAccountRelationModel extends RelationModel {
	protected $tableName='withdraw_account';
	protected $_link = array(
			'bank_name'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'bank_name',
					'foreign_key'	=>'id',
					'mapping_key'	=>'bank_id',
					'mapping_name'	=>'bank',
					'mapping_fields'=>'logo,bank_name',
				),

		);

}
?>