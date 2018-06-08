<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/4/21
 * Time: 17:05
 */

namespace Seller\Model;


use Think\Model;

class Luckdraw1ApplyModel extends Model
{
    protected $tableName = 'luckdraw1_apply';
    protected $_validate = [
        ['coupons', 'require', '优惠信息不能为空', 1],
        ['luckdraw_id', 'require', '报名促销不能为空', 1, 'regex', 1],
        ['luckdraw_id', 'isApply', '您已经参与过了', 1, 'callback', 1],
        //['luckdraw_id', 'isExpire', '报名的促销不存在或已过期', 1, 'callback', 1],
        ['id', 'isEdit', '已通过审核的不可再次编辑', 1, 'callback', 2],
        ['luckdraw_id', 'checkApply', '当前状态下不可申请', 1, 'callback'],
    ];

    protected $_auto = [
        //['uid', 'getUid', 'function', 1],
        //['shop_id', 'getShopId', 'function', 1],
    ];

    /**
     * subject: 判断是否已经参与过
     * api: isApply
     * author: Mercury
     * day: 2017-04-21 17:20
     * [字段名,类型,是否必传,说明]
     * @param $var
     * @return bool
     */
    protected function isApply($var) {
        $flag = M('Luckdraw1Apply')->cache(true)->where(['uid' => getUid(), 'luckdraw_id' => $var])->getField('id');
        if ($flag) return false;
        return true;
    }

    /**
     * subject: 判断促销是否正常
     * api: isExpire
     * author: Mercury
     * day: 2017-04-21 17:22
     * [字段名,类型,是否必传,说明]
     * @param $var
     * @return bool
     */
    protected function isExpire($var)
    {
        $map = [
            'id'    =>  $var,
            'status'=>  1,
            'start_time' => ['gt', date('Y-m-d H:i:s', NOW_TIME)]
        ];
        $flag = M('Luckdraw1')->where($map)->getField('id');
        if ($flag) return true;
        return false;
    }

    /**
     * subject: 判断是否可以编辑
     * api: isEdit
     * author: Mercury
     * day: 2017-04-22 11:00
     * [字段名,类型,是否必传,说明]
     * @param $var
     * @return bool
     */
    protected function isEdit($var)
    {
        $map = [
            'id'    =>  $var,
            'status'=>  3,  //被拒绝后才能修改
            'uid'   =>  getUid(),
        ];
        if ($this->where($map)->getField('id') == true) return true;
        return false;
    }

    /**
     * subject: 判断当前游戏是否可以申请
     * 当状态为报名中及游戏中的时候才可以报名
     * api: checkApply
     * author: Mercury
     * day: 2017-05-16 22:07
     * [字段名,类型,是否必传,说明]
     * @param $var
     * @return bool
     */
    protected function checkApply($var)
    {
        $var = $var ? : $this->where(['id' => I('post.id')])->cache(true)->getField('luckdraw_id');
        if (false == M('luckdraw1')->where(['id' => $var, 'status' => ['in', '1,3']])->getField('id')) {
            return false;
        }
        return true;
    }
}