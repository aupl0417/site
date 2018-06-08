<?php
namespace Common\Model;
use Think\Model\RelationModel;
class OrdersBuyerRelationModel extends RelationModel {
	protected $tableName='orders';
	protected $_link = array(
			'orders_shop'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'			=>'orders_shop',
					'foreign_key'			=>'o_id',
					'mapping_key'			=>'id',
					'mapping_name'			=>'orders_shop',
					'mapping_fields'		=>'etime,ip',
					'mapping_fields_type'	=>true,
				),
		);

}
?>