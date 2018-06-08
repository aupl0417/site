<?php
namespace Common\Model;
use Think\Model\RelationModel;
class UpgradeUserRelationModel extends RelationModel {
	protected $tableName='user_upgrade_logs';
	protected $_link = array(
			'user_level'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user_level',
					'foreign_key'	=>'id',
					'mapping_key'	=>'level_id',
					'mapping_name'	=>'level',
					'mapping_fields'=>'level_name,icon',
					'as_fields'		=>'level_name,icon',
				),
			'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user',
					'foreign_key'	=>'id',
					'mapping_key'	=>'uid',
					'mapping_name'	=>'user',
					'mapping_fields'=>'nick,mobile',
					'as_fields'		=>'nick,mobile',
				),
		);

}
?>