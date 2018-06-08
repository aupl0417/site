<?php
/**
 * Created by PhpStorm.
 * User: mercury
 * Date: 16-9-18
 * Time: 下午3:34
 */

namespace Vendor\Cluster;


class Loader
{
    static public function loadConfig($name) {
        $cfg = unserialize(GLOCAL_CONFIG);
        return $cfg[$name];
    }
}