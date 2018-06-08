<?php
namespace Common\Model;
use Think\Model\RelationModel;
class CouponRelationModel extends RelationModel {
	protected $tableName='coupon';
	protected $_link = array(
			'shop'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'		=>'shop',
					'foreign_key'		=>'id',
					'mapping_key'		=>'shop_id',
					'as_fields'	=>'shop_name,domain,shop_logo',
				),
		);

}
?>