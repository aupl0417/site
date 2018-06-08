<?php
/**
 * ----------------------------------------
 * 唐人街待处理订单数据入列
 * ----------------------------------------
 * Author: Lazycat <673090083@qq.com>
 * ----------------------------------------
 * 2016-11-1
 * -----------------------------------------
 */
class TrjPut
{
    public $worker;
    /**
     * @param string $args['type'] 为要执行的方法名
     * @param string $args['days'] 对应TrjInQueue中的变量
     * @param string $args['goods_time'] 对应TrjInQueue中的变量
     * @param string $args['shop_time'] 对应TrjInQueue中的变量
     */
    public function run($args){
        $option = $args;
        $method = $args['type'];
        $task   = new TrjInQueue($option);
        $task->$method();
    }


    public function _exit(){
        $pid = posix_getpid();
        if($pid > 0) swoole_process::kill($pid);
    }

    public function get_pid(){
        $pid = posix_getpid();
        return $pid;
    }
}

