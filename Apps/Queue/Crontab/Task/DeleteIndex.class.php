<?php
/**
 * ----------------------------------------
 * 唐人街删除商品索引或店铺索引
 * ----------------------------------------
 * Author: Lazycat <673090083@qq.com>
 * ----------------------------------------
 * 2016-11-1
 * -----------------------------------------
 */
class DeleteIndex
{
    public $worker;

    public function run($args=null){
        switch ($args['type']){
            case 'shop':    //清除店铺索引
                $res = curl_post(APIURL.'/Cron/xs_remove_shop_index',['time' => time()]);
                break;
            default:    //清除商品索引
                $res = curl_post(APIURL.'/Cron/xs_remove_goods_index',['time' => time()]);
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

