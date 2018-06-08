<?php
date_default_timezone_set('Asia/Shanghai');
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', '/home/script/TrjQueue');
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
    'DB_PCONNECT'   => 0,

    'PROCESS_PREV'  => 'lazycat_',   //进程名前缀
    'APIURL'        => 'https://rest.trj.cc', //API接口地址前缀

    'LISTEN_PID_FILE'   => ROOT_PATH . '/listen_tube.pid', //监听队列进程PID
    'WORKER_PID_FILE'   => ROOT_PATH . '/worker.pid', //监听队列进程PID
    'PID_FILE'          => ROOT_PATH . '/pid', //监听队列进程PID
);


foreach($config as $key => $val){
    define($key,$val);
}

function pid_status($pid_file)
{
    $pid = @file_get_contents($pid_file);
    if ($pid) {
        $arr = array();
        $pid = explode(',', $pid);
        foreach ($pid as $val) {
            if ($val && swoole_process::kill($val, 0)) {
                $arr[$val] = 1;
            }else $arr[$val] = 0;
        }

        return $arr;
    }

    return false;
}

$status['master'] = 0;
$pid = @file_get_contents(PID_FILE);
if ($pid) {
	/*
    if (swoole_process::kill($pid, 0)) {
        $status['master'] = 1;
    }else{
        $status['master'] = 0;
    }
	*/
	if(file_exists('/proc/'.$pid)) $status['master'] = 1;
}

//$status['worker']['master'] = pid_status(WORKER_PID_FILE);
//$status['worker']['listen'] = pid_status(LISTEN_PID_FILE);

//print_r($status);

echo json_encode($status);

?>