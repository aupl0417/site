<?php
/**
+-------------------------------------------
| 唐人街队列任务
+-------------------------------------------
| Author: Lazycat <673090083@qq.com>
+-------------------------------------------
| 2016-10-30
+-------------------------------------------
 */
class Trj
{
    private $db;
    private $tubes;
    public $pworker;    //运行该进程的句柄，从上级赋值

    public function __construct(){
        $this->db = new MyDB();
        //$db = new db();
        //$this->tubes = $this->db->table('swoole_queue')->where(['status' => 1])->select();

    }

    /**
     * 开启work
     * @param int $worker_id worker ID
     * @param array $args 相关参数
     */
    public function start($worker_id,$args = null){
        $this->tubes = $this->db->get_all('select * from ylh_swoole_queue where status=1 and worker_id='.$worker_id);
        //$this->bubes = $this->db->table('swoole')->where(['status' => 1,'worker_id' => $worker_id])->select();

        if(empty($this->tubes)){
            throw new Exception('暂无队列可执行！');
            $this->_exit($this->pworker);
        }

        foreach($this->tubes as $val){
            $listen = new BeanstalkdListen($val);   //创建监听进程
            $listen->create_listen();
        }
    }

    //退出当前子进程
    private function _exit($worker)
    {
        $worker->exit(1);
    }


}

