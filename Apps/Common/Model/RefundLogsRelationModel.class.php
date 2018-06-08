<?php
namespace Common\Model;
use Think\Model\RelationModel;
class RefundLogsRelationModel extends RelationModel {
	protected $tableName='refund_logs';
	protected $_link = array(
			'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user',
					'foreign_key'	=>'id',
					'mapping_key'	=>'uid',
					'mapping_name'	=>'user',
					//'condition'		=>'uid > 0',
					'mapping_fields'=>'id,nick,face,mobile',
				),
			'admin'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'admin',
					'foreign_key'	=>'id',
					'mapping_key'	=>'a_uid',
					'mapping_name'	=>'admin',
					//'condition'		=>'a_uid > 0',
					'mapping_fields'=>'id,username,face,mobile',
				),			
		);

}
?>