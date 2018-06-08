<?php
namespace Common\Model;
use Think\Model\RelationModel;
class OrdersShopBuyerRelationModel extends RelationModel {
	protected $tableName='orders_shop';
	protected $_link = array(
			'orders_goods'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'			=>'orders_goods',
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
			'shop'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'			=>'shop',
					'foreign_key'			=>'id',
					'mapping_key'			=>'shop_id',
					'mapping_name'			=>'shop',
					'mapping_fields'		=>'id,uid,shop_name,shop_logo,qq,mobile,wang,domain,inventory_type',
				),
			'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'			=>'user',
					'foreign_key'			=>'id',
					'mapping_key'			=>'seller_id',
					'mapping_name'			=>'seller',
					'mapping_fields'		=>'id,nick,name,face,erp_uid,openid',
				),
			'coupon'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'			=>'coupon',
					'foreign_key'			=>'id',
					'mapping_key'			=>'coupon_id',
					'mapping_name'			=>'coupon',
					'mapping_fields'		=>'code,price',
				),

		);

}
?>