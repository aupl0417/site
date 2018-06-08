<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\RelationModel;
class User72RelationModel extends RelationModel {
	protected $tableName='user';
	protected $_link = array(
			'user_level'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user_level',
					'foreign_key'	=>'id',
					'mapping_key'	=>'level_id',
					'mapping_name'	=>'user_level',
					'mapping_fields'=>'id,level_name,icon',
				),
		);

}
?>