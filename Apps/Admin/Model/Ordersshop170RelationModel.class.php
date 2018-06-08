<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\RelationModel;
class Ordersshop170RelationModel extends RelationModel {
	protected $tableName='orders_shop';
	protected $_link = array(
'activity_participate'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'			=>'activity_participate',
					'foreign_key'			=>'s_no',
					'parent_key'			=>'s_no',
					'mapping_name'			=>'activity',
					'mapping_fields'		=>'id,type_id,status',
				),
'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user',
					'foreign_key'	=>'id',
					'mapping_key'	=>'uid',
					'mapping_name'	=>'user',
					'mapping_fields'=>'id,nick',
				),
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
					'mapping_fields'=>'id,shop_name,domain,qq,mobile,wang',
				),
		);

}
?>