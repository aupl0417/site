<?php
/**
 * Redis List
 */
namespace Common\Cache;
use Think\Cache\Driver\Redis;
use Common\Builder\Queue;
class RedisList extends Redis {
    const TYPE              =   3;      //当前类型，list
    public $_parentMax   =   100000;   //主队列最长
    public $_childMax    =   1000;    //子队列最大数量
    public $_parentPrefix=   'list_parent_';  //主队列前缀
    public $_childPrefix =   'list_child_';   //子队列前缀
    private $_key;
    
    function __construct($parentMax = null, $childMax = null, $options = array()) {
        parent::__construct();
        if($parentMax) $this->_parentMax =   $parentMax;
        if($childMax) $this->_childMax  =   $childMax;
    }
    
    /**
     * 将值value插入到列表key的表头。
     * 如果key不存在，一个空列表会被创建并执行LPUSH操作。
     * 当key存在但不是列表类型时，返回一个错误 
     * @param string $key
     * @param string $value
     * @return boolean
     */
    public function lpush($key, $value) {
        //如果key存在并且key的类型非list时直接返回false
        $s  =   microtime(true);
        if ($this->isType($this->_parentPrefix . $key) == false) return false;
        $parentSize =   $this->llen($this->_parentPrefix . $key);
        //父列
        $parentData =   [
            'child' =>  $this->_childPrefix . $key . '_' . ($parentSize + 1),
            'func'  =>  Queue::$queueName[$key]['func'],
        ];
        $parentValue=   serialize($parentData);
        if ($parentSize == 0) {
            if ($this->handler->lpush($this->_parentPrefix . $key, $parentValue) == false) {
                return false;
            }
            $parentSize =   1;
        }
        if ($parentSize < $this->_parentMax) {
            if ($this->llen($this->_childPrefix . $key . '_' . $parentSize) >= $this->_childMax) {
                if ($this->handler->lpush($this->_parentPrefix . $key, $parentValue) == false) {    //每次都加1
                    return false;
                }
                return $this->handler->lpush($this->_childPrefix . $key . '_' . ($parentSize + 1), $value);
            } else {
                return $this->handler->lpush($this->_childPrefix . $key . '_' . $parentSize, $value);
            }
        }
        //if ($this->llen($key) >= $this->_max) return false;
    }
    
    /**
     * 将值value插入到列表key的表头，并且且key存在并且是一个列表
     * 和LPUSH命令相反，当key不存在时，LPUSHX命令什么也不做
     * @param string $key
     * @param string $value
     * @return boolean
     */
    public function lpushx($key, $value) {
        if ($this->keyExists($key) == false || ($this->keyType($key) !== self::TYPE)) return false;
        return $this->handler->lpushx($key, $value);
    }
    
    
    /**
     * 将值value插入到列表key的表尾
     * 如果key不存在，一个空列表会被创建并执行RPUSH操作
     * 当key存在但不是列表类型时，返回一个错误
     * @param string $key
     * @param string $vlaue
     * @return boolean
     */
    public function rpush($key, $vlaue) {
        if ($this->isType($key) == false) return false;
        return $this->handler->rpush($key, $vlaue);
    }
    
    
    /**
     * 将值value插入到列表key的表尾，当且仅当key存在并且是一个列表
     * 和RPUSH命令相反，当key不存在时，RPUSHX命令什么也不做
     * @param string $key
     * @param string $value
     * @return boolean
     */
    public function rpushx($key, $value) {
        if ($this->keyExists($key) == false || ($this->keyType($key) !== $this->_type)) return false;
        return $this->handler->rpushx($key, $value);
    }
    
    
    /**
     * 移除并返回列表key的头元素
     * @param string $key
     * @return boolean
     */
    public function lpop($key, $isPrefix = true) {
        if ($isPrefix)  $key    =   $this->_parentPrefix . $key;
        return $this->handler->lpop($key);
    }
    
    
    /**
     * 移除并返回列表key的尾元素
     * @param string $key
     * @param bool $prefix   //前缀    查父队列的时候需要带上前缀，查子队列的时候直接使用父队列的值所以不需要前缀
     * @return boolean
     */
    public function rpop($key, $isPrefix = true) {
        if ($isPrefix)  $key    =   $this->_parentPrefix . $key;
        return $this->handler->rpop($key);
    }
    
    
    /**
     * BLPOP是列表的阻塞式(blocking)弹出原语
     * 它是LPOP命令的阻塞版本，当给定列表内没有任何元素可供弹出的时候，连接将被BLPOP命令阻塞，直到等待超时或发现可弹出元素为止
     * 当给定多个key参数时，按参数key的先后顺序依次检查各个列表，弹出第一个非空列表的头元素
     * @param string $key
     * @param number $timeout
     * @return boolean
     */
    public function blpop($key, $timeout = 0) {
        return $this->handler->blpop($key, $timeout);
    }
    
    
    /**
     * BRPOP是列表的阻塞式(blocking)弹出原语
     * 它是RPOP命令的阻塞版本，当给定列表内没有任何元素可供弹出的时候，连接将被BRPOP命令阻塞，直到等待超时或发现可弹出元素为止
     * 当给定多个key参数时，按参数key的先后顺序依次检查各个列表，弹出第一个非空列表的尾部元素
     * 关于阻塞操作的更多信息，请查看BLPOP命令，BRPOP除了弹出元素的位置和BLPOP不同之外，其他表现一致
     * @param string $key
     * @param number $timeout
     * @return boolean
     */
    public function brpop($key, $timeout = 0) {
        return $this->handler->brpop($key, $timeout);
    }
    
    
    /**
     * 返回列表key的长度
     * 如果key不存在，则key被解释为一个空列表，返回0.
     * 如果key不是列表类型，返回一个错误
     * @param string $key
     * @return boolean
     */
    public function llen($key) {
        if ($this->isType($key) == false) return false;
        return $this->handler->llen($key);
    }
    
    
    /**
     * 返回列表key中指定区间内的元素，区间以偏移量start和stop指定
     * 下标(index)参数start和stop都以0为底，也就是说，以0表示列表的第一个元素，以1表示列表的第二个元素，以此类推
     * 你也可以使用负数下标，以-1表示列表的最后一个元素，-2表示列表的倒数第二个元素，以此类推
     * @param string $key
     * @param boolean $prefix 当前缀为true的时候，则取父队列前缀
     * @param number $start
     * @param number $end
     * @return boolean
     */
    public function lrange($key, $prefix = true, $start = 0, $end = -1) {
        if ($this->isType($key) == false) return false;
        if ($prefix) $key   =   $this->_parentPrefix . $key;
        return $this->handler->lrange($key, $start, $end);
    }
    
    
    /**
     * 根据参数count的值，移除列表中与参数value相等的元素
     * @param string $key   
     * @param integer $count    删除数量
     * @param string $value     删除当前key中的值
     * @return boolean
     */
    public function lrem($key, $count, $value) {
        if ($this->isType($key) == false) return false;
        return $this->handler->lrem($key, $count, $value);
    }
    
    
    /**
     * 将列表key下标为index的元素的值甚至为value
     * @param string $key
     * @param integer $index
     * @param string $value
     * @return boolean
     */
    public function lset($key, $index, $value) {
        if ($this->isType($key) == false) return false;
        return $this->handler->lset($key, $index, $value);
    }
    
    
    /**
     * 对一个列表进行修剪(trim)，就是说，让列表只保留指定区间内的元素，不在指定区间之内的元素都将被删除
     * 举个例子，执行命令LTRIM list 0 2，表示只保留列表list的前三个元素，其余元素全部删除
     * 下标(index)参数start和stop都以0为底，也就是说，以0表示列表的第一个元素，以1表示列表的第二个元素，以此类推
     * 你也可以使用负数下标，以-1表示列表的最后一个元素，-2表示列表的倒数第二个元素，以此类推
     * 当key不是列表类型时，返回一个错误 
     * @param string $key
     * @param integer $start
     * @param integer $end
     * @return boolean
     */
    public function ltrim($key, $start, $end) {
        if ($this->isType($key) == false) return false;
        return $this->handler->ltrim($key, $start, $end);
    }
    
    
    /**
     * 返回列表key中，下标为index的元素
     * 下标(index)参数start和stop都以0为底，也就是说，以0表示列表的第一个元素，以1表示列表的第二个元素，以此类推
     * 你也可以使用负数下标，以-1表示列表的最后一个元素，-2表示列表的倒数第二个元素，以此类推
     * 如果key不是列表类型，返回一个错误 
     * @param string $key
     * @param string $index
     * @return boolean
     */
    public function lindex($key, $index) {
        if ($this->isType($key) == false) return false;
        return $this->handler->lindex($key, $index);
    }
    
    
    /**
     * 将值value插入到列表key当中，位于值pivot之前或之后
     * 当pivot不存在于列表key时，不执行任何操作
     * 当key不存在时，key被视为空列表，不执行任何操作
     * 如果key不是列表类型，返回一个错误 
     * @param string $key
     * @param string $position after before
     * @param string $pivot val1 val2
     * @return boolean
     */
    public function linsert($key, $position, $pivot) {
        if ($this->isType($key) == false) return false;
        return $this->handler->linsert($key, $position, $pivot);
    }
    
    
    /**
     * 命令RPOPLPUSH在一个原子时间内，执行以下两个动作
     * 将列表source中的最后一个元素(尾元素)弹出，并返回给客户端
     * 将source弹出的元素插入到列表destination，作为destination列表的的头元素 
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function rpoplpush($source, $destination) {
        if ($this->isType($source) == false) return false;
        return $this->handler->rpoplpush($source, $destination);
    }
    
    
    /**
     * BRPOPLPUSH是RPOPLPUSH的阻塞版本，当给定列表source不为空时，BRPOPLPUSH的表现和RPOPLPUSH一样
     * 当列表source为空时，BRPOPLPUSH命令将阻塞连接，直到等待超时，或有另一个客户端对source执行LPUSH或RPUSH命令为止
     * 超时参数timeout接受一个以秒为单位的数字作为值。超时参数设为0表示阻塞时间可以无限期延长(block indefinitely) 
     * @param string $source
     * @param string $destination
     * @param number $timeout
     * @return boolean
     */
    public function brpoplpush($source, $destination, $timeout = 0) {
        if ($this->isType($source) == false) return false;
        return $this->handler->brpoplpush($source, $destination, $timeout);
    }
    
    /**
     * 判断当前key的类型是否为list集合
     * @param string $key
     * @return boolean
     */
    protected function isType($key) {
        //如果key存在并且key的类型非list时直接返回false
        if ($this->keyExists($key) == true && ($this->keyType($key) != self::TYPE)) return false;
        return true;
    }
}