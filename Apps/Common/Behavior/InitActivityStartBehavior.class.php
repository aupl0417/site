<?php
/**
 * 启动活动
 */
namespace Common\Behavior;
use Think\Behavior;
class InitActivityStartBehavior extends Behavior {
    public function run(&$content) {
        $map    =   [
            'status'    =>  0,          //活动未开始标记
            'start_time'=>  ['elt', date('Y-m-d H:i:s', NOW_TIME)],
            'end_time'  =>  ['gt', date('Y-m-d H:i:s', NOW_TIME)],  //结束时间大于当前时间
        ];
        M('activity')->where($map)->setInc('status', 1);
    }
}