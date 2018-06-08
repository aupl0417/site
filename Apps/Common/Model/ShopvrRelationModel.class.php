<?php
namespace Common\Model;
use Think\Model\RelationModel;
class ShopvrRelationModel extends RelationModel {
	protected $tableName='shop_vr';
	protected $_link = array(
			'shop_vr_logs'	=>array(
					'mapping_type'		=>self::HAS_MANY,
					'class_name'	=>'shop_vr_logs',
					'foreign_key'	=>'shop_vr_id',
					'mapping_key'	=>'id',
					'mapping_name'	=>'logs',
					'mapping_fields'=>'status,remark,atime',
					'mapping_order'	=>'atime desc',
			),
			'shop_rules'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'shop_rules',
					'foreign_key'	=>'id',
					'mapping_key'	=>'wrongdoing',
					'mapping_name'	=>'rules',
					'mapping_fields'=>'reason',
			),
	);
}
?>