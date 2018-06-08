<?php
namespace Common\Model;
use Think\Model\RelationModel;
class CartSelectRelationModel extends RelationModel {
	protected $tableName='cart';
	protected $_link = array(
			'shop'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'shop',
					'foreign_key'	=>'id',
					'mapping_key'	=>'shop_id',
					'mapping_name'	=>'shop',
					'mapping_fields'=>'id,uid,shop_name,qq,mobile,wang,domain,inventory_type',
				),
			/*
			'Common/ExpressView'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'ExpressView',
					'foreign_key'	=>'uid',
					'mapping_key'	=>'seller_id',
					'mapping_name'	=>'express',
					'condition'		=>'express.status=1',
					'mapping_fields'=>'id,status,sub_name,logo',
				),
			*/
			/*
			'coupon'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'coupon',
					'foreign_key'	=>'seller_id',
					'mapping_key'	=>'seller_id',
					'mapping_name'	=>'coupon',
					'mapping_fields'=>'id,code,price',
				),
			*/
		);

}
?>