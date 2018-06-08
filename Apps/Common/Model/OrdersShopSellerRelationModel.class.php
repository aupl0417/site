<?php
namespace Common\Model;
use Think\Model\RelationModel;
class OrdersShopSellerRelationModel extends RelationModel {
	protected $tableName='orders_shop';
	protected $_link = array(
			'OrdersGoodsOfficialactivityView'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'			=>'OrdersGoodsOfficialactivityView',
					'foreign_key'			=>'s_id',
					'mapping_key'			=>'id',
					'mapping_name'			=>'orders_goods',
					//'mapping_fields'		=>'etime,ip',
					//'mapping_fields_type'	=>ture,
				),
			'orders'	=>array(
					'mapping_type'		=>self::BELONGS_TO,
					'class_name'			=>'orders',
					'foreign_key'			=>'o_id',
					'mapping_key'			=>'id',
					'mapping_name'			=>'orders',
					'mapping_fields'		=>'province,city,district,town,street,linkname,tel,mobile,postcode',
				),
			'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'			=>'user',
					'foreign_key'			=>'id',
					'mapping_key'			=>'uid',
					'mapping_name'			=>'buyer',
					'mapping_fields'		=>'id,nick,name,face',
				),
			'coupon'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'			=>'coupon',
					'foreign_key'			=>'id',
					'mapping_key'			=>'coupon_id',
					'mapping_name'			=>'coupon',
					'mapping_fields'		=>'code,price',
				),
			'refund'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'			=>'refund',
					'foreign_key'			=>'s_no',
					'mapping_key'			=>'s_no',
					'mapping_name'			=>'refund',
					'mapping_fields'		=>'s_no',
					
				),

		);

}
?>