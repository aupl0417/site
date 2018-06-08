<?php
namespace Common\Model;
use Think\Model\RelationModel;
class CustomPublishLayoutModulesRelationModel extends RelationModel {
	protected $tableName='custom_publish_layout';
	protected $_link = array(
			'custom_publish_modules'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'		=>'custom_publish_modules',
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