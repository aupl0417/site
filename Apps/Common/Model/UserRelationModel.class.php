<?php
namespace Common\Model;
use Think\Model\RelationModel;
class UserRelationModel extends RelationModel {
	protected $tableName='user';
	protected $_link = array(
			'user_level'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user_level',
					'foreign_key'	=>'id',
					'mapping_key'	=>'level_id',
					'mapping_name'	=>'level',
					'mapping_fields'=>'level_name,icon',
				),
		);

}
?>