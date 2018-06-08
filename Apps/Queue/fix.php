<?php
@ini_set('memory_limit','2048M');   //最大占用内存1024M，启用进程越多，所需内存就越大
//@ini_set('display_errors','off');   //屏弊错误信息

date_default_timezone_set('Asia/Shanghai');
define('APP_DEBUG', true);
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', realpath(dirname(__FILE__)) . DS);
define('COMMON_PATH',ROOT_PATH . 'Common' . DS);
define('CONFIG_PATH',COMMON_PATH . 'Config' . DS);
define('FUNCTION_PATH',COMMON_PATH . 'Common' . DS);
define('VENDOR_PATH',ROOT_PATH . 'Vendor' . DS);
define('TUBE_PREFIX','trj_');

include_once FUNCTION_PATH . 'function.php';
$config = array(
    //Beanstalkd参数
    'BS_HOST'       => '10.0.0.90',
    'BS_PORT'       => 11300,
    'BS_TIMEOUT'    => 1,
    'BS_LOGGER'     => null,

    //数据库配置
    'DB_TYPE'       => 'mysql',
    'DB_HOST'       => '10.0.0.100',
    'DB_USER'       => 'tangmalluser',
    'DB_PWD'        => 'Ma#-26Tang',
    'DB_PORT'       => '3306',
    'DB_NAME'       => 'tangmall',
    'DB_CHARSET'    => 'utf8',
    'DB_PREFIX'     => 'ylh_',
    'DB_PCONNECT'   => 0,

    'PROCESS_PREV'  => 'lazycat_',   //进程名前缀
    'APIURL'        => 'https://rest.trj.cc', //API接口地址前缀

    'LISTEN_PID_FILE'   => ROOT_PATH . '/listen_tube.pid', //监听队列进程PID
    'WORKER_PID_FILE'   => ROOT_PATH . '/worker.pid', //监听队列进程PID
    //'JOB_PID_FILE'      => ROOT_PATH . '/job.pid', //监听队列进程PID

    'XS_PROJECT_PATH'   => ROOT_PATH . 'Config/Xunsearch', //迅搜项目文件.ini存放目录
);


foreach($config as $key => $val){
    define($key,$val);
}


        spl_autoload_register(function ($name) {
            $file_path = ROOT_PATH . "Vendor" . DS . $name . ".class.php";
            if(file_exists($file_path)) include $file_path;
        });


$trj = new TrjInQueue(['days' => 100]);
$list = $trj->in_queue_days();


?>