<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Adsucai145Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='ad_sucai';
	protected $_validate = array(
        array('sucai_name','require','标题不能为空!',1,'regex',3), 
        array('uid','require','会员不能为空!',1,'regex',3), 
        array('category_id','require','投放类目不能为空!',1,'regex',3), 
        array('images','require','图片不能为空!',1,'regex',3), 
        array('height','require','高度不能为空!',1,'regex',3), 
        array('width','require','宽度不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>