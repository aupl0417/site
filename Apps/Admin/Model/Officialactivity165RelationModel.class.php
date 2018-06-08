<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\RelationModel;
class Officialactivity165RelationModel extends RelationModel {
	protected $tableName='officialactivity';
	protected $_link = array(
'officialactivity_category'	=>array(
					'mapping_type'		=>self::BELONGS_TO,
					'foreign_key'	=>'category_id',
					'parent_key'	=>'id',
					'mapping_name'	=>'category',
					'mapping_fields'=>'id,status,category_name,icon',
				),
		);

}
?>