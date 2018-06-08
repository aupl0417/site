<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class ShopvrlogsModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='shop_vr_logs';
	protected $_validate = array(
		array('shop_vr_id','require','参数错误!'),
		array('status','require','请选择处罚结果!'), 
		array('remark','require','审核回复不能为空!'), 
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>