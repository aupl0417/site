<?php
namespace Common\Model;
use Think\Model\RelationModel;
class GoodsAttrListUpRelationModel extends RelationModel {
	protected $tableName='goods_attr_list';
	protected $_link = array(
			'goods'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'		=>'goods',
					'foreign_key'		=>'id',
					'mapping_key'		=>'goods_id',
					'mapping_name'		=>'goods',
					'mapping_fields'	=>'status,category_id,goods_name,seller_id,shop_id,score_ratio,express_tpl_id,activity_id,officialactivity_join_id,officialactivity_price,is_daigou',
				),
			/*
			'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'		=>'user',
					'foreign_key'		=>'id',
					'mapping_key'		=>'seller_id',
					'mapping_name'		=>'seller',
					'mapping_fields'	=>'id,nick',
				),
			*/
		);

}
?>