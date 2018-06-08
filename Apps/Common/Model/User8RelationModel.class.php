<?php
namespace Common\Model;
use Think\Model\RelationModel;
class User8RelationModel extends RelationModel {
	protected $tableName='user';
	protected $_link = array(
			'user_upgrade_logs'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'user_upgrade_logs',
					'foreign_key'	=>'uid',
					'mapping_key'	=>'id',
					'mapping_name'	=>'upgrade',
					'mapping_fields'=>'upgrade_time',
					'mapping_order' =>'id asc',
					'mapping_limit'	=>1,
					'is_getfield'	=>1,
				),
		);

}
?>