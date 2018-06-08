<?php
/**
 * Created by PhpStorm.
 * User: mercury
 * Date: 16-9-18
 * Time: 下午3:31
 */

namespace Vendor\Cluster;


class RpcLog
{
    static public function getMicroTime() {
        return microtime(true);
    }

    static public function log($text, $stime, $etime, $type) {
        return true;
        writeLog($text . '__sTime:' . $stime . '__eTime:' . $etime . '__sec:' . ($etime - $stime) . '__type:' . $type);
    }
}