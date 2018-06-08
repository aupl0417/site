<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Openapis263Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='open_apis';
	protected $_validate = array(
        array('name','require','api名称不能为空!',1,'regex',3), 
        array('url','require','请求地址不能为空!',1,'regex',3), 
        array('category_id','require','所属分类不能为空!',1,'regex',3), 
        array('is_free','require','是否收费不能为空!',1,'regex',3), 
        array('status','require','状态不能为空!',1,'regex',3), 
        array('intro','require','api介绍不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>