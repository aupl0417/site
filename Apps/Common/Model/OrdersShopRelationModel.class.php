<?php
namespace Common\Model;
use Think\Model\RelationModel;
class OrdersShopRelationModel extends RelationModel {
	protected $tableName='orders_shop';
	protected $_link = array(
			'orders_goods'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'			=>'orders_goods',
					'foreign_key'			=>'s_id',
					'mapping_key'			=>'id',
					'mapping_name'			=>'orders_goods',
					'mapping_fields'		=>'etime,ip',
					'mapping_fields_type'	=>ture,
				),

		);

}
?>