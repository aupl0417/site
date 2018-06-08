<?php
namespace Common\Model;
use Think\Model\RelationModel;
class BrandExtRetionModel extends RelationModel {
	protected $tableName='brand_ext';
	protected $_link = array(
			'brand_ext_logs'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'brand_ext_logs',
					'foreign_key'	=>'brand_ext_id',
					'mapping_key'	=>'id',
					'mapping_name'	=>'logs',
				),
		);

}
?>