<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\RelationModel;
class Goods86RelationModel extends RelationModel {
	protected $tableName='goods';
	protected $_link = array(
'seller'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user',
					'foreign_key'	=>'id',
					'mapping_key'	=>'seller_id',
					'mapping_name'	=>'seller',
					'mapping_fields'=>'id,nick',
				),
			'shop'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'shop',
					'foreign_key'	=>'id',
					'mapping_key'	=>'shop_id',
					'mapping_name'	=>'shop',
					'mapping_fields'=>'id,shop_name,shop_logo,domain,qq,mobile',
				),
			'goods_attr_list'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'goods_attr_list',
					'foreign_key'	=>'goods_id',
					'mapping_key'	=>'id',
					'mapping_name'	=>'attr_list',
					'mapping_fields'=>'id',
				),
		);

}
?>