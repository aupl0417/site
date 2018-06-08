<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think\Cache\Driver;
use Think\Cache;
//defined('THINK_PATH') or exit();

/**
 * Redis缓存驱动 
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 */
class Redis extends Cache {
	 /**
	 * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options=array()) {
        if ( !extension_loaded('redis') ) {
            E(L('_NOT_SUPPORT_').':redis');
        }
        $options = array_merge(array (
            'host'          => C('REDIS_HOST') ? : '127.0.0.1',
            'port'          => C('REDIS_PORT') ? : 6379,
            'timeout'       => C('DATA_CACHE_TIMEOUT') ? : false,
            'auth'          => C('REDIS_AUTH') ? : false,   //认证
            'persistent'    => false,
        ),$options);

        $this->options           =  $options;
        $this->options['expire'] =  isset($options['expire'])?  $options['expire']  :   C('DATA_CACHE_TIME');
        $this->options['prefix'] =  isset($options['prefix'])?  $options['prefix']  :   C('DATA_CACHE_PREFIX');        
        $this->options['length'] =  isset($options['length'])?  $options['length']  :   0;        
        $func = $options['persistent'] ? 'pconnect' : 'connect';
        $this->handler  = new \Redis;
        
        $options['timeout'] === false ?
            $this->handler->$func($options['host'], $options['port']) :
            $this->handler->$func($options['host'], $options['port'], $options['timeout']);
        
        if ($this->options['auth'] !== false) $this->handler->auth($options['auth']); //认证
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        N('cache_read',1);
        $value = $this->handler->get($this->options['prefix'].$name);
        $jsonData  = json_decode( $value, true );
        return ($jsonData === NULL) ? $value : $jsonData;	//检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = null) {
        N('cache_write',1);
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        $name   =   $this->options['prefix'].$name;
        //对数组/对象数据进行缓存处理，保证数据完整性
        $value  =  (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if(is_int($expire) && $expire) {
            $result = $this->handler->setex($name, $expire, $value);
        }else{
            $result = $this->handler->set($name, $value);
        }
        if($result && $this->options['length']>0) {
            // 记录缓存队列
            $this->queue($name);
        }
        return $result;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name) {
        return $this->handler->delete($this->options['prefix'].$name);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolean
     */
    public function clear() {
        return $this->handler->flushDB();
    }
    
    /**
     * key是否已存在
     * @param string $key
     * @return boolean
     */
    public function keyExists($key) {
        return $this->handler->exists($key);
    }
    
    /**
     * key的剩余时间
     * @param string $key
     * @return integer
     */
    public function keyExpire($key) {
        return $this->handler->ttl($key);
    }
    
    /**
     * 修改key名
     * @param string $key
     * @param string $newkey
     * @return boolean
     */
    public function keyRename($key, $newkey) {
        if ($key == $newkey || $this->keyExists($key) == false) return false;
        return $this->handler->rename($key, $newkey);
    }
    
    /**
     * 获取key的类型
     * @param string $key
     * @return bool
     * none(key不存在)
     * string(字符串)
     * list(列表)
     * set(集合)
     * zset(有序集)
     * hash(哈希表)
     */
    public function keyType($key) {
        $type   =   $this->handler->type($key);
        if ($type == 'none') return false;
        return $type;
    }
    
    /**
     * 为key设置过期时间
     * @param string $key
     * @param integer $expire   秒数
     * @return boolean
     */
    public function keySetExpire($key, $expire) {
        return $this->handler->expire($key, $expire);
    }
    
    /**
     * 移除key的过期时间
     * @param string $key
     * @return boolean
     */
    public function keyPersist($key) {
        return $this->handler->persist($key);
    }
    
    /**
     * 为集合排序
     * @param string $key
     * @param string $limit
     * @param string $order ASC DESC ALPHA
     * @return array 
     */
    public function keySort($key, $limit = '0 -1', $order = 'ASC') {
        return $this->handler->sort($key, $limit, $order);
    }
    
    /**
     * 随机返回key
     * @return string
     */
    public function keyRandom() {
        return   $this->handler->randomkey();
    }
    
    /**
     * 默认返回所有key,可使用正则
     * @param string $key
     * KEYS *命中数据库中所有key。
     * KEYS h?llo命中hello， hallo and hxllo等。
     * KEYS h*llo命中hllo和heeeeello等。
     * KEYS h[ae]llo命中hello和hallo，但不命中hillo。
     * @return string
     */
    public function keys($key = '*') {
        return $this->handler->keys($key);
    }
    
    /**
     * 删除一个key
     * @param string $key
     * @return boolean
     */
    public function del($key) {
        return $this->handler->del($key);
    }
}
