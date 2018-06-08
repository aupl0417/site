<?php
namespace Common\Model;
use Think\Model\RelationModel;
class OfficialactivityJoinUpRelationModel extends RelationModel {
	protected $tableName='officialactivity_join';
	protected $_link = array(
			'officialactivity'	=>array(
					'mapping_type'		=>self::BELONGS_TO,
					'class_name'	=>'officialactivity',
					'foreign_key'	=>'activity_id',
					'parent_key'	=>'id',
					'mapping_name'	=>'officialactivity',
					'mapping_fields'=>'max_buy',
				),
		);

}
?>