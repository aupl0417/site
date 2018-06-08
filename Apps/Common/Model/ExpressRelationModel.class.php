<?php
namespace Common\Model;
use Think\Model\RelationModel;
class ExpressRelationModel extends RelationModel {
	protected $tableName='express';
	protected $_link = array(
			'express_area'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'		=>'express_area',
					'foreign_key'		=>'express_id',
					'mapping_key'		=>'id',
					'mapping_name'		=>'express_area',
					'mapping_fields'	=>'id,city_ids,first_unit,first_price,next_unit,next_price',
				),
			'express_company'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'		=>'express_company',
					'foreign_key'		=>'id',
					'mapping_key'		=>'express_company_id',
					'mapping_name'		=>'express_company',
					'mapping_fields'	=>'id,company,sub_name,logo',
				),
				
		);

}
?>