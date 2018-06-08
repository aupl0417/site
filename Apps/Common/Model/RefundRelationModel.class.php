<?php
namespace Common\Model;
use Think\Model\RelationModel;
class RefundRelationModel extends RelationModel {
	protected $tableName='refund';
	protected $_link = array(
			/*
			'refund_logs'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'refund_logs',
					'foreign_key'	=>'r_id',
					'mapping_key'	=>'id',
					'mapping_name'	=>'logs',
					'mapping_fields'=>'id,atime,r_id,r_no,uid,status,type,images,remark',
					'mapping_order' =>'id desc',
				),
			*/
			'orders_shop'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'orders_shop',
					'foreign_key'	=>'id',
					'mapping_key'	=>'s_id',
					'mapping_name'	=>'orders_shop',
					'mapping_fields'=>'id,s_no,status,goods_price,express_price,pay_price,refund_price,refund_score,refund_express,express_price_edit,daigou_cost',
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
					'mapping_fields'=>'id,nick,face,mobile,erp_uid',
				),
			'shop'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'shop',
					'foreign_key'	=>'id',
					'mapping_key'	=>'shop_id',
					'mapping_name'	=>'shop',
					'mapping_fields'=>'id,shop_name,shop_logo,qq,wang,domain',
				),
			'orders_goods'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'orders_goods',
					'foreign_key'	=>'id',
					'mapping_key'	=>'orders_goods_id',
					'mapping_name'	=>'orders_goods',
					'mapping_fields'=>'id,goods_id,attr_list_id,attr_name,images,goods_name,price,num,total_price_edit,score_ratio',
				),	

		);

}
?>