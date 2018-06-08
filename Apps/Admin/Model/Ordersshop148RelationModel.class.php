<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\RelationModel;
class Ordersshop148RelationModel extends RelationModel {
	protected $tableName='orders_shop';
	protected $_link = array(
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
					'mapping_fields'=>'id,uid,shop_name,domain,qq,mobile,wang',
				),
            'orders_goods'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'orders_goods',
					'foreign_key'	=>'s_id',
					'mapping_key'	=>'id',
					'mapping_name'	=>'orders_goods',
                    'as_fields'     => 'score_type',
				),
		);

}
?>