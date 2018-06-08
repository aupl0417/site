<?php
namespace Admin\Controller;
use Think\Controller;
class LogsController extends CommonController {
    //雇员操作日志
    public function index(){
    	if(I('get.type')) $map['type']=I('get.type');
    	if(I('get.name')) $map['name']=I('get.name');
    	if(I('get.q')) $map[I('get.field')]=array('like',trim(I('get.q')));

    	if(I('get.sday') && I('get.eday')) $map['atime']=array('between',array(I('get.sday'),I('get.eday')));
    	else if(I('get.sday')=='' && I('get.eday')) $map['atime']=array('lt',I('get.eday'));
    	else if(I('get.sday') && I('get.eday')=='') $map['atime']=array('gt',I('get.sday'));

    	$pagelist=pagelist(array(
    		'table'		=>'admin',
    		'pagesize'	=>50,
    		'is_mongo'	=>1,
    		'fields'	=>'_id,atime,ip,type,name,table,insid,res,url',
            'order'     =>'_id desc',
    		'map'		=>$map
    	));

    	//dump($pagelist['sql']);

    	$this->assign('pagelist',$pagelist);

    	$this->display();
    }

    //日志详情
    public function view(){
    	$do=new \Think\Model\MongoModel(C('DB_MONGO_CONFIG.DB_PREFIX').'admin',null,C('DB_MONGO_CONFIG'));
    	$rs=$do->find(I('get.id'));

    	$this->assign('rs',$rs);
    	$this->display();
    }


    //api接口日志
    public function api_logs(){
        if(I('get.name')) $map['name']=I('get.name');
        if(I('get.q')) $map[I('get.field')]=array('like',trim(I('get.q')));
        if(I('get.sday') && I('get.eday')) $map['atime']=array('between',array(I('get.sday'),I('get.eday')));
        else if(I('get.sday')=='' && I('get.eday')) $map['atime']=array('lt',I('get.eday'));
        else if(I('get.sday') && I('get.eday')=='') $map['atime']=array('gt',I('get.sday'));
		
		//表格名字选择
		if(I('get.table_name')){
			$table_name = I('get.table_name');
		}else{
			$table_name = 'api_';
		}
		//月份选择
		if(I('get.month')){
			$month = $table_name.I('get.month');
		}else{
			$month = $table_name.date('Ym');
		}
        $pagelist=pagelist(array(
            'table'     =>$month,
            'pagesize'  =>50,
            'is_mongo'  =>1,
            'fields'    =>'_id,ip,nick,url,apiurl,atime,dotime,code',
            'order'     =>'_id desc',
            'map'       =>$map
        ));
		$_SESSION['admin']['month'] = $month;
		//dump($_SESSION['admin']['month']);
		$year = date('Y');
		$month1 = date('m');
		$change_option;
		$key = 0;
		if($month1<10){
			$month1 = substr($month1,1,1);
			if($month1>6){
				$key = $month1-6;
			}
			for($i = $month1;$i > $key; $i--){
				if($year."0".$i == I('get.month')){
					$change_option .= "<option value='".$year."0".$i."' selected='selected'>".$year."0".$i."</option>";
				}else{
					$change_option .= "<option value='".$year."0".$i."'>".$year."0".$i."</option>";
				}
			}
		}else{
			$key = $month1-6;
			for($i = $month1;$i > $key; $i--){
				if($i<10){
					if($year."0".$i == I('get.month')){
						$change_option .= "<option value='".$year."0".$i."' selected='selected'>".$year."0".$i."</option>";
					}else{
						$change_option .= "<option value='".$year."0".$i."'>".$year."0".$i."</option>";
					}
				}else{
					if($year."0".$i == I('get.month')){
						$change_option .= "<option value='".$year.$i."' selected='selected'>".$year.$i."</option>";
					}else{
						$change_option .= "<option value='".$year.$i."'>".$year.$i."</option>";
					}
				}
			}
		}
        $this->assign('change_option',$change_option);
        $this->assign('pagelist',$pagelist);
        $this->display();
    }

    //api接口日志详情
    public function api_logs_view(){
        $do=new \Think\Model\MongoModel(C('DB_MONGO_CONFIG.DB_PREFIX').$_SESSION['admin']['month'],null,C('DB_MONGO_CONFIG'));
        $rs=$do->find(I('get.id'));

        $this->assign('rs',$rs);
        $this->display();
    }

    //ERP接口日志
    public function erp(){
    	if(I('get.name')) $map['name']=I('get.name');
    	if(I('get.q')) $map[I('get.field')]=array('like',trim(I('get.q')));
    	if(I('get.sday') && I('get.eday')) $map['atime']=array('between',array(I('get.sday'),I('get.eday')));
    	else if(I('get.sday')=='' && I('get.eday')) $map['atime']=array('lt',I('get.eday'));
    	else if(I('get.sday') && I('get.eday')=='') $map['atime']=array('gt',I('get.sday'));

		//表格名字选择
		if(I('get.table_name')){
			$table_name = I('get.table_name');
		}else{
			$table_name = 'erp_';
		}
		//月份选择
		if(I('get.month')){
			$month = $table_name.I('get.month');
		}else{
			$month = $table_name.date('Ym');
		}
    	$pagelist=pagelist(array(
    		'table'		=>$month,
    		'pagesize'	=>50,
    		'is_mongo'	=>1,
    		'fields'	=>'_id,ip,nick,url,apiurl,atime,dotime',
            'order'     =>'_id desc',
    		'map'		=>$map
    	));
		//查询详情要用的
		$_SESSION['admin']['month'] = $month;
		
		$year = date('Y');
		$month1 = date('m');
		$change_option;
		$key = 0;
		if($month1<10){
			$month1 = substr($month1,1,1);
			if($month1>6){
				$key = $month1-6;
			}
			for($i = $month1;$i > $key; $i--){
				if($year."0".$i == I('get.month')){
					$change_option .= "<option value='".$year."0".$i."' selected='selected'>".$year."0".$i."</option>";
				}else{
					$change_option .= "<option value='".$year."0".$i."'>".$year."0".$i."</option>";
				}
			}
		}else{
			$key = $month1-6;
			for($i = $month1;$i > $key; $i--){
				if($i<10){
					if($year."0".$i == I('get.month')){
						$change_option .= "<option value='".$year."0".$i."' selected='selected'>".$year."0".$i."</option>";
					}else{
						$change_option .= "<option value='".$year."0".$i."'>".$year."0".$i."</option>";
					}
				}else{
					if($year."0".$i == I('get.month')){
						$change_option .= "<option value='".$year.$i."' selected='selected'>".$year.$i."</option>";
					}else{
						$change_option .= "<option value='".$year.$i."'>".$year.$i."</option>";
					}
				}
			}
		}
        $this->assign('change_option',$change_option);
    	$this->assign('pagelist',$pagelist);
    	$this->display();
    }

    //ERP接口日志详情
    public function erp_view(){
    	$do=new \Think\Model\MongoModel(C('DB_MONGO_CONFIG.DB_PREFIX').$_SESSION['admin']['month'],null,C('DB_MONGO_CONFIG'));
    	$rs=$do->find(I('get.id'));

    	$this->assign('rs',$rs);
    	$this->display();
    }


    //ERP异步支付接口日志
    public function erp_pays(){
    	if(I('get.s_no')) $map['s_no']=I('get.s_no');
    	if(I('get.q')) $map[I('get.field')]=array('like',trim(I('get.q')));
    	if(I('get.sday') && I('get.eday')) $map['atime']=array('between',array(I('get.sday'),I('get.eday')));
    	else if(I('get.sday')=='' && I('get.eday')) $map['atime']=array('lt',I('get.eday'));
    	else if(I('get.sday') && I('get.eday')=='') $map['atime']=array('gt',I('get.sday'));

		//表格名字选择
		$table_name = 'erp_pays_notify_success';
		if(I('get.table_name')){
			$table_name = I('get.table_name');
		}

    	$pagelist=pagelist(array(
    		'table'		=>$table_name,
    		'pagesize'	=>50,
    		'is_mongo'	=>1,
    		'fields'	=>'_id,is_fix,ip,group_no,s_no,msg,atime,dotime',
            'order'     =>'_id desc',
    		'map'		=>$map
    	));
		
		//查询详情要用的
		$_SESSION['admin']['table_name'] = $table_name;

    	$this->assign('pagelist',$pagelist);
		
        $this->display();
    }

    //ERP异步支付接口日志详情
    public function erp_pays_view(){
        $do=new \Think\Model\MongoModel(C('DB_MONGO_CONFIG.DB_PREFIX').$_SESSION['admin']['table_name'],null,C('DB_MONGO_CONFIG'));
        $rs=$do->find(I('get.id'));

        $this->assign('rs',$rs);
        $this->display();
    }

    //定时任务日志
    public function timed_task(){
    	if(I('get.val')) $map['val']=I('get.val');
    	if(I('get.q')) $map[I('get.field')]=array('like',trim(I('get.q')));
    	if(I('get.sday') && I('get.eday')) $map['atime']=array('between',array(I('get.sday'),I('get.eday')));
    	else if(I('get.sday')=='' && I('get.eday')) $map['atime']=array('lt',I('get.eday'));
    	else if(I('get.sday') && I('get.eday')=='') $map['atime']=array('gt',I('get.sday'));

		//表格名字选择
		$table_name = 'cron_orders';
		if(I('get.table_name')){
			$table_name = I('get.table_name');
		}

    	$pagelist=pagelist(array(
    		'table'		=>$table_name,
    		'pagesize'	=>50,
    		'is_mongo'	=>1,
    		'fields'	=>'_id,code,val,method,job_name,method,msg,atime,dotime',
            'order'     =>'_id desc',
    		'map'		=>$map
    	));
		//查询详情要用的
		$_SESSION['admin']['table_name'] = $table_name;

    	$this->assign('pagelist',$pagelist);
		
        $this->display();
    }

    //定时任务日志详情
    public function timed_task_view(){
        $do=new \Think\Model\MongoModel(C('DB_MONGO_CONFIG.DB_PREFIX').$_SESSION['admin']['table_name'],null,C('DB_MONGO_CONFIG'));
        $rs=$do->find(I('get.id'));

        $this->assign('rs',$rs);
        $this->display();
    }
	
    //订单修复日志
    public function orders_fix(){
    	if(I('get.s_no')) $map['s_no']=I('get.s_no');
    	if(I('get.q')) $map[I('get.field')]=array('like',trim(I('get.q')));
    	if(I('get.sday') && I('get.eday')) $map['atime']=array('between',array(I('get.sday'),I('get.eday')));
    	else if(I('get.sday')=='' && I('get.eday')) $map['atime']=array('lt',I('get.eday'));
    	else if(I('get.sday') && I('get.eday')=='') $map['atime']=array('gt',I('get.sday'));

		//表格名字选择
		$table_name = 'orders_fix';
		if(I('get.table_name')){
			$table_name = I('get.table_name');
		}

    	$pagelist=pagelist(array(
    		'table'		=>$table_name,
    		'pagesize'	=>50,
    		'is_mongo'	=>1,
    		'fields'	=>'_id,s_no,subject,status,fix_status,atime,dotime',
            'order'     =>'_id desc',
    		'map'		=>$map
    	));
		
		foreach($pagelist['list'] as $key=>$val){
			$pagelist['list'][$key]['atime'] = date('Y-m-d H:i:s', $pagelist['list'][$key]['atime']);
		}
		//查询详情要用的
		$_SESSION['admin']['table_name'] = $table_name;
    	$this->assign('pagelist',$pagelist);
		
        $this->display();
    }

    //订单修复日志详情
    public function orders_fix_view(){
        $do=new \Think\Model\MongoModel(C('DB_MONGO_CONFIG.DB_PREFIX').$_SESSION['admin']['table_name'],null,C('DB_MONGO_CONFIG'));
        $rs=$do->find(I('get.id'));
		$rs['atime'] = date('Y-m-d H:i:s', $rs['atime']);
        $this->assign('rs',$rs);
        $this->display();
    }
}