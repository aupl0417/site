<?php
namespace Common\Model;
use Think\Model;
class ExpressTplModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='express_tpl';
	protected $_validate = array(
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('tpl_name','require','运费模板名称不能为空!',1,'regex',3), 
        array('province','require','省份不能为空!',1,'regex',3), 
        array('city','require','城市不能为空!',1,'regex',3), 
        array('district','require','区、县不能为空!',1,'regex',3), 
        array('is_free','require','是否包邮不能为空!',1,'regex',1), 
        array('unit','require','计价方式不能为空!',1,'regex',1), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),
		//array('uid', 'getUid', 1, 'function'),
	);
	
	//protected $updateFields	=	['tpl_name','province','city','district', 'uid'];

}
?>