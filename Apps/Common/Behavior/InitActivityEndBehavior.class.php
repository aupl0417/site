<?php
/**
 * 结束活动
 */
namespace Common\Behavior;
use Think\Behavior;
class InitActivityEndBehavior extends Behavior {
    
    public function run(&$content) {
        $map    =   [
            'status'    =>  ['in', '0,1'],          //活动未开始标记及开始标记
            'end_time'  =>  ['lt', date('Y-m-d H:i:s', NOW_TIME)],  //结束时间小于当前时间
        ];
        M('activity')->where($map)->save(['status' => 2, 'over_time' => date('Y-m-d H:i:s', NOW_TIME)]);
    }
}