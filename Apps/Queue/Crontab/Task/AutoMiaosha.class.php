<?php
/**
 * ----------------------------------------
 * 清除前一天未使用的赠送抽奖机会
 * ----------------------------------------
 * Author: Lazycat <673090083@qq.com>
 * ----------------------------------------
 * 2016-11-12
 * -----------------------------------------
 */
class AutoMiaosha
{
    public $worker;

    public function run($args=null){
        $res = curl_post(APIURL.'/CronV2/auto_activity',[]);

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

