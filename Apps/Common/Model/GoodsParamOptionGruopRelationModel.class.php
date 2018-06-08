<?php
namespace Common\Model;
use Think\Model\RelationModel;
class GoodsParamOptionGruopRelationModel extends RelationModel {
	protected $tableName='goods_param_group_option';
	protected $_link = array(			
			'goods_param_group'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'		=>'goods_param_group',
					'foreign_key'		=>'id',
					'mapping_key'		=>'group_id',
					'mapping_name'		=>'goods_param_group',
					'mapping_fields'	=>'category_id',
					'as_fields'			=>'category_id',
				),


		);

}
?>