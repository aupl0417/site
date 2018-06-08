<?php
/**
 * 优惠券过期        //需要使用定时任务，每天晚上12点整开始执行
*/
namespace Common\Behavior;
use Think\Behavior;
class InitCouponExpireBehavior extends Behavior {

    public function run(&$content) {
        $map    =   [
            'status'    =>  1,          //活动未开始标记及开始标记
            'eday'      =>  ['elt', date('Y-m-d H:i:s', NOW_TIME)],  //结束时间小于当前时间
        ];
        M('coupon')->where($map)->save(['status' => 3]);
        M('coupon_batch')->where($map)->save(['status' => 2]);
    }
}