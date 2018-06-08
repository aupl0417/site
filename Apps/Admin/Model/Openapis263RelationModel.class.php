<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\RelationModel;
class Openapis263RelationModel extends RelationModel {
	protected $tableName='open_apis';
	protected $_link = array(
'open_apis' => ['*'],
'open_api_category' => ['name' => 'category_name', '_on' => 'open_apis.cid = open_api_category.id']
		);

}
?>