<?php
/**
 * ----------------------------------------
 * 唐人街每10分钟刷新宝贝上架时间
 * ----------------------------------------
 * Author: Lazycat <673090083@qq.com>
 * ----------------------------------------
 * 2016-11-1
 * -----------------------------------------
 */
class GoodsUptime
{
    public $worker;

    public function run($args=null){
		$res = curl_post(APIURL.'/Cron/goods_uptime',['time' => time()]);
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

