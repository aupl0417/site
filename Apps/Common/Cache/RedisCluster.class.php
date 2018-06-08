<?php
/**
 * Created by PhpStorm.
 * User: mercury
 * Date: 16-9-9
 * Time: 下午4:55
 */

namespace Common\Cache;


class RedisCluster
{
    protected $_slave   =   [];
    protected $_servers =   [];
    protected $_config  =   [];
    protected $_mConfig =   [];
    protected $_sCount;
    protected $_master;
    protected $_timeOut =   1.5;

    function __construct($config = []) {
        if (!empty($config)) {
            $this->_config  =   $config;
        } else {
            $cfg = unserialize(GLOCAL_CONFIG);
            $this->_config  =   $cfg['redis'];
        }
        $this->_sCount      =   count($this->_config);
        $master             =   Master::getMaster();
        if (!is_null($master)) {
            $this->_mConfig     =   $this->_config[$master];
        }
    }

    public function _conn($isMaster = 1) {
        if ($isMaster == 1) {
            if (empty($this->_mConfig)) die('not master host!');
            $this->_master  =   new \Redis();
            $this->_master->connect($this->_mConfig['host'], $this->_mConfig['port'], $this->_timeOut);
            if (empty($this->_master->socket)) {  //zhao mster
                die('connect redis master fail!');
            }
            if (isset($this->_mConfig['auth']) && !empty($this->_mConfig['auth'])) {
                $this->_master->auth($this->_mConfig['auth']);
            }
        } else {
            $c = 1;
            $tmp = new \Redis();
            for ($i=0;$i<$this->_sCount;$i++) {
                $this->_slave[$i]   = $tmp;
                if ($this->_slave[$i]->connect($this->_config[$i]['host'], $this->_config[$i]['port'], $this->_timeOut) == false) {
                    unset($this->_slave[$i]);
                } else if (isset($this->_config[$i]['auth']) && !empty($this->_config[$i]['auth'])) {
                    $c++;
                    $this->_slave[$i]->auth($this->_config[$i]['auth']);
                }
            }
        }
    }

    public function getIntance($master = 1) {
        $this->_conn($master);
        if ($master == 1) {
            return $this->_master;
        }
        return $this->_slave[1];
    }

    public function __get($name)
    {
        if (property_exists($this->$name)) {
            return $this->$name;
        }
        return null;
        // TODO: Implement __get() method.
    }

    public function __set($name, $value)
    {
        //$var    =   '_' . $name;
        //if (property_exists($this, $var)) {
            $this->$name =   $value;
        //}
        // TODO: Implement __set() method.
    }
}