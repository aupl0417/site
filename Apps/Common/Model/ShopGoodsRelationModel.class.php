<?php
namespace Common\Model;
use Think\Model\RelationModel;
class ShopGoodsRelationModel extends RelationModel {
	protected $tableName='shop';
	protected $_link = array(
			'goods'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'			=>'goods',
					'foreign_key'			=>'shop_id',
					'mapping_key'			=>'id',
					'mapping_name'			=>'goods',
					'mapping_limit'			=>5,
					'condition'				=>'status=1 and num>0',
					'mapping_fields'		=>'id,goods_name,images,price,sale_num',

				),
		);
	
	

}
?>