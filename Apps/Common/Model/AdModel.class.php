<?php
namespace Common\Model;
use Think\Model;
class AdModel extends Model {
	protected $tableName='ad';
	protected $_validate = array(
                array('uid','require','用户ID不能为空!',1,'regex',3), 
                array('a_no','require','订单号不能为空!',1,'regex',3), 
                array('name','require','广告标题不能为空!',1,'regex',3), 
                array('position_id','require','广告位ID不能为空!',1,'regex',3), 
                array('status','require','投放状态不能为空!',1,'regex',3), 
                array('sort','require','投放位置不能为空!',1,'regex',3), 
                array('sday','require','投放起始日期不能为空!',1,'regex',3), 
                array('eday','require','投放结束日期不能为空!',1,'regex',3), 
                array('days','require','投放日期列表不能为空!',1,'regex',3), 
                array('num','require','投放天数不能为空!',1,'regex',3), 
                array('images','require','广告图片不能为空!',1,'regex',3), 
                array('url','require','URL不能为空!',1,'regex',3), 
                array('price','require','单价不能为空!',1,'regex',3), 
                array('money','require','合计金额不能为空!',1,'regex',3), 
                array('money_pay','require','实付金额不能为空!',1,'regex',3), 
                array('type','require','投放类型不能为空!',1,'regex',3), 
                array('sucai_id','require','素材ID不能为空!',1,'regex',3), 
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>