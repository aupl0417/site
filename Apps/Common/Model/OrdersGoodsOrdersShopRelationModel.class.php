<?php
namespace Common\Model;
use Think\Model\RelationModel;
class OrdersGoodsOrdersShopRelationModel extends RelationModel {
	protected $tableName='orders_goods';
	protected $_link = array(
			'orders_shop'	=>array(
					'mapping_type'		=>self::BELONGS_TO,
					'class_name'			=>'orders_shop',
					'foreign_key'			=>'s_id',
					'mapping_key'			=>'id',
					'mapping_name'			=>'orders_shop',
					'mapping_fields'		=>'etime,ip',
					'mapping_fields_type'	=>true,
				),
		);

}
?>