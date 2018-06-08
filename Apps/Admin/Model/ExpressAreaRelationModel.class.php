<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class ExpressAreaRelationModel extends RelationModel {
	protected $tableName='express_area';
	protected $_link = array(
			'express'	=>array(
					'mapping_type'		=>self::BELONGS_TO,
					'class_name'	=>'express',
					'foreign_key'	=>'id',
					'mapping_key'	=>'express_id',
					'mapping_name'	=>'express',
					//'mapping_fields'=>'id,sub_name,logo',
				),
		);

}
?>