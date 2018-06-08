<?php
namespace Common\Model;
use Think\Model\RelationModel;
class CustomMakeLayoutModulesRelationModel extends RelationModel {
	protected $tableName='custom_make_layout';
	protected $_link = array(
			'custom_make_modules'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'		=>'custom_make_modules',
					'foreign_key'		=>'layout_id',
					'mapping_key'		=>'id',
					'mapping_name'		=>'modules',
					'mapping_fields'	=>'atime,etime,ip',
					'mapping_fields_type'	=>true,
					'mapping_order'		=>'sort asc',
				),


		);

}
?>