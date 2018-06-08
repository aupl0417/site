<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\RelationModel;
class Shop116RelationModel extends RelationModel {
	protected $tableName='shop';
	protected $_link = array(
'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user',
					'foreign_key'	=>'id',
					'mapping_key'	=>'uid',
					'mapping_name'	=>'user',
					'mapping_fields'=>'id,nick',
				),
			'province'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'area',
					'foreign_key'	=>'id',
					'mapping_key'	=>'province',
					'mapping_name'	=>'province',
					'mapping_fields'=>'id,a_name',
				),
			'city'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'area',
					'foreign_key'	=>'id',
					'mapping_key'	=>'city',
					'mapping_name'	=>'city',
					'mapping_fields'=>'id,a_name',
				),
		);

}
?>