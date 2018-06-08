<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class AdRelation2Model extends RelationModel {
	protected $tableName='ad';
	protected $_link = array(
			'ad_position'	=>array(
				'mapping_type'		=>self::HAS_ONE,
				'class_name'	=>'ad_position',
				'foreign_key'	=>'id',
				'mapping_key'	=>'position_id',
				'mapping_name'	=>'ad_position',
				'mapping_fields'=>'id,images,position_name,device',
				'as_fields' 	=>'position_name,device',
			),
			'user'	=>array(
				'mapping_type'		=>self::HAS_ONE,
				'class_name'	=>'user',
				'foreign_key'	=>'id',
				'mapping_key'	=>'uid',
				'mapping_name'	=>'user',
				'mapping_fields'=>'nick',
				'as_fields'=>'nick',
			),
			'goods'	=>array(
				'mapping_type'		=>self::HAS_ONE,
				'class_name'	=>'goods',
				'foreign_key'	=>'id',
				'mapping_key'	=>'goods_id',
				'mapping_name'	=>'goods',
				'mapping_fields'=>'goods_name',
				'as_fields'=>'goods_name',
			),
			'shop'	=>array(
				'mapping_type'		=>self::HAS_ONE,
				'class_name'	=>'shop',
				'foreign_key'	=>'id',
				'mapping_key'	=>'shop_id',
				'mapping_name'	=>'shop',
				'mapping_fields'=>'shop_name',
				'as_fields'=>'shop_name',
			),
			'sucai'	=>array(
				'mapping_type'		=>self::HAS_ONE,
				'class_name'	=>'ad_sucai',
				'foreign_key'	=>'id',
				'mapping_key'	=>'sucai_id',
				'mapping_name'	=>'ad_sucai',
				'mapping_fields'=>'sucai_name',
				'as_fields'=>'sucai_name',
			),
		);

}
?>