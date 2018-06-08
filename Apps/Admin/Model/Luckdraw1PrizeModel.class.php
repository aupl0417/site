<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/4/28
 * Time: 13:57
 */

namespace Admin\Model;


use Think\Model;

/**
 * 奖品验证模型
 *
 * Class Luckdraw1PrizeModel
 * @package Admin\Model
 */

class Luckdraw1PrizeModel extends Model
{
    protected $tableName = 'luckdraw1_prize';
//    protected $_vallidate= [
//        ['probability', 'require', '中奖概率不能为空', 1],
//        ['sort', 'require', '排序不能为空', 1],
//        ['prize_id', 'require', '奖品不能为空', 1],
//        ['max_winning', 'require', '每天最大中奖数量不能为空', 1],
//    ];

    protected $_validate = [
        ['luckdraw_id', 'require', '所属游戏不能为空', 1],
        ['type_id', 'require', '奖品类型不能为空', 1],
        ['probability', 'require', '中奖概率不能为空', 1],
        ['luckdraw_id', 'sumProbability', '中奖概率总和不能超过10000', 1, 'callback'],
        ['max_winning', 'require', '每天最大中奖数量不能为空', 1],
        ['sort', 'require', '排序不能为空', 1],
        ['value', 'checkValue', '类型为积分时积分为1-10000个，类型为代金券或者优惠券时不能为空', 1, 'callback']
    ];

    /**
     * subject: 总和不能大于10000
     * api: sumProbability
     * author: Mercury
     * day: 2017-05-15 15:47
     * [字段名,类型,是否必传,说明]
     * @param $var
     * @return bool
     */
    protected function sumProbability($var)
    {
        $num = $this->where(['luckdraw_id' => $var, 'id' => ['neq', I('post.id')]])->sum('probability');
        if ((int)($num+I('post.probability')) > 10000) return false;
        return true;
    }

    /**
     * subject: 奖品判断
     * api: checkValue
     * author: Mercury
     * day: 2017-05-16 21:58
     * [字段名,类型,是否必传,说明]
     * @param $var
     * @return bool
     */
    protected function checkValue($var)
    {
        if (isset($_POST['type_id'])) {
            switch (I('post.type_id')) {
                case 1: //代金券
                    if (empty($var)) return false;
                    break;
                case 2: //优惠券
                    if (empty($var)) return false;
                    break;
                case 3: //积分
                    if (empty($var)) return false;
                    if (false == is_numeric($var)) return false;
                    if ($var <= 0 || $var >=10000) return false;
                    break;
            }
        }
        return true;
    }
}