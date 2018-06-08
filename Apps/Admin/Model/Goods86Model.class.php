<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Goods86Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='goods';
	protected $_validate = array(
        array('goods_name','require','宝贝标题不能为空!',1,'regex',3), 
        array('category_id','require','宝贝分类不能为空!',0,'',3), 
        //array('score_ratio','require','赠送积分比例不能为空!',1,'regex',3),
        array('images','require','主图不能为空!',1,'regex',3), 
        array('express_tpl_id','require','运费模板ID不能为空!',1,'regex',3), 
        array('shop_id','require','店铺ID不能为空!',1,'regex',3), 
        array('content','require','商品详情不能为空!',1,'regex',3),
	    array('service_days', 'isNumber', '售后天数必须是数字类型', 1, 'callback'),
        array('service_days', 'checkDays', '售后天数不正确', 1, 'callback'),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	
	/**
	 * 判断售后天数是否大于指定天数
	 * @param unknown $var
	 */
    protected function checkDays($var) {
        $serviceDays = M('goods_category')->where(['id' => I('post.category_id')])->getField('cate_service_days');
        if($serviceDays > 0 && $var < $serviceDays) {
            return false;
        }
        return true;
    }
    
    /**
     * 判断service_days是否为数字类型
     * @param unknown $var
     */
    protected function isNumber($var) {
        if (!is_numeric($var)) return false;
        return true;
    }
}
?>