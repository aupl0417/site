<?php
/**
+-------------------------------------------
| Beanstalkd队列管理
+-------------------------------------------
| Author: Lazycat <673090083@qq.com>
+-------------------------------------------
| 2016-10-30
+-------------------------------------------
 */
class BeanstalkdJob
{
    private $bs;
    private $tube;
    public function __construct($tube){
        $this->bs = new Beanstalkd();
        if(!$this->bs->connect()){
            throw new Exception('连接Beanstalkd失败！');
        }

        //打开队列
        if(!$this->bs->useTube($tube)){
            throw new Exception('无法使用队列或队列不存在！');
        }

        $this->tube     = $tube;
    }

    /**
     * 加入队列
     * @param string $tube 队列名
     * @param array|string $data 入队列数据
     * @param int $param['pri'] 任务优先级（0~1024，数值越小优先级越高）
     * @param int $param['delay'] 延迟处理时间
     * @param int $param['ttr'] 充许任务执行的最长时间
     * 入列数据格式 array('execute' => '','args' => '')
     */
    public function put($data,$param=null){
        if(empty($data)) return false;
        $data           = is_array($data) ? serialize($data) : $data;
        $param['pri']   = isset($param['pri']) ? $param['pri'] : 0;
        $param['delay'] = isset($param['delay']) ? $param['delay'] : 0;
        $param['ttr']   = isset($param['ttr']) ? $param['ttr'] : 30;
        $this->bs->put($param['pri'],$param['delay'],$param['ttr'],$data);
    }

    /**
     * 批量入队
     */
    public function puts($data,$param=null){
        foreach($data as $val) {
            $this->put($val,$param);
        }
    }

    /**
     * 创建进程
     */
    public function create_process(){
        $process = new swoole_process(array($this, "run"));
        if (!($pid = $process->start())) {
            //创建进行失败
        }
    }

    /**
     * 执行任务
     */
    public function run($worker){
        //file_put_contents('/tmp/tmp/job_'.$worker->pid.'.txt',$worker->pid);
        //file_put_contents(JOB_PID_FILE,$worker->pid.',',FILE_APPEND);

        if(!$this->bs->connect()){  //创建进程需要重连beanstalkd
            $this->_exit($worker);
        }

        $res = $this->bs->useTube($this->tube);
        $this->bs->watch($this->tube);

        while(true) {
            $job = $this->bs->reserve();

            if(empty($job)){
                continue;
            }

            if($job['body'] == 'exit'){
                $this->bs->delete($job['id']);
                $this->_exit($worker);
                break;
            }

            //file_put_contents('/tmp/tmp/'.$job['id'].'.txt',var_export($job,true));
            $this->work($job,$worker);
        }

    }


    private function work($job,$worker){
        $item = unserialize(html_entity_decode($job['body']));
        //file_put_contents('/tmp/tmp/'.$job['id'].'.txt',var_export($item,true));
        $class = $item["execute"];
        $worker->name(PROCESS_PREV . 'beanstalkd_' . $class . "_" . $job["id"]);
        $this->autoload($class);
        $c = new $class;
        $c->worker = $worker;
        $res = $c->run($item["args"]);
        //file_put_contents('/tmp/tmp/'.$item['args']['type'].'_'.$item['args']['val'].'.txt',$res['code']);
        if($res['code'] == 1 || $res['code'] == 100){
            $this->bs->delete($job['id']);
        }else{
            $this->bs->delete($job['id']);

            //执行失败的任务最多只能再执行5次，且每次延迟5分钟执行
            if(!isset($item['execute_num'])) $item['execute_num'] = 1;
            if($item['execute_num'] < 5) {
                $item['execute_num']++;
                $this->put($item, ['delay' => (300 * $item['execute_num'])]);
            }
        }

        //由于队列处于监听状态，所以不要退出进程，可以入列时传入exit退出进程
        //$this->_exit($worker);  //退出当前进程
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
    private function autoload($class)
    {
        $file = ROOT_PATH . "Work" . DS . "Task" . DS . $class . ".class.php";
        if (file_exists($file)) {
            include_once($file);
        } else {
            //file_put_contents('/tmp/class.txt',$class);
            Main::log_write("处理类不存在");
            //$this->_exit($worker);    //不退出，等待下个任务
        }
    }

    /**
     * 析构方法，清除
     */
    public function __destruct(){
        $this->bs->disconnect();
    }
}

