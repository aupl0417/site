<?php
namespace Common\Builder;
use Common\Cache\RedisList;
require_once './Apps/Queue/beanstalk/src/Client.php'; //加载 beanstalk
class Queue {
    
    //目前可执行队列
    public static $queueName    =   [
        'spike'     =>  [
            'name'  =>  'spike',        //0元购及秒杀
            'func'  =>  'closeParticipate', //执行方法
        ],
        'coupon'    =>  [
            'name'  =>  'coupon',       //优惠券
            'func'  =>  'closeCoupon',//执行方法
        ],
        'couponBatch'    =>  [
            'name'  =>  'couponBatch',       //优惠券批次
            'func'  =>  'closeCouponBatch',//执行方法
        ],
        'activity'    =>  [
            'name'  =>  'activity',       //促销或者
            'func'  =>  'closeActivity',//执行方法
        ],
        'spikes'    =>  [               //定时处理的0元购及秒杀促销
            'name'  =>  'spikes',
            'func'  =>  'closeParticipate',//执行方法
        ],
    ];

    const max_process   =   10; //最大为10个线程
    const pageSize      =   1000;//1000条记录为一页
    //入列
    public static function intoQueue($key, $value, $max = null) {
        if (is_array($value)) $value    =   serialize($value);
        return self::redisList($max)->lpush($key['name'], $value);
    }
    
    //出列
    public static function outQueue($key, $isPrefix = true) {
        return self::redisList()->rpop($key, $isPrefix);
    }
    
    public static function redisList($max = null) {
        return new RedisList($max);
    }
    
    /**
     * 数据处理
     * @param string $key
     */
    public static function run($key) {
        $parentKey      =   self::redisList()->rpop($key);  //取子队列
        if ($parentKey) {
            $parentKey  =   unserialize($parentKey);    //反序列化父队列
            $childData  =   self::redisList()->lrange($parentKey['child'], false);  //取出子列
            if ($childData) {
                $countChild =   count($childData);
                for ($i = 0; $i < $countChild; $i ++) {
                    self::redisList()->rpop($parentKey['child'], false);
                    /*$data[$i]   =   unserialize(self::redisList()->rpop($parentKey['child'], false));
                    if (Cron::$parentKey['func']($data[$i]['map']) == false) {
                        self::redisList()->lpush($key . '_error', serialize($data[$i]));    //如果数据处理失败则将其列入失败队列
                    }*/
                }
            }
        }
    }
    
    /**
     * 第一列出列
     * 第二列入列
     * @param string $source
     * @param string $destination
     */
    public static function outAndIntoQueue($source, $destination) {
        $size   =   self::redisList()->llen($source);
        if ($size > 0) {
            $count  =   0;
            foreach (self::redisList()->lrange($source) as $k => $v) {
                $v  =   unserialize($v);
                if ($v['time'] <= NOW_TIME) {
                    $count++;
                    self::redisList()->rpush($destination, serialize($v));
                }
            }
            if ($count > 0) self::redisList()->ltrim($source, -1, ($size - $count));
        }
    }

    /**
     * 定时执行的任务
     * @param $type
     * @return bool
     * ding shi ren wu
     */
    public static function cronIntoQueue($type) {
        $flag   =   false;
        switch ($type) {
            case 'couponBatch': //coupon batch
                $model  =   M('coupon_batch');
                $map    =   [
                    'status'    =>  1,
                    'eday'      =>  ['elt', date('Y-m-d')],
                ];
                $count  =   $model->where($map)->count();
                if ($count > 0) {
                    $page   =   ceil($count / self::pageSize);
                    for ($i = 1; $i <= $page; $i++) {
                        $data   =   $model->where($map)->order('id desc')->page($i, self::pageSize)->field('id')->select();
                        $flag   =   true;
                        if ($data) {
                            foreach ($data as $v) {
                                //ru lie coupon batch
                                if (self::intoQueue(self::$queueName['couponBatch'], ['map' => ['id' => $v['id']]])) {
                                    self::getCoupon($v['id']);  //receive coupon
                                };
                            }
                        }
                    }
                }
                break;
            case 'activity':    //chang gui huo dong
                $model  =   M('activity');
                $map    =   [
                    'status'    =>  1,
                    'end_time'  =>  ['elt', date('Y-m-d H:i:s', NOW_TIME)],
                ];
                $count  =   $model->where($map)->count();
                if ($count > 0) {
                    $page   =   ceil($count / self::pageSize);
                    for ($i = 1; $i <= $page; $i++) {
                        $data   =   $model->where($map)->order('id desc')->page($i, self::pageSize)->field('id')->select();
                        if ($data) {
                            $flag   =   true;
                            foreach ($data as $v) {
                                self::intoQueue(self::$queueName['activity'], ['map' => ['id' => $v['id']]]);
                            }
                        }
                    }
                }
                break;
            case 'spikes':  //0 yuan gou & miao sha
                $model  =   M('activity_participate');
                $map    =   [
                    'type_id'   =>  ['in', '5,6'],
                    'status'    =>  0,
                    'atime'     =>  ['egt', date('Y-m-d H:i:s', (NOW_TIME - (15 * 60)))],   //15 mins ago
                ];
                $count  =   $model->where($map)->count();
                if ($count > 0) {
                    $page   =   ceil($count / self::pageSize);
                    for ($i = 0; $i < $page; $i++) {
                        $data   =   $model->where($map)->field('id,s_no')->order('id desc')->page($i, self::pageSize)->select();
                        if ($data) {
                            $flag   =   true;
                            foreach ($data as $v) {
                                self::intoQueue(self::$queueName['spikes'], ['map' => ['activity' => ['id' => $v['id']], 'orders' => ['s_no' => $v['s_no']]]]);
                            }
                        }
                    }
                }
                break;
        }
        return $flag;
    }

    /**
     * 获取已领取的优惠券
     * @param $id integer
     */
    private static function getCoupon($id) {
        $count  =   M('coupon')->where(['b_id' => $id])->count();
        if ($count > 0) {
            $page   =   ceil($count / self::pageSize);
            for ($i = 1; $i <= $page; $i++) {
                $data   =   M('coupon')->where(['b_id' => $id])->order('id desc')->page($page, self::pageSize)->field('id')->select();
                if ($data) {
                    foreach ($data as $v) {
                        self::intoQueue(self::$queueName['coupon'], ['map' => ['id' => $v['id']]]);
                    }
                }
            }
        }
    }

    /**
     * work
     * 处理队列信息
     */
    public static function work() {
        foreach (self::$queueName as $k => $v) {
            for ($i = 0; $i <= self::max_process; $i++) {
                $pid    =   pcntl_fork();
                if ($pid == -1) {
                    writeLog('pid is -1, run time ' . date('Y-m-d H:i:s', time()));
                } else if ($pid) {
                    pcntl_wait($status);
                } else {
                    self::job($k);
                    sleep(1);
                    exit(0);
                }
            }
        }
    }

    /**
     * ruLie
     * 入列
     */
    public static function put() {
        foreach (self::$queueName as $k => $v) {
            self::getInstance()->useTube($k);
            self::getInstance()->put(
                0,	//任务优先级
                0,	//不等待直接放入队列
                30,	//处理任务时间
                $k
            );
        }
        self::getInstance()->disconnect();
    }

    /**
     * @param $key string Lie Ming
     * 出列
     */
    private static function job($key) {
        $beans  =   self::getInstance();
        $redis  =   self::redisList();
        $beans->useTube($key);
        $beans->watch($key);
        while (true) {
            $job    =   $beans->reserve();
            $queue  =   $redis->rpoplpush($job['body'], $job['body'] . '_backup');
            if ($queue) {
                $queue  =   unserialize($queue);
                $size   =   $redis->lsize($queue['child']);
                if ($size > 0) {
                    for ($i = 1; $i <= $size; $i++) {
                        $childRpop   =   $redis->rpoplpush($queue['child'], $queue['child'] . '_backup', false);
                        if ($childRpop) {
                            $childRpopArr   =   unserialize($childRpop);
                            $func           =   $queue['func'];
                            $flag           =   Cron::$func($childRpopArr['map']);
                            if ($flag == false) {
                                $redis->lpush($key . '_sql_error', $childRpop);
                            }
                        }
                    }
                } else {
                    $beans->delete($job['id']);
                }
            } else {
                $beans->delete($job['id']);
            }
        }
        $beans->disconnect();
    }

    /**
     * @return Client Shi Li
     */
    private static function getInstance() {
        $beans  =   new \Beanstalk\Client(['persistent' => false, 'logger' => true]);
        if ($beans->connect() == false) {
            exit(0);
        }
        return $beans;
    }
}