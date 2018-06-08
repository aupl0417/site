<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\RelationModel;
class Zhaoshangupgrade192RelationModel extends RelationModel {
	protected $tableName='zhaoshang_upgrade';
	protected $_link = array(
'user'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'user',
					'foreign_key'	=>'id',
					'mapping_key'	=>'uid',
					'mapping_name'	=>'user',
					'mapping_fields'=>'id,nick,type',
				),
			'shop_type'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'shop_type',
					'foreign_key'	=>'id',
					'mapping_key'	=>'shop_type_id',
					'mapping_name'	=>'shop_type',
					'mapping_fields'=>'id,type_name,max_best,max_goods',
				),
		);

}
?>