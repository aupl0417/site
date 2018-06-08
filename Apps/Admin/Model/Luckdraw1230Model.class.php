<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Luckdraw1230Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='luckdraw1';
	protected $_validate = array(
        array('template','require','模板不能为空!',1,'regex',3),
        array('luckdraw_name','require','游戏名称不能为空!',1,'regex',3),
        array('images','require','主图不能为空!',1,'regex',3), 
        array('cid','require','所属类型不能为空!',1,'regex',3), 
        array('start_time','require','开始时间不能为空!',1,'regex',3),
        array('end_time','require','结束时间不能为空!',1,'regex',3), 
        array('position','require','共几个位置不能为空!',1,'regex',3), 
        array('rule','require','抽奖规则不能为空!',1,'regex',3), 
        array('status','require','状态不能为空!',1,'regex',3),
        array('status', 'checkStatus', '位置数不够或者位置中不包含谢谢抽奖奖品且中奖概率之和必须等于10000才能进行游戏', 1, 'callback'),
        array('position','checkPosition','位置在4-9个之间!',1,'callback',3),
        array('luckdraw_name','','游戏名称已存在!',1,'unique',1),
        array('game_images', 'require', '游戏图片不能为空', 1),
        array('coupon_condition', 'require', '优惠券面额不能为空', 1)
    );
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);

    /**
     * subject: 判断是否可以设置为游戏进行中状态
     * 如果奖品位置不符则返回false
     * api: checkStatus
     * author: Mercury
     * day: 2017-05-16 21:30
     * [字段名,类型,是否必传,说明]
     * @param $var
     * @return bool
     */
    protected function checkStatus($var)
    {
        //状态0禁用，1报名进行中，2禁止报名，3游戏进行中，4已完成
        if ($var == 3) {
            if (isset($_POST['id'])) {  //编辑的时候
                $position = $this->where(['id' => I('post.id')])->getField('position');
                //如果位置不够则返回false
                if ($position > M('luckdraw1_prize')->where(['luckdraw_id' => I('post.id')])->count()) return false;
                //如果未设置谢谢抽奖位置则返回false
                if (M('luckdraw1_prize')->where(['luckdraw_id' => I('post.id'), 'type_id' => 4])->getField('id') == false) return false;
                //中奖概率之和必须等于10000才能进行游戏
                if (M('luckdraw1_prize')->where(['luckdraw_id' => I('post.id')])->sum('probability') != 10000) return false;
            } else {
                return false;
            }
        }
        return true;
	}

    /**
     * subject: 位置必须在4-9个之间
     * api: checkPosition
     * author: Mercury
     * day: 2017-05-16 21:39
     * [字段名,类型,是否必传,说明]
     * @param $var
     * @return bool
     */
    protected function checkPosition($var)
    {
        if (false == is_numeric($var)) return false;
        if ($var > 9 || $var < 4) return false;
	}

}
?>