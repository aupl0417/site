<?php
/**
 * ----------------------------------------
 * 唐人街-官方秒杀上线及下线
 * ----------------------------------------
 * Author: Lazycat <673090083@qq.com>
 * ----------------------------------------
 * 2016-12-20
 * ----------------------------------------
 */
class Activity
{
    public $worker;

    public function run($args=null){
        switch ($args['type']){
            default:
                $res = curl_post(APIURL.'/Cron/Officialactivity',['time' => time()]);
                break;
        }

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

