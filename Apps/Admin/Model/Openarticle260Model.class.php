<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Openarticle260Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='open_article';
	protected $_validate = array(
        array('title','require','文章标题不能为空!',1,'regex',3), 
        array('content','require','文章内容不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>