<?php
namespace Common\Model;
use Think\Model;

class WorkorderModel extends Model
{

	protected $tableName = 'workorder';
	protected $_validate = array(
        array('type','require','请选择工单类型!',1,'regex',3),
        array('type2','require','请选择工单详细类型!',1,'regex',3),
        array('title','require','请填写标题!',1,'regex',3),
        array('content','require','请填写描述内容!',1,'regex',3),
        array('mobile','require','请填写手机!',1,'regex',3),
        array('smstime','require','请选择手机接收短信时间!',1,'regex',3),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}