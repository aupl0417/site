<?php
namespace Common\Model;
use Think\Model\RelationModel;
class OrdersCodeRelationModel extends RelationModel {
	protected $tableName='orders_code_category';
	protected $_link = array(
			'orders_code'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'			=>'orders_code',
					'foreign_key'			=>'sid',
					'mapping_key'			=>'id',
					'mapping_name'			=>'orders_code',
					'mapping_fields'		=>'code,msg',
					'is_getfield' 			=>1,
				),
		);

}
?>