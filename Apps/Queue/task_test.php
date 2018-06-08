<?php
date_default_timezone_set('Asia/Shanghai');
define('APP_DEBUG', true);
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', realpath(dirname(__FILE__)) . DS);
define('COMMON_PATH',ROOT_PATH . 'Common' . DS);
define('CONFIG_PATH',COMMON_PATH . 'Config' . DS);
define('FUNCTION_PATH',COMMON_PATH . 'Common' . DS);
define('TUBE_PREFIX','trj_');

include FUNCTION_PATH . 'function.php';

$config = array(
    //Beanstalkd参数
    'BS_HOST'       => '127.0.0.1',
    'BS_PORT'       => 11300,
    'BS_TIMEOUT'    => 1,
    'BS_LOGGER'     => null,

    //数据库配置
    'DB_TYPE'       => 'mysql',
    'DB_HOST'       => '192.168.3.203',
    'DB_USER'       => 'root',
    'DB_PWD'        => 'sql@8234ERe8',
    'DB_PORT'       => '3306',
    'DB_NAME'       => 'dtmall',
    'DB_CHARSET'    => 'utf8',
    'DB_PREFIX'     => 'ylh_',
    'DB_PCONNECT'   => false,
);

foreach($config as $key => $val){
    define($key,$val);
}



        spl_autoload_register(function ($name) {
            $file_path = ROOT_PATH . "Vendor" . DS . $name . ".class.php";
            if(file_exists($file_path)) include $file_path;
        });


$trj = new TrjInQueue(['days' => 5]);
//$list = $trj->orders_close();
//$list = $trj->orders_confirm();
//$list = $trj->orders_rate();
//$list = $trj->refund_not_express();
//$list = $trj->refund_first();
//$list = $trj->refund_reject();
//$list = $trj->refund_edit();
//$list = $trj->refund_goods_accept();
$list = $trj->orders_confirm_refund_finished();
//print_r($list);


$bs = new Beanstalkd();
$bs->connect();
$bs->useTube('trj_orders_confirm_refund_finished');
$bs->watch('trj_orders_confirm_refund_finished');
while(true){
    $job= $bs->reserve();
    if(empty($job)) break;
    print_r($job);
}

?>