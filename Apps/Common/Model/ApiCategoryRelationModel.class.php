<?php
namespace Common\Model;
use Think\Model\RelationModel;
class ApiCategoryRelationModel extends RelationModel {
	protected $tableName='api_category';
	protected $_link = array(
			'api_doc'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'api_doc',
					'foreign_key'	=>'sid',
					'mapping_key'	=>'id',
					'mapping_name'	=>'apidoc',
					'mapping_fields'=>'id,status,title',
					'condition'		=>'status=1',
					'mapping_order'	=>'sort asc,id asc',
				),
		);

}
?>