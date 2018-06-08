<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class AdRelationModel extends RelationModel {
	protected $tableName='ad';
	protected $_link = array(
			'ad_position'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'ad_position',
					'foreign_key'	=>'id',
					'mapping_key'	=>'position_id',
					'mapping_name'	=>'ad_position',
				),
			'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user',
					'foreign_key'	=>'id',
					'mapping_key'	=>'uid',
					'mapping_name'	=>'user',
					'mapping_fields'=>'id,nick,mobile,face'
				),
			'Goods'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'foreign_key'		=>'id',
					'mapping_key'		=>'goods_id',
					'mapping_name'		=>'goods',
					'mapping_fields'	=>'id,goods_name,images',
				),		
			'shop'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'foreign_key'		=>'id',
					'mapping_key'		=>'shop_id',
					'mapping_name'		=>'shop',
					'mapping_fields'	=>'id,shop_name,shop_logo,domain',
				),				
		);

}
?>