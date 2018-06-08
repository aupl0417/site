<?php
/**
 * ----------------------------------------
 * 唐人街任务处理
 * ----------------------------------------
 * Author: Lazycat <673090083@qq.com>
 * ----------------------------------------
 * 2017-03-21
 * -----------------------------------------
 */
class TrjWorker
{
    public $worker;
    /**
     * @param string $args['type'] 入列类型,即上面注释的类型
     * @param string $args['val'] 要处理的订单号或退款单号
     */
    public function run($args){
        //file_put_contents('/tmp/tmp/'.$args['type'].'.log',implode(',',$args['val']).PHP_EOL,FILE_APPEND);
        $res = curl_post(APIURL . '/CronV2/job',['type' => $args['type'],'val' => $args['val']]);
        return json_decode($res,true);
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

