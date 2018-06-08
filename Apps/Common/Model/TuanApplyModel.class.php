<?php
namespace Common\Model;
use Think\Model;

class TuanApplyModel extends Model
{


	protected $tableName = 'tuan_apply';


	protected $_validate = array(
        array('ta_no','require','团购申请表订单号不能为空!',1,'regex',3),
        array('category_id','require','拼团分类必须选择!',1,'regex',3),
        array('category_id','check_category','拼团分类不存在!',1,'callback',3),
        array('goods_id','require','商品不能为空!',1,'regex',3),
        array('number','require','成团人数不能为空!',1,'regex',3),
        array('number','check_number','成团人数在2~10之内!',1,'callback',3),
        array('days','require','活动天数不能为空!',1,'regex',3),
        array('days','check_days','活动天数在1-5之内!',1,'callback',3),
        
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),
	);

    protected function check_number($number){
        return (bool) preg_match('/^\d+$/', $number) && $number >= 2 && $number <= 10;
    }

    protected function check_days($days){
        return (bool) preg_match('/^\d+$/', $days) && $days >= 1 && $days <= 5;
    }


    protected function check_category($category){
        return M('tuan_category')->where(['id' => $category])->getField('status') == 1;
    }
	
}