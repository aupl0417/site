<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Custompage194Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='custom_page';
	protected $_validate = array(
        array('channel_name','require','频道名称不能为空!',1,'regex',3), 
        array('page','require','页面不能为空!',1,'regex',3), 
        array('domain','require','域名前缀不能为空!',1,'regex',3), 
        array('title','require','页面标题不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>