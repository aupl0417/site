<?php
namespace Common\Model;
use Think\Model\RelationModel;
class BrandExtShopRelationModel extends RelationModel {
	protected $tableName='brand_ext';
	protected $_link = array(
			'shop'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'shop',
					'foreign_key'	=>'id',
					'mapping_key'	=>'shop_id',
					'mapping_name'	=>'shop',
					'mapping_fields'=>'id,shop_name,shop_logo,qq,domain',
				),			
		);

}
?>