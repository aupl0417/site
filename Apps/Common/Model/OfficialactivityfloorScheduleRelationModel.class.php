<?php
namespace Common\Model;
use Think\Model\RelationModel;
class OfficialactivityfloorScheduleRelationModel extends RelationModel {
	protected $tableName='officialactivity_floor';
	protected $_link = array(
			'officialactivity_schedule'	=>array(
					'mapping_type'		=>self::BELONGS_TO,
					'class_name'	=>'officialactivity_schedule',
					'foreign_key'	=>'schedule_id',
					'parent_key'	=>'id',
					'mapping_name'	=>'schedule',
					'mapping_fields'=>'status,day,time',
				),
		);

}
?>