<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class FormtplGroupRelationModel extends RelationModel {
	protected $tableName='formtpl_group';
	protected $_link = array(
			'FormtplFields'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'FormtplFields',
					'foreign_key'	=>'group_id',
					'mapping_key'	=>'id',
					'mapping_name'	=>'tplfields',
					'mapping_order'	=>'sort asc',
					'mapping_fields'=>'id,atime,active,name,label,formtype,is_need,is_list,is_search,is_verify,default',
				),
		);

}
?>