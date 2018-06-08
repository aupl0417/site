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
    'DB_PWD'        => '123456',
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




$db = new db();
$list = $db->table('swoole_queue')->select();

//print_r($list);

$arr=['Ainfo','Binfo'];

foreach($list as $val){
    $bs = new BeanstalkdJob($val['tube']);
    for($i=0;$i<50;$i++){
        $data = [
            'execute'   => $val['tube'] == 'tube_abc' ? 'Ainfo' :'Binfo',
            'args'      => ['type' => md5(microtime(true))],
        ];
        //print_r($data);
        $bs->put($data);
    }
}

$bs = new Beanstalkd();
$bs->connect();
foreach($list as $val){
    $res = $bs->useTube($val['tube']);
    $bs->watch($val['tube']);
    echo $res.PHP_EOL;
    $job= $bs->reserve();
    print_r($job);
    //$bs->delete($job['id']);
}

?>