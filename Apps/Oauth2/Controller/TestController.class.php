<?php
/**
同步登录ERP系统-用户授权获取
*/
namespace Oauth2\Controller;
use Think\Cache\Driver\Memcached;
use Think\Controller;
class TestController extends Controller {
    //获取ERP 用户
    //获取ERP 用户
    public function memd(){
        $key=I('get.key');
        $mem = new \Memcached();
        $mem->addServer('10.0.0.50',12000);
        $mem->addServer('10.0.0.51',12000);

        echo '读取memcached Key的值：'.$mem->get($key);

    }

    public function mem(){
        $key=I('get.key');
        $memcache = new \Memcache;
        $memcache->connect('10.0.0.50',12000);

        echo '读取memcache Key的值：'.$mem->get($key);
    }

    public function test(){
        S('abc','abc');
        dump(S('abc'));
    }
}