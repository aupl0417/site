<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class OrdersShopRelationModel extends RelationModel {
	protected $tableName='orders_shop';
	protected $_link = array(
			'orders'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'orders',
					'foreign_key'	=>'id',
					'mapping_key'	=>'o_id',
					'mapping_name'	=>'orders',
					'mapping_fields'=>'id,province,city,district,town,street,linkname,tel,mobile,postcode',
				),	
			'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user',
					'foreign_key'	=>'id',
					'mapping_key'	=>'uid',
					'mapping_name'	=>'buyer',
					'mapping_fields'=>'id,nick,mobile',
				),
			'seller'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user',
					'foreign_key'	=>'id',
					'mapping_key'	=>'seller_id',
					'mapping_name'	=>'seller',
					'mapping_fields'=>'id,nick,mobile',
				),				
			'shop'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'shop',
					'foreign_key'	=>'id',
					'mapping_key'	=>'shop_id',
					'mapping_name'	=>'shop',
					'mapping_fields'=>'id,shop_name,shop_logo,domain,qq,wang,mobile',
				),	
			'express_company'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'express_company',
					'foreign_key'	=>'id',
					'mapping_key'	=>'express_company_id',
					'mapping_name'	=>'express',
					'mapping_fields'=>'id,company,sub_name,logo,code',
				),				
			'orders_goods'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'orders_goods',
					'foreign_key'	=>'s_id',
					'mapping_key'	=>'id',
					'mapping_name'	=>'orders_goods',
					'mapping_fields'=>'etime,ip',
					'mapping_fields_type'	=>true,
				),				
		);

}
?>