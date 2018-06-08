<?php
namespace Common\Model;
use Think\Model;
class AccountModel extends Model {
	protected $tableName='account';
	protected $_validate = array(
        array('uid','require','用户ID不能为空！',1), 
        array('crc','require','CRC签名不能为空！',1),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>