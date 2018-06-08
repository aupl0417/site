<?php
namespace Common\Model;
use Think\Model;
class GoodsAttrListModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='goods_attr_list';
	protected $_validate = array(
        array('price','checkform','售价为数值型且须大于0.1元',1,'function',3,array('egt',0.1)), 
        array('num','checkform','数量为正整数且大于或等于0',1,'function',3,array(array('is_positive_number'),array('egt0'))), 
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>