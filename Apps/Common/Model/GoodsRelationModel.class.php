<?php
namespace Common\Model;
use Think\Model\RelationModel;
class GoodsRelationModel extends RelationModel {
	protected $tableName='goods';
	protected $_link = array(			
			'goods_attr_list'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'		=>'goods_attr_list',
					'foreign_key'		=>'goods_id',
					'mapping_key'		=>'id',
					'mapping_name'		=>'attr_list',
				),
			
			'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'		=>'user',
					'foreign_key'		=>'id',
					'mapping_key'		=>'seller_id',
					'mapping_name'		=>'seller',
					'mapping_fields'	=>'id,nick',
				),
			'shop'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'		=>'shop',
					'foreign_key'		=>'id',
					'mapping_key'		=>'shop_id',
					'mapping_name'		=>'shop',
					'mapping_fields'	=>'id,status,uid,shop_name,qq,mobile,wang,domain,type_id,inventory_type',
			        'condition'         =>'is_test = 0'
				),
		);

}
?>