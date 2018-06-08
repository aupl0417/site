<?php
namespace Common\Model;
use Think\Model\RelationModel;
class GoodsCategoryUpRelationModel extends RelationModel {
	protected $tableName='goods_category';
	protected $_link = array(
			'category'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'goods_category',
					'foreign_key'	=>'sid',
					'mapping_key'	=>'sid',
					'mapping_name'	=>'category',
					'mapping_fields'=>'id,category_name',
					'condition'		=>'status=1',
					'mapping_order'	=>'sort asc',
				),
		);

}
?>