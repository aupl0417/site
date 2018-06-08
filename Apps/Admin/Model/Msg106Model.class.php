<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Msg106Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='msg';
	protected $_validate = array(
        array('category_id','require','消息类型不能为空!',1,'',3), 
        array('from_uid','require','来源用户ID不能为空!',0,'',3), 
        array('from_nick','require','来源用户账号不能为空!',0,'',3), 
        array('to_uid','require','接收用户ID不能为空!',0,'',3), 
        array('to_nick','require','接收用户账号不能为空!',0,'',3), 
        array('content','require','消息内容不能为空!',0,'',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>