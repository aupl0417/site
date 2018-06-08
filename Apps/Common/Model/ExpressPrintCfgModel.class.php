<?php
namespace Common\Model;
use Think\Model;
class ExpressPrintCfgModel extends Model {
	protected $tableName='express_print_cfg';
	protected $_validate = array(
                array('uid','require','用户ID不能为空!',1,'regex',3), 
                array('is_come','require','是否通知快递上门取件不能为空!',1,'regex',3),
                array('is_send','require','是否自动标记发货不能为空!',1,'regex',3),
                array('default_company_id','require','默认发货快递公司不能为空!',1,'regex',3),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>