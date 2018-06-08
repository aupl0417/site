<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Msgtpl104Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='msg_tpl';
	protected $_validate = array(
        array('tpl_name','require','模板名称不能为空!',0,'',3), 
        array('tpl_content','require','模板内容不能为空!',0,'',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>