<?php
namespace Common\Model;
use Think\Model\RelationModel;
class ShopMakeLayoutModulesRelationModel extends RelationModel {
	protected $tableName='shop_make_layout';
	protected $_link = array(
			'shop_make_modules'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'		=>'shop_make_modules',
					'foreign_key'		=>'make_layout_id',
					'mapping_key'		=>'id',
					'mapping_name'		=>'modules',
					'mapping_fields'	=>'atime,etime,ip',
					'mapping_fields_type'	=>true,
					'mapping_order'		=>'sort asc',
				),


		);

}
?>