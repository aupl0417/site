<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\RelationModel;
class GoodsIlleglRelationModel extends RelationModel {
	protected $tableName='goods_illegl';
	protected $_link = array(
			'seller'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user',
					'foreign_key'	=>'id',
					'mapping_key'	=>'uid',
					'mapping_name'	=>'seller',
					'mapping_fields'=>'id,nick,mobile,is_auth',
				),
			'shop'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'shop',
					'foreign_key'	=>'id',
					'mapping_key'	=>'shop_id',
					'mapping_name'	=>'shop',
					'mapping_fields'=>'id,shop_name,shop_logo,mobile,qq,wang',
				),
			'goods'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'goods',
					'foreign_key'	=>'id',
					'mapping_key'	=>'goods_id',
					'mapping_name'	=>'goods',
					'mapping_fields'=>'id,goods_name,images,status',
				),			
			'goods_attr_list'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'goods_attr_list',
					'foreign_key'	=>'goods_id',
					'mapping_key'	=>'goods_id',
					'mapping_name'	=>'attr_list',
					'mapping_fields'=>'id,attr_name,images',
				),	
			'goods_illegl_logs'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'goods_illegl_logs',
					'foreign_key'	=>'illegl_id',
					'mapping_key'	=>'id',
					'mapping_name'	=>'logs',
					'mapping_order' =>'id desc',
				),			
		);

}
?>