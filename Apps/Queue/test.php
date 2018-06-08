<?php
date_default_timezone_set('Asia/Shanghai');
define('APP_DEBUG', true);
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', realpath(dirname(__FILE__)) . DS);
define('COMMON_PATH',ROOT_PATH . 'Common' . DS);
define('CONFIG_PATH',COMMON_PATH . 'Config' . DS);
define('FUNCTION_PATH',COMMON_PATH . 'Common' . DS);

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

$bs = new Beanstalkd();
$bs->connect();
$tubes = $bs->listTubes();
$bs->disconnect();
dojob('trj_orders_close');
function dojob($tube){
    $bs = new Beanstalkd();
    $bs->connect();
    $bs->useTube($tube);
    $bs->watch($tube);
    while(true){
        $job= $bs->reserve();
        if(empty($job)) break;
        print_r($job);
        file_put_contents('/tmp/tmp/job_'. microtime(true) .'_'.$job['id'].'txt',$job['body']);
        //$bs->delete($job['id']);
    }
    $bs->disconnect();
}
?>