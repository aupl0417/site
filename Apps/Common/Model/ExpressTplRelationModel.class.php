<?php
namespace Common\Model;
use Think\Model\RelationModel;
class ExpressTplRelationModel extends RelationModel {
	protected $tableName='express_tpl';
	protected $_link = array(
			'express_area'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'		=>'express_area',
					'foreign_key'		=>'tpl_id',
					'mapping_key'		=>'id',
					'mapping_name'		=>'express_area',
					//'mapping_fields'	=>'id,city_ids,first_unit,first_price,next_unit,next_price',
				),
				
		);

}
?>