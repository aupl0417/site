<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class ExpressCategoryRelationModel extends RelationModel {
	protected $tableName='express_category';
	protected $_link = array(
			'express_company'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'express_company',
					'foreign_key'	=>'category_id',
					'mapping_key'	=>'id',
					'mapping_name'	=>'company',
					'mapping_fields'=>'id,sub_name,logo',
				),
		);

}
?>