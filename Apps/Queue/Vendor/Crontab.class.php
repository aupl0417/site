<?php
/**
+------------------------------------------
| 任务处理
+------------------------------------------
| Author: lazycat <673090083@qq.com>
+------------------------------------------
| 2016-10-28
+------------------------------------------
 */
class Crontab
{
    static public $process_name = "lazycat_task";//进程名称
    static public $pid_file;                    //pid文件位置
    static public $log_path;                    //日志文件位置
    static public $taskParams;                 //获取task任务参数
    static public $taskType;                    //获取task任务的类型
    static public $tasksHandle;                 //获取任务的句柄
    static public $daemon = false;              //运行模式
    static private $pid;                        //pid
    static public $checktime = false;           //精确对时
    static public $task_list = array();         //正在执行中的任务
    static public $unique_list = array();
    static public $worker = false;  //是否启用work
    //static public $delay = array();
    static public $worker_list = array();   //记录进程
    static public $workers =array();    //worker任务列表
    static public $tasks =array();  //定时任务列表

    //static public $listen_pid_file  = LISTEN_PID_FILE;   //监听队列进程PID
    //static public $worker_pid_file  = WORKER_PID_FILE;   //监听队列进程PID
    //static public $job_pid_file     = JOB_PID_FILE;   //监听队列进程PID


    //static public $db;
    //static public $bs;
    /**
     * 重启
     */
    static public function restart()
    {
        self::stop(false);
        sleep(1);
        self::start();
    }

    /**
     * 停止进程
     * @param bool $output
     */
    static public function stop($output = true)
    {
        $pid = @file_get_contents(self::$pid_file);
        if ($pid) {
            if (swoole_process::kill($pid, 0)) {
                swoole_process::kill($pid, SIGTERM);
                Main::log_write("进程" . $pid . "已结束");
            } else {
                @unlink(self::$pid_file);
                Main::log_write("进程" . $pid . "不存在,删除pid文件");
            }

            //清除子进程（当主进程出错时，可能无法将子进程正常退出，所以要清除旧的子进程）
            self::kill_process_item(LISTEN_PID_FILE);
            self::kill_process_item(WORKER_PID_FILE);

        } else {
            $output && Main::log_write("需要停止的进程未启动");
        }
    }

    static public function kill_process_item($pid_file){
        $pid = @file_get_contents($pid_file);
        if($pid) {
            $pid = explode(',', $pid);
            foreach ($pid as $val) {
                if ($val && swoole_process::kill($val, 0)) {
                    swoole_process::kill($val, SIGTERM);
                }
            }
        }
        @unlink($pid_file);
    }

    /**
     * 启动
     */
    static public function start()
    {
        if (file_exists(self::$pid_file)) {
            die("Pid文件已存在!\n");
        }
        self::daemon();
        self::set_process_name();
        self::run();
        Main::log_write("启动成功");
    }

    /**
     * 匹配运行模式
     */
    static private function daemon()
    {
        if (self::$daemon) {
            swoole_process::daemon();
        }
    }

    /**
     * 设置进程名
     */
    static private function set_process_name()
    {
        if (!function_exists("swoole_set_process_name")) {
            self::exit2p("Please install swoole extension.http://www.swoole.com/");
        }
        swoole_set_process_name(self::$process_name);
    }

    /**
     * 退出进程口
     * @param $msg
     */
    static private function exit2p($msg)
    {
        @unlink(self::$pid_file);
        Main::log_write($msg . "\n");
        exit();
    }

    /**
     * 运行
     */
    static protected function run()
    {
        self::$tasksHandle = new Tasks(self::$taskType);
        //self::$db = new db();
        //self::$bs = new Beanstalkd();

        self::register_signal();
        if (self::$checktime) {
            $run = true;
            Main::log_write("正在启动...");
            while ($run) {
                $s = date("s");
                if ($s == 0) {

                    Crontab::load_config();
                    self::register_timer();
                    $run = false;
                } else {
                    Main::log_write("启动倒计时 " . (60 - $s) . " 秒");
                    sleep(1);
                }
            }
        } else {
            self::load_config();
            self::register_timer();
        }
        self::get_pid();
        self::write_pid();
        //开启worker
        if (self::$worker) {
            $w = new Worker(self::$workers);
            $w->loadworker();
            //(new Trj())->start();
        }
    }

    /**
     * 过去当前进程的pid
     */
    static private function get_pid()
    {
        if (!function_exists("posix_getpid")) {
            self::exit2p("Please install posix extension.");
        }
        self::$pid = posix_getpid();
    }

    /**
     * 写入当前进程的pid到pid文件
     */
    static private function write_pid()
    {
        file_put_contents(self::$pid_file, self::$pid);
    }

    /**
     * 根据配置载入需要执行的任务
     */
    static public function load_config()
    {
        $time = time();
        $config = self::$tasksHandle->getTasks();
        //$config = self::getTasks();
        foreach ($config as $id => $task) {
            $ret = ParseCrontab::parse($task["rule"], $time);
            if ($ret === false) {
                Main::log_write(ParseCrontab::$error);
            } elseif (!empty($ret)) {
                TickTable::set_task($ret, array_merge($task, array("id" => $id)));
            }
        }

        self::$workers = self::$tasksHandle->getWorkers();
    }

    static public function getTasks(){
        $db = new db();
        $list =$db->table('swoole_crontab')->where(['status' => 1])->select();
        if($list){
            foreach($list as $key => $val){
                if(strstr($val['rule'],':')) $list[$key]['rule'] = eval(html_entity_decode($val['rule']));
                if($val['args'])	$list[$key]['args'] = eval(html_entity_decode($val['args']));
            }

            return $list;
        }
        return false;
    }

    /**
     *  注册定时任务
     */
    static protected function register_timer()
    {
        swoole_timer_tick(60000, function () {  //每一分钟读取一次任务列表
            Crontab::load_config();
        });
        swoole_timer_tick(1000, function ($interval) {
            Crontab::do_something($interval);
        });
    }

    /**
     * 运行任务
     * @param $interval
     * @return bool
     */
    static public function do_something($interval)
    {

        $tasks = TickTable::get_task();
        if (empty($tasks)) return false;
        foreach ($tasks as  $task) {
            if (isset($task["unique"]) && $task["unique"]) {
                if (isset(self::$unique_list[$task["id"]]) && (self::$unique_list[$task["id"]] >= $task["unique"])) {
                    continue;
                }
                self::$unique_list[$task["id"]] = isset(self::$unique_list[$task["id"]]) ? (self::$unique_list[$task["id"]] + 1) : 0;
            }
            (new Process())->create_process($task["id"], $task);
        }
        return true;
    }

    /**
     * 注册信号
     */
    static private function register_signal()
    {
        swoole_process::signal(SIGTERM, function ($signo) {
            self::exit2p("收到退出信号,退出主进程");
        });
        swoole_process::signal(SIGCHLD, function ($signo) {
            while ($ret = swoole_process::wait(false)) { //进程执行结束时返回$ret
                $pid = $ret['pid'];
                if (isset(self::$task_list[$pid])) {
                    $task = self::$task_list[$pid];
                    if ($task["type"] == "crontab") {
                        $end = microtime(true);
                        $start = $task["start"];
                        $id = $task["id"];
                        Main::log_write("{$id} [Runtime:" . sprintf("%0.6f", $end - $start) . "]");
                        $task["process"]->close();//关闭进程
                        unset(self::$task_list[$pid]);
                        if (isset(self::$unique_list[$id]) && self::$unique_list[$id] > 0) {
                            self::$unique_list[$id]--;
                        }
                    }
                    if ($task["type"] == "worker") {
                        $item = unserialize($task['item']);
                        $end = microtime(true);
                        $start = $task["start"];
                        $classname = $item["name"].'('.$item['execute'].')';
                        Main::log_write("{$classname}_{$task["id"]} [Runtime:" . sprintf("%0.6f", $end - $start) . "]");
                        $task["process"]->close();//关闭进程
                        //sleep(60);
                        //$w = new Worker(self::$workers);
                        //$w->create_process($item,$task['key']);
                        //(new Worker())->create_worker($item, $task["key"]);    //再次创建Worker进程
                    }
                }
            };
        });
        swoole_process::signal(SIGUSR1, function ($signo) {
            //TODO something
        });

    }
}