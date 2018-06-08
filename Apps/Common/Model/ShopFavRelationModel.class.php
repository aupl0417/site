<?php
namespace Common\Model;
use Think\Model\RelationModel;
class ShopFavRelationModel extends RelationModel {
	protected $tableName='shop_fav';
	protected $_link = array(
			'shop'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'			=>'shop',
					'foreign_key'			=>'id',
					'mapping_key'			=>'shop_id',
					'mapping_name'			=>'shop',
					'mapping_fields'		=>'id,uid,shop_name,shop_logo,qq,mobile,wang,about,domain,inventory_type,fav_num,sale_num',
				),
		);
	
	

}
?>