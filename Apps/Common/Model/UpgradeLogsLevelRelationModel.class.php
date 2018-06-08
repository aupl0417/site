<?php
namespace Common\Model;
use Think\Model\RelationModel;
class UpgradeLogsLevelRelationModel extends RelationModel {
	protected $tableName='user_upgrade_logs';
	protected $_link = array(
			'user_level'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user_level',
					'foreign_key'	=>'id',
					'mapping_key'	=>'level_id',
					'mapping_name'	=>'level',
					'mapping_fields'=>'level_name,about,upgrade_money,team_ratio,upgrade_ratio,upgrade_ratio,upuser_ratio,icon',
				),
		);

}
?>