<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\RelationModel;
class Ordersgoodscomment135RelationModel extends RelationModel {
	protected $tableName='orders_goods_comment';
	protected $_link = array(
			'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user',
					'foreign_key'	=>'id',
					'mapping_key'	=>'uid',
					'mapping_name'	=>'user',
					'mapping_fields'=>'id,nick,is_auth',
				),
			'seller'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user',
					'foreign_key'	=>'id',
					'mapping_key'	=>'seller_id',
					'mapping_name'	=>'seller',
					'mapping_fields'=>'id,nick,is_auth',
				),
			'shop'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'shop',
					'foreign_key'	=>'id',
					'mapping_key'	=>'shop_id',
					'mapping_name'	=>'shop',
					'mapping_fields'=>'id,shop_name,shop_logo,mobile,qq,wang',
				),
			'goods_attr_list'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'goods_attr_list',
					'foreign_key'	=>'id',
					'mapping_key'	=>'attr_list_id',
					'mapping_name'	=>'attr_list',
					'mapping_fields'=>'id,attr_name,images',
				),	
		);

}
?>