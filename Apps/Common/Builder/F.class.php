<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/8/21
 * Time: 14:06
 */

namespace Common\Builder;


use Think\Model\MongoModel;

/**
 * Class F
 * Subject : 公用方法
 * Group :
 * @package Common\Builder
 */

class F
{
    use Log;
    /**
     * subject: 创建批量更新sql
     * api: createBatchUpdateSql
     * author: Mercury
     * day: 2017-08-21 14:27
     * @param array $data       数据
     * @param string $table     数据表名称
     * @param string $pri       数据表主键
     * @return string
     * UPDATE `ylh_orders_shop` SET `price` = CASE `id` WHEN '1' THEN '39.6' WHEN '2' THEN '47.8' END, `date` = CASE `id` WHEN '1' THEN '2017-08-21 14:43:18' WHEN '2' THEN '2017-08-21 14:43:18' END WHERE id IN(1,2)
     * $data   = [
            0 => [
                's_no'    => '2016081014151709637822',
                'pay_price' => 59.9,
                'pay_time'  => date('Y-m-d H:i:s')
            ],
            1 => [
                's_no'    => '2016081014175536458338',
                'pay_price' => 60.8,
                'pay_time'  => date('Y-m-d H:i:s')
            ],
        ];
     */
    public static function createBatchUpdateSql($data, $table, $pri)
    {
        $table  = C('DB_PREFIX') . $table;
        $sql    = "UPDATE `{$table}` SET ";
        $keys   = '';   //ids
        $data   = self::parseData($data);   //解析data数据
        foreach ($data as $k => $v) {
            if ($k == $pri) {
                $keys = join(',', $v);
            } else {
                $tmps   = " `{$k}` = CASE `{$pri}` ";
                foreach ($v as $item => $val) {
                    $tmps   .= " WHEN '{$data[$pri][$item]}' THEN '{$val}'";
                }
                $tmps   .= " END,";
            }
            $sql    .= $tmps;
        }
        $keys    = trim($keys, ',');
        $sql     = rtrim($sql, ',');
        $sql    .= " WHERE `{$pri}` IN({$keys})";
        return $sql;
    }

    /**
     * subject: 解析数据
     * api: parseData
     * author: Mercury
     * day: 2017-08-21 14:54
     * @param $data
     * @return array
     */
    public static function parseData($data)
    {
        $arr    = [];
        array_reduce($data, function (&$tmp, $val) use (&$arr) {
            foreach ($val as $k => $v) {
                $arr[$k][] = $v;
            }
        });
        return $arr;
    }

    /**
     * subject: 获取支付方式
     * api: getPayType
     * author: Mercury
     * day: 2017-08-23 9:51
     * @return \Common\Cache\mix|false|mixed|\PDOStatement|string|\think\Collection
     */
    public static function getPayType()
    {
        $model  = M('pay_type');
        $cacheName  = self::getCacheName('pay_type');   //获取缓存名称
        $data   = redisRead()->get($cacheName);
        if (false == $data) {
            $data   = $model->field('atime,etime,ename', true)->select();
            redisWrite()->set($cacheName, serialize($data));
        } else {
            $data   = unserialize($data);
        }
        return $data;
    }

    /**
     * subject: 获取缓存名称
     * api: getCacheName
     * author: Mercury
     * day: 2017-08-23 11:04
     * @param $arg  获取cache name 传参 pay_type.expire.row || pay_type.expire.lists  || pay_type.name.lists || pay_type.name.row     获取过期时间或者缓存名称
     * @param null $suffix  缓存名称后缀
     * @return string
     */
    public static function getCacheName($arg, $suffix = null)
    {
        $by     = 'lists';  //来自lists or row
        $key    = 'name';   //name or expire
        $name   = $arg;     //缓存短名称
        $suffix = is_null($suffix) ? '' : '_' . $suffix;
        $cnt    = substr_count($arg, '.');  //出现的次数
        if ($cnt > 0) {
            $name   = substr($arg, 0, strpos($arg, '.'));
            $key    = substr($arg, strpos($arg, '.') + 1);
        }
        if ($cnt > 1) {
            $tmp    = $key;
            $key    = substr($tmp, 0, strpos($tmp, '.'));
            $by     = substr($tmp, strripos($tmp, '.') + 1);
        }
        if ($by == 'row') {
            return ($key == 'name' ? CacheName::CACHE_NAME_PREFIX : '') . CacheName::CACHE_ROW[$name][$key] . $suffix;
        }
        return ($key == 'name' ? CacheName::CACHE_NAME_PREFIX : '') . CacheName::CACHE_TABLE[$name][$key] . $suffix;
    }
	
	
	/**
     * 获取缓存数据
     * Create by liangfeng
     * 2017-08-24
     */
	public static function getCache($arg,$suffix){
		$CacheName = self::getCacheName($arg,$suffix);
		$data   = redisRead()->get($CacheName);
		$data   = unserialize($data);
		return $data;
	}

    /**
     * subject: 记录日志
     * api: WriteLogByMongo
     * author: Mercury
     * day: 2017-08-25 9:42
     * @param $table        表名
     * @param array $data   数据
     * @param bool $suffix  后缀，  为true的时候会带上 【_年月】
     * @return bool
     */
    public static function WriteLogByMongo($table, $data = [], $suffix = false)
    {
        if ($suffix) $table = $table .'_'. date('Ym');  //加入后缀
        $model  = new MongoModel($table, C('DB_MONGO_CONFIG.DB_PREFIX'), C('DB_MONGO_CONFIG')); //模型对象
        if (!isset($data['time'])) $data['time'] = date('Y-m-d H:i:s'); //加入时间
        if (false == $model->add($data)) return false;
        return true;
	}
	
	/**
     * 获取银行数据
     * Create by liangfeng
     * 2017-09-07
     */
	public static function getBankName(){
        $cacheName  = self::getCacheName('bank_name');   //获取缓存名称
        $data   = redisRead()->get($cacheName);
        if (false == $data) {
            $data   = M('bank_name')->field('id,bank_code,bank_name,logo')->select();
            redisWrite()->set($cacheName, serialize($data));
        } else {
            $data   = unserialize($data);
        }
        return $data;
	}
}