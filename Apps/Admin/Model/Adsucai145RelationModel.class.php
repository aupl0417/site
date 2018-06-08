<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\RelationModel;
class Adsucai145RelationModel extends RelationModel {
	protected $tableName='ad_sucai';
	protected $_link = array(
			'user'	=>array(
				'mapping_type'		=>self::HAS_ONE,
				'class_name'	=>'user',
				'foreign_key'	=>'id',
				'mapping_key'	=>'uid',
				'mapping_name'	=>'user',
				'mapping_fields'=>'id,nick,type',
			),
		);

}
?>