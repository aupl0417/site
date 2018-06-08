<?php
/**
 * -------------------------------------------
 * 执行Worker任务
 * -------------------------------------------
 * Author: Lazycat <673090083@qq.com>
 * -------------------------------------------
 * 2016-10-28
 * -------------------------------------------
 */
class Worker
{
    private $workers;
    private $item;

    public function __construct($workers){
        $this->workers = $workers;
        if(empty($this->workers)){
            throw new Exception('无Worker可执行！');
        }
    }

    public function loadworker(){
        foreach($this->workers as $key => $val){
            $this->create_process($val,$key);
        }
    }

    /**
     * 创建一个子进程
     * @param $classname
     * @param $number
     * @param $redis
     */
    public function create_process($item,$key)
    {
        $this->item = $item;
        $process = new swoole_process(array($this, "run"));
        if (!($pid = $process->start())) {

        }
        //记录当前任务
        Crontab::$task_list[$pid] = array(
            "start"     => microtime(true),
            "item"      => serialize($item),
            "key"       => $key,
            "id"        => $item['id'],
            "type"      => "worker",
            "process"   =>$process
        );
    }

    /**
     * 子进程执行的入口
     * @param $worker
     */
    public function run($worker)
    {
        //file_put_contents('/tmp/worker_'.$worker->pid.'.txt',$worker->pid);
        file_put_contents(WORKER_PID_FILE,$worker->pid.',',FILE_APPEND);

        if(!empty($this->item['args'])) $this->item['args'] = eval(html_entity_decode($this->item['args']));
        $class = $this->item['execute'];
        $worker->name(PROCESS_PREV . 'worker_' . $class . "_" . $this->item['id']);

        //$this->_exit($worker);

        $this->autoload($class,$worker);
        $w = new $class;
        $w->pworker = $worker;
        $w->start($this->item['id'],$this->item["args"]);

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
    public function autoload($class,$worker)
    {
        $file = ROOT_PATH . "Work" . DS . $class . ".class.php";
        if (file_exists($file)) {
            include_once($file);
        } else {
            Main::log_write("处理类不存在");
            $this->_exit($worker);
        }
    }

    /**
     * 析构方法，清除
     */
    public function __destruct(){
        //$this->db->close();
    }


}