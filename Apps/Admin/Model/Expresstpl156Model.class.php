<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Expresstpl156Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='express_tpl';
	protected $_validate = array(
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('tpl_name','require','模板名称不能为空!',1,'regex',3), 
        array('province','require','省份不能为空!',1,'regex',3), 
        array('city','require','城市不能为空!',1,'regex',3), 
        array('district','require','区县不能为空!',1,'regex',3), 
        array('is_free','require','1=包邮不能为空!',1,'regex',3), 
        array('unit','require','1=按件数,2=按重量(kg)不能为空!',1,'regex',3), 
        array('is_express','require','是否启用快递不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>