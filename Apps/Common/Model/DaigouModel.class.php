<?php
namespace Common\Model;
use Think\Model;
class DaigouModel extends Model {
	protected $tableName='daigou';
	protected $_validate = array(
        array('uid','require','用户ID不能为空！',1), 
        array('goods_name','require','商品名称不能为空!',1), 
		array('num','require','代购商品数量不能为空!',1), 
	    array('url','require','第三方平台链接不能为空!',1),
        array('price','require','代购价格不能为空!',1), 
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),
	);
}
?>