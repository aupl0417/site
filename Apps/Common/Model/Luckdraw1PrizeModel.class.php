<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/5/15
 * Time: 15:41
 */

namespace Common\Model;


use Think\Model;

class Luckdraw1PrizeModel extends Model
{
    protected $tableName = 'luckdraw1_prize';
    protected $_validate = [
        ['luckdraw_id', 'require', '所属游戏不能为空', 1],
        ['type_id', 'require', '奖品类型不能为空', 1],
        ['probability', 'require', '中奖概率不能为空', 1],
        ['luckdraw_id', 'sumProbability', '中奖概率总和不能超过10000', 1, 'callback']
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
        $num = $this->where(['luckdraw_id' => $var])->sum('probability');
        writeLog($num);
        writeLog($this->getLastSql());
        if ($num > 10000) return false;
        return true;
    }
}