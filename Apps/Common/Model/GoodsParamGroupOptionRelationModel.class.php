<?php
namespace Common\Model;
use Think\Model\RelationModel;
class GoodsParamGroupOptionRelationModel extends RelationModel {
	protected $tableName='goods_param_group_option';
	protected $_link = array(			
			'goods_param'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'		=>'goods_param',
					'foreign_key'		=>'option_id',
					'mapping_key'		=>'id',
					'mapping_name'		=>'param_value',
					'mapping_fields'	=>'param_value',
					'is_getfield'		=>1,
				),


		);

}
?>