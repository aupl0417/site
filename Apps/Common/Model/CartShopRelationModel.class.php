<?php
namespace Common\Model;
use Think\Model\RelationModel;
class CartShopRelationModel extends RelationModel {
	protected $tableName='cart';
	protected $_link = array(
			'shop'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'shop',
					'foreign_key'	=>'id',
					'mapping_key'	=>'shop_id',
					'mapping_name'	=>'shop',
					'mapping_fields'=>'id,uid,shop_name,qq,mobile,wang,domain,inventory_type',
				),
		);

}
?>