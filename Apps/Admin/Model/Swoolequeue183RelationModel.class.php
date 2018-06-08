<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\RelationModel;
class Swoolequeue183RelationModel extends RelationModel {
	protected $tableName='swoole_queue';
	protected $_link = array(
'swoole_worker'	=>array(
					'mapping_type'		=>self::HAS_ONE,
					'class_name'	=>'swoole_worker',
					'foreign_key'	=>'id',
					'mapping_key'	=>'worker_id',
					'mapping_name'	=>'worker',
					'mapping_fields'=>'id,name',
				),
		);

}
?>