<?php
/**
+-------------------------------------------
| 读取任务
+-------------------------------------------
| Author: Lazycat <673090083@qq.com
+-------------------------------------------
| 2016-10-28
+-------------------------------------------
 */
class Tasks
{

    private $db;
    private $tasks;
    private $workers;
    public function __construct($type = 'mysql'){
        //$this->db = new db();
        //$this->db = new MyDB();
    }

    /**
     * 读取定时任务
     */
    public function getTasks(){
        //$this->db = new db();
        //$list = $this->db->table('swoole_crontab')->where(['status' => 1])->select();
        $this->db = new MyDB();
        $list = $this->db->get_all('select * from ylh_swoole_crontab where status=1');
        if($list){
            foreach($list as $key => $val){
                if(strstr($val['rule'],':')) $list[$key]['rule'] = eval(html_entity_decode($val['rule']));
                if($val['args'])	$list[$key]['args'] = eval(html_entity_decode($val['args']));
            }
            $this->tasks = $list;
        }
        return $this->tasks;
    }

    /**
     * 获取Worker任务
     */
    public function getWorkers(){
        //$this->db = new db();
        //$this->workers = $this->db->table('swoole_worker')->where(['status' => 1])->select();
        $this->db = new MyDB();
        $this->workers = $this->db->get_all('select * from ylh_swoole_worker where status=1');
        return $this->workers;
    }
    /**
     * 析构方法，清除
     */
    public function __destruct(){
        //$this->db->close();
    }

}

