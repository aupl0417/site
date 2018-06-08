<?php
/**
 * ----------------------------------------
 * 唐人街每天基础数据统计
 * ----------------------------------------
 * Author: Lazycat <673090083@qq.com>
 * ----------------------------------------
 * 2016-11-1
 * -----------------------------------------
 */
class Totals
{
    public $worker;

    public function run($args=null){
		$res = curl_post(APIURL.'/Cron/totals',['time' => time()]);
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

