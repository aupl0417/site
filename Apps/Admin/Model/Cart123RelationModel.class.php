<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\RelationModel;
class Cart123RelationModel extends RelationModel {
	protected $tableName='cart';
	protected $_link = array(
			'goods_attr_list'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'		=>'goods_attr_list',
					'foreign_key'		=>'id',
					'mapping_key'		=>'attr_list_id',
					'mapping_name'		=>'attr_list',
					'mapping_fields'	=>'images',
				),
			'goods'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'		=>'goods',
					'foreign_key'		=>'id',
					'mapping_key'		=>'goods_id',
					'mapping_name'		=>'goods',
					'mapping_fields'	=>'goods_name',
				),
		);

}
?>