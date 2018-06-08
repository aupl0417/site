<?php
namespace Admin\Controller;
use Think\Controller;
class ClearCacheController extends CommonController {
    public function index(){
        /*
        if(C('DATA_CACHE_TYPE') == 'Memcached') {
            C('MEMCACHED_HOST', '10.0.0.92');
            C('MEMCACHED_PORT', 1200);
            $m = new \Think\Cache\Driver\Memcached();
            $this->assign('keys', $m->getAllKeys());
        }
        */
		$this->display();
    }
	
	
	public function clear(){
		set_time_limit(300);
        if(C('DATA_CACHE_TYPE') == 'Memcached') {
            //集群时getAllKeys函数无效，只能单独连接到后备那台读取key
            S(null);
            C('MEMCACHED_HOST', '10.0.30.50');
            C('MEMCACHED_PORT', 1400);
            $m = new \Think\Cache\Driver\Memcached();
            $keys = $m->getAllKeys();

            foreach ($keys as $val) {
                $val = substr($val, strlen(C('DATA_CACHE_PREFIX')));
                S($val, null);
            }
        }else {
            S(null);
            //$dir=new \Org\Util\Dir();
            //$dir->delDir('./Runtime');
        }
		
		$this->ajaxReturn(array('status'=>'success','msg'=>'清除成功！'));
	}


    public function clear2(){
        set_time_limit(300);
        S(null);
        //$dir=new \Org\Util\Dir();
        //$dir->delDir('./Runtime');
        $this->ajaxReturn(array('status'=>'success','msg'=>'清除成功！'));
    }
}