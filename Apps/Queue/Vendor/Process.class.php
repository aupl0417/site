<?php
/**
+-------------------------------------------
| 创建子进程并处理定时任务
+-------------------------------------------
| Author: Lazycat <673090083@qq.com>
+-------------------------------------------
| 2016-10-28
+-------------------------------------------
 */
class Process
{
    public $task;   //任务内容，数组格式

    /**
     * 创建一个子进程
     * @param $task
     */
    public function create_process($id,$task)
    {
        $this->task = $task;
        $process = new swoole_process(array($this, "run"));
        if (!($pid = $process->start())) {

        }        

        //记录当前任务        
        Crontab::$task_list[$pid] = array(
            "start" => microtime(true),
            "id" => $id,
            "task" => $task,
            "type" => "crontab",
            "process" =>$process,
        );
        
    }

    /**
     * 子进程执行的入口
     * @param $worker
     */
    public function run($worker)
    {
        $class = $this->task["execute"];
        $worker->name(PROCESS_PREV . $class . "_" . $this->task["id"]);
        $this->autoload($class,$worker);
        $c = new $class;
        $c->worker = $worker;
        $c->run($this->task["args"]);
        $c->_exit();    //退出子进程，在子进程中不要出现exit()，否则整个将会退出整个父进程
        $this->_exit($worker);  //退出当前进程
    }

    //退出当前子进程
    private function _exit($worker)
    {
        $worker->exit(1);
    }

    /**
     * 子进程 自动载入需要运行的工作类
     * @param $class
     */
    private function autoload($class,$worker)
    {
        $file = ROOT_PATH . "Crontab" . DS . 'Task' . DS . $class . ".class.php";
        if (file_exists($file)) {
            include_once($file);
        } else {
            Main::log_write("处理类不存在");
            $this->_exit($worker);
        }
    }
}

