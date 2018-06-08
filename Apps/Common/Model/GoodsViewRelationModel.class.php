<?php
namespace Common\Model;
use Think\Model\RelationModel;
class GoodsViewRelationModel extends RelationModel {
	protected $tableName='goods';
	protected $_link = array(			
			/*
			'goods_package'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'		=>'goods_package',
					'foreign_key'		=>'id',
					'mapping_key'		=>'package_id',
					'mapping_name'		=>'package',
					'mapping_fields'	=>'content',
				),
			'goods_protection'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'		=>'goods_protection',
					'foreign_key'		=>'id',
					'mapping_key'		=>'protection_id',
					'mapping_name'		=>'protection',
					'mapping_fields'	=>'content',
				),
			'goods_content'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'		=>'goods_content',
					'foreign_key'		=>'goods_id',
					'mapping_key'		=>'id',
					'mapping_name'		=>'content',
					'mapping_fields'	=>'content',
				),
			*/
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
					'mapping_fields'	=>'id,uid,fraction,fraction_speed,fraction_service,fraction_desc,shop_name,qq,mobile,wang,domain,inventory_type',
				),
            'goods_collocation' =>array(    //搭配商品
                'mapping_type'		=>self::HAS_ONE,
                'class_name'		=>'goods_collocation',
                'foreign_key'		=>'goods_id',
                'mapping_key'		=>'id',
                'as_fields'         =>'collocations',
            ),
		);

}
?>