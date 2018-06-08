<?php
namespace Common\Model;
use Think\Model;

class TuanApplyListModel extends Model
{


	protected $tableName = 'tuan_apply_list';


	protected $_validate = array(
        array('tuan_apply_id','require','团购申请表ID不能为空!',1,'regex',3),
        array('goods_attr_list_id','require','团购申请商品属性id不能为空!',1,'regex',3),
        array('ta_no','require','团购申请表订单号不能为空!',1,'regex',3),
        array('price','require','拼团购价格不能为空!',1,'regex',3),
        array('price','check_price','拼团购价格格式错误!',1,'callback',3),
        array('single_price','require','单独买价格不能为空!',1,'regex',3),
        array('single_price','check_single_price','单独买价格格式错误!',1,'callback',3),
        array('num','check_num','拼团购商品总数错误!',1,'callback',3),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),
	);

    protected function check_price($price){
        return (bool) is_numeric($price) && $price > 0;
    }

    protected function check_single_price($price){
        return (bool) is_numeric($price) && $price > 0;
    }

    protected function check_num($num){
        return (bool) preg_match('/^\d+$/', $num) && $num > 0;
    }
	
}