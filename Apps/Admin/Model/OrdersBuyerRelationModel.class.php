<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class OrdersBuyerRelationModel extends RelationModel {
	protected $tableName='orders';
	protected $_link = array(
			'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user',
					'foreign_key'	=>'id',
					'mapping_key'	=>'uid',
					'mapping_name'	=>'buyer',
					'mapping_fields'=>'id,nick,mobile'
				),
			'orders_shop'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'orders_shop',
					'foreign_key'	=>'o_id',
					'mapping_key'	=>'id',
					'mapping_name'	=>'orders_shop',
				),
		);

}
?>