<?php
/**
 * Created by PhpStorm.
 * User: mercury
 * Date: 16-9-18
 * Time: 下午3:41
 */

namespace Vendor\Cluster;


class RpcLogEnvConfig
{
    const RPC_LOG_TYPE_REDIS = 'redis';
    const RPC_LOG_TYPE_SOCKET = 'socket';
    const RPC_LOG_TYPE_REDIS_CONNECT_FAIL = 'redis connect fail';
}