<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class BrandExtRelationModel extends RelationModel {
	protected $tableName='brand_ext';
	protected $_link = array(
			'brand_ext_logs'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'brand_ext_logs',
					'foreign_key'	=>'brand_ext_id',
					'mapping_key'	=>'id',
					'mapping_name'	=>'logs',
					'mapping_fields'=>'id,atime,a_uid,status,reason,remark',
					'mapping_order' =>'id desc',
				),
			'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user',
					'foreign_key'	=>'id',
					'mapping_key'	=>'uid',
					'mapping_name'	=>'user',
					'mapping_fields'=>'id,nick,face,is_auth',
				)
		);

}
?>