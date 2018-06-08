<?php
namespace Common\Model;
use Think\Model\RelationModel;
class ChangeFinanceRelationModel extends RelationModel {
	protected $tableName='change_finance';
	protected $_link = array(
			'from_user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'		=>'user',
					'foreign_key'		=>'id',
					'mapping_key'		=>'from_uid',
					'mapping_name'		=>'from_user',
					'mapping_fields'	=>'nick as from_nick',
					'as_fields'			=>'from_nick',
				),
			'to_user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'		=>'user',
					'foreign_key'		=>'id',
					'mapping_key'		=>'to_uid',
					'mapping_name'		=>'to_user',
					'mapping_fields'	=>'nick as to_nick',
					'as_fields'			=>'to_nick',
				),
			'd_user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'		=>'user',
					'foreign_key'		=>'id',
					'mapping_key'		=>'d_uid',
					'mapping_name'		=>'d_user',
					'mapping_fields'	=>'nick as d_nick',
					'as_fields'			=>'d_nick',
				),

		);

}
?>