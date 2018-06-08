<?php
/**
+-------------------------------------------
| Beanstalkd 监听队列
+-------------------------------------------
| Author: Lazycat <673090083@qq.com>
+-------------------------------------------
| 2016-10-30
+-------------------------------------------
 */
class BeanstalkdListen
{
    private $bs;
    private $tube;

    public function __construct($tube){
        $this->bs = new Beanstalkd();
        if(!$this->bs->connect()){
            throw new Exception('连接Beanstalkd失败！');
        }

        $this->tube     = $tube;
        $this->tube['tube'] = TUBE_PREFIX . $this->tube['tube'];

    }

    //创建监听进徎
    public function create_listen(){
        $process = new swoole_process(array($this, "run_tube"));
        if (!($pid = $process->start())) {
            //创建进行失败
        }

    }

    //开启监听队列
    public function run_tube($worker){
        if(!$this->bs->connect()){  //创建进程需要重连beanstalkd
            $this->_exit($worker);
        }

        $worker->name(PROCESS_PREV . "_listen_" . $this->tube['tube']);
        if(! $watched = $this->bs->watch($this->tube['tube'])){
            $this->_exit($worker);
        }

        //file_put_contents('/tmp/listen_'.$worker->pid.'.txt',$worker->pid.'_'.$this->tube['tube']);
        file_put_contents(LISTEN_PID_FILE,$worker->pid.',',FILE_APPEND);

        //创建并发处理任务子进程
        $this->tube['worker_num'] = $this->tube['worker_num'] > 0 ? $this->tube['worker_num'] : 1;

        for($i=0;$i<$this->tube['worker_num'];$i++){
            $job = new BeanstalkdJob($this->tube['tube']);
            $job->create_process();
        }
    }

    //退出当前子进程
    private function _exit($worker)
    {
        $worker->exit(1);
    }

    /**
     * 析构方法，清除
     */
    public function __destruct(){
        $this->bs->disconnect();
    }
}

