<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Ad70Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='ad';
	protected $_validate = array(
        array('status','require','状态不能为空!',0,'regex',3), 
        array('position_id','require','广告位ID不能为空!',1,'regex',3), 
        array('name','require','广告标题不能为空!',0,'',3), 
        array('orderno','require','订单号不能为空!',0,'',3), 
        array('uid','require','会员ID不能为空!',0,'',3), 
        array('sday','require','开始投放日期不能为空!',0,'',3), 
        array('eday','require','结束投放日期不能为空!',0,'',3), 
        array('days','require','投放时段表不能为空!',0,'',3), 
        array('num','require','投放天数不能为空!',0,'',3), 
        array('price','require','日单价不能为空!',0,'',3), 
        array('money','require','金额不能为空!',0,'',3), 
        array('money_pay','require','实付金额不能为空!',0,'',3), 
        //array('images','require','广告图片不能为空!',0,'',3), 
        array('url','require','链接地址不能为空!',0,'',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>