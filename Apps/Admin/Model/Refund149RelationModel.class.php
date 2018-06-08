<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\RelationModel;
class Refund149RelationModel extends RelationModel {
	protected $tableName='refund';
	protected $_link = array(
			'shop'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'			=>'shop',
					'foreign_key'			=>'id',
					'mapping_key'			=>'shop_id',
					'mapping_name'			=>'shop',
					'mapping_fields'		=>'id,uid,shop_name,qq,mobile,wang,domain',
				),
			'seller'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'			=>'user',
					'foreign_key'			=>'id',
					'mapping_key'			=>'seller_id',
					'mapping_name'			=>'seller',
					'mapping_fields'		=>'id,nick,name,face',
				),
			'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'			=>'user',
					'foreign_key'			=>'id',
					'mapping_key'			=>'uid',
					'mapping_name'			=>'user',
					'mapping_fields'		=>'id,nick,name,face',
				),	
			'orders_goods'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'orders_goods',
					'foreign_key'	=>'id',
					'mapping_key'	=>'orders_goods_id',
					'mapping_name'	=>'orders_goods',
					'mapping_fields'=>'id,goods_id,attr_list_id,attr_name,images,goods_name',
				),				
		);

}
?>