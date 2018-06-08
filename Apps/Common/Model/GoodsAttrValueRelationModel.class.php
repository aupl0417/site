<?php
namespace Common\Model;
use Think\Model\RelationModel;
class GoodsAttrValueRelationModel extends RelationModel {
	protected $tableName='goods_attr';
	protected $_link = array(
			'goods_attr_value'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'		=>'goods_attr_value',
					'foreign_key'		=>'attr_id',
					'mapping_key'		=>'id',
					'mapping_name'		=>'option',
                    'mapping_order'     =>'id asc',
					'mapping_fields'	=>'option_id,attr_value,attr_images,attr_album',
				),
		);

}
?>