<?php
namespace Common\Model;
use Think\Model\RelationModel;
class ShopGoodsCommentRelationModel extends RelationModel {
	protected $tableName='orders_goods_comment';
	protected $_link = array(
			'orders_goods'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'			=>'orders_goods',
					'foreign_key'			=>'id',
					'mapping_key'			=>'orders_goods_id',
					'mapping_name'			=>'orders_goods',
					'mapping_fields'		=>'id,attr_name,price,total_price,goods_name,images',
				),
			'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'			=>'user',
					'foreign_key'			=>'id',
					'mapping_key'			=>'uid',
					'mapping_name'			=>'user',
					'mapping_fields'		=>'nick,face',
				),			

		);

}
?>