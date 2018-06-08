<?php
namespace Common\Model;
use Think\Model\RelationModel;
class AdRelationModel extends RelationModel {
	protected $tableName='ad';
	protected $_link = array(
			'ad_position'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'ad_position',
					'foreign_key'	=>'id',
					'mapping_key'	=>'position_id',
					'mapping_name'	=>'ad_position',
					'mapping_fields'=>'id,images,position_name,device',
				),
		);

}
?>