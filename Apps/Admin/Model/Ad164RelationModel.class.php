<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\RelationModel;
class Ad164RelationModel extends RelationModel {
	protected $tableName='ad';
	protected $_link = array(
'ad_position'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'foreign_key'	=>'id',
					'mapping_key'	=>'position_id',
					'mapping_name'	=>'ad_position',
				),
		);

}
?>