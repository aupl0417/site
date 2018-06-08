<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class OrdersShopViewRelationModel extends RelationModel {
	protected $tableName='orders_shop';
	protected $_link = array(
			'orders_logs'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'orders_logs',
					'foreign_key'	=>'s_id',
					'mapping_key'	=>'id',
					'mapping_name'	=>'logs',
					'mapping_fields'=>'id,atime,s_id,s_no,status,images,reason,remark',
					'mapping_order' =>'id desc',
				),
			'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user',
					'foreign_key'	=>'id',
					'mapping_key'	=>'uid',
					'mapping_name'	=>'user',
					'mapping_fields'=>'id,nick,face,mobile',
				),
			'seller'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user',
					'foreign_key'	=>'id',
					'mapping_key'	=>'seller_id',
					'mapping_name'	=>'seller',
					'mapping_fields'=>'id,nick,face,mobile',
				),
			'shop'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'shop',
					'foreign_key'	=>'id',
					'mapping_key'	=>'shop_id',
					'mapping_name'	=>'shop',
					'mapping_fields'=>'id,shop_name,shop_logo,qq,wang,mobile,domain',
				),
			'OrdersGoodsOfficialactivityView'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'Common/OrdersGoodsOfficialactivityView',
					'foreign_key'	=>'s_id',
					'mapping_key'	=>'id',
					'mapping_name'	=>'orders_goods',
					//'mapping_fields'=>'',
				),
			'orders'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'orders',
					'foreign_key'	=>'id',
					'mapping_key'	=>'o_id',
					'mapping_name'	=>'orders',
					'mapping_fields'=>'id,province,city,district,town,street,linkname,tel,mobile,postcode',
				),			
			'express_company'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'express_company',
					'foreign_key'	=>'id',
					'mapping_key'	=>'express_company_id',
					'mapping_name'	=>'express',
					'mapping_fields'=>'id,company,sub_name,logo',
				),						
		);

}
?>