<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class GoodsParamOptionRelationModel extends RelationModel {
	protected $tableName='goods_param_group';
	protected $_link = array(
			'goods_param_group_option'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'goods_param_group_option',
					'foreign_key'	=>'group_id',
					'mapping_key'	=>'id',
					'mapping_name'	=>'param_option',
					'mapping_order'	=>'sort asc',
					'mapping_fields'=>'id,status,param_name,options,type,is_need,group_id',
				),

		);

}
?>