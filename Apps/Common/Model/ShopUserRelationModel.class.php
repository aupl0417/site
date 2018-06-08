<?php
namespace Common\Model;
use Think\Model\RelationModel;
class ShopUserRelationModel extends RelationModel {
	protected $tableName='shop';
	protected $_link = array(
			'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'			=>'user',
					'foreign_key'			=>'id',
					'mapping_key'			=>'uid',
					'mapping_name'			=>'user',
					'mapping_fields'		=>'nick,openid,erp_uid',
					'as_fields'				=>'nick,openid,erp_uid',
				),

		);

}
?>