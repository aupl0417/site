<?php
namespace Home\Controller;
use Common\Cache\RedisDB;
use Common\Cache\RedisList;
use Common\Builder\Queue;
use Common\Common\Appeal;
use Common\Controller\OrdersController;
use Common\Controller\OrdersExpireController;
use Common\Notice\System;
use Common\Notice\Message;
use Common\Notice\Email;
use Common\Notice\Pushs;
use Common\Controller\OrdersExpireActionController;
class ServiceController extends CommonController {
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index() {
        $this->seo(['title' => '服务中心']);
        $this->display();
    }

    public function test()
    {
        $redis = redisRead();
        $a = $redis->get('key');
        dump($a);
    }

}