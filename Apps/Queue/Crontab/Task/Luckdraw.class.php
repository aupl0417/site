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
class Luckdraw
{
    public $worker;

    public function run($args=null){
        switch ($args['type']){
            case 'clean':    //清除前一天未使用的抽奖机会
                $res = curl_post(APIURL.'/Cron/clean_luckdraw',['time' => time()]);
                break;
			case 'total':	//抽奖统计
				$res = curl_post(APIURL.'/Cron/luckdraw_total',['time' => time()]);
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

