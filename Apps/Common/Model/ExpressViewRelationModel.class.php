<?php
namespace Common\Model;
use Think\Model\RelationModel;
class ExpressViewRelationModel extends RelationModel {
	protected $tableName='express';
	protected $_link = array(
			'express_company'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'		=>'express_company',
					'foreign_key'		=>'id',
					'mapping_key'		=>'express_company_id',
					'mapping_name'		=>'express_company',
					'mapping_fields'	=>'company,sub_name,code,logo',
					'as_fields'			=>'company,sub_name,code,logo',
				),
		);

}
?>