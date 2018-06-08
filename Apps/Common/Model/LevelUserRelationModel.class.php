<?php
namespace Common\Model;
use Think\Model\RelationModel;
class LevelUserRelationModel extends RelationModel {
	protected $tableName='user_level';
	protected $_link = array(
			'User'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'User',
					'foreign_key'	=>'level_id',
					'mapping_key'	=>'id',
					'mapping_name'	=>'downline_user',
					//'mapping_fields'=>'id,atime,nick,team_num',
				),
		);

}
?>