<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Authperson99Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='auth_person';
	protected $_validate = array(
        array('uid','require','用户ID不能为空!',0,'',3), 
        array('status','require','状态不能为空!',0,'',3), 
        array('name','require','姓名不能为空!',0,'',3), 
        array('card_no','require','证件号码不能为空!',0,'',3), 
        array('card_type','require','证件类型不能为空!',0,'',3), 
        array('card_pic','require','证件正面图片不能为空!',0,'',3), 
        array('card_pic2','require','证件背面图片不能为空!',0,'',3), 
        array('card_pic3','require','手持证件图片不能为空!',0,'',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>