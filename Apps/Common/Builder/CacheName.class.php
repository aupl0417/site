<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/8/23
 * Time: 10:38
 */

namespace Common\Builder;

/**
 * Class CacheName
 * Subject : 缓存名称
 * Group :
 * @package Common\Builder
 */

class CacheName
{
    const CACHE_NAME_PREFIX = 'trj_';    //缓存前缀

    //永久，时效[时间戳；在某个时间销毁，时间：多少秒后销毁]
    //table：【整表缓存】，fields:列表数据缓存

    //整表缓存
    const CACHE_TABLE   = [
        'pay_type'  => [
            'name'  => 'table_pay_type_lists',  //缓存名称
            'expire'=> 300,  //过期时间戳
        ],
		'config'  => [
            'name'  => 'table_config_lists',  //缓存名称
            'expire'=> 300,  //过期时间戳
        ],
		'bank_name'  => [
            'name'  => 'table_bank_name',  //缓存名称
            'expire'=> 300,  //过期时间戳
        ],
    ];

    //行缓存
    const CACHE_ROW = [
        'pay_type'  => [
            'name'  => 'table_pay_type_row',
            'expire'=> 5200,
        ],
		'config'  => [
            'name'  => 'table_config_row',
            'expire'=> 5200,
        ],
		'bank_name'  => [
            'name'  => 'table_bank_name_row',  //缓存名称
            'expire'=> 300,  //过期时间戳
        ],
    ];
}