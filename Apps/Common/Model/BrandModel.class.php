<?php
namespace Common\Model;
use Think\Model;
class BrandExtModel extends Model {
	protected $tableName='brand';
	protected $_validate = array(
                array('uid','require','用户ID不能为空!',1,'regex',3), 
                array('status','require','审核状态不能为空!',1,'regex',3), 
                array('b_name','require','品牌名称不能为空!',1,'regex',3), 
                array('b_logo','require','品牌logo不能为空!',1,'regex',3), 
                array('b_master','require','品牌所有者不能为空!',1,'regex',3), 
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>