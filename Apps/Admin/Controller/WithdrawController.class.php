<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class WithdrawController extends CommonModulesController {
	protected $name 			='提现管理';	//控制器名称
    protected $formtpl_id		=83;			//表单模板ID
    protected $fcfg				=array();		//表单配置
    protected $do 				='';			//实例化模型
    protected $map				=array();		//where查询条件

    /**
    * 初始化
    */
    public function _initialize() {
    	parent::_initialize();

    	//初始化表单模板
    	$this->_initform();

    	//dump($this->fcfg);

    }

    /**
    * 列表
    */
    public function index($param=null){
    	$this->_index();
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a> <div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">审核</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
    	$this->assign('fields',$this->plist(null,$btn));        	
		$this->display();
    }

    /**
    * 添加记录
    */
    public function add($param=null){
    	$this->display();
    }
	
	/**
	* 保存新增记录
	*/
	public function add_save($param=null){
		$result=$this->_add_save();

		$this->ajaxReturn($result);
	}

	/**
	* 修改记录
	*/
	public function edit($param=null){
		$this->_edit();
		$this->display();
	}

	/**
	* 保存修改记录
	*/
	public function edit_save($param=null){
		$result=$this->_edit_save();

		$this->ajaxReturn($result);
	}

	/**
	* 删除选中记录
	*/
	public function delete_select($param=null){
		$result=$this->_delete_select();
		$this->ajaxReturn($result);
	}

	/**
	* 批量更改状态
	*/
	public function active_change_select($param=null){
		$result=$this->_active_change_select();

		$this->ajaxReturn($result);		
	}

	/**
	* 详情
	*/
	public function view(){
		$do=D('WithdrawRelation');
		$rs=$do->relation(true)->where(array('id'=>I('get.id')))->find();
		//dump($rs);

		$this->assign('rs',$rs);
		$this->display();
	}

	/** 
	* 保存审核记录
	*/
	public function logs_add(){
		//检查是否做过处理，如果已处理过，不充许再次处理
		$do=M('withdraw_logs');
		if($do->where(array('status'=>array('gt',0),'w_id'=>I('post.w_id')))->find()){
			$this->ajaxReturn(array('status'=>'warning','msg'=>'该笔记录已处理过，不可再次处理！'));
		}

		if(I('post.status')==2 && I('post.reason')==''){
			$this->ajaxReturn(array('status'=>'warning','msg'=>'请输入驳回原因！'));
		}

		$do=D('WithdrawRelation');
		$rs=$do->relation(true)->where(array('id'=>I('post.w_id')))->find();

		$_POST['a_uid']=session('admin.id');
		$do->startTrans();
		if($sw1=D('WithdrawLogs')->create()) $sw1=D('WithdrawLogs')->add();
		else{
			$msg[]=D('WithdrawLogs')->getError();
			goto error;
		}

		if($rs['status']!=I('post.status')){
			if(!$sw2=M('withdraw')->where(array('id'=>I('post.w_id')))->save(array('status'=>I('post.status')))) goto error;
		}


		//资金异动
		$money=$rs['money'];
		if(I('post.status')==1){ //提现到账
			$to_account=M('account')->where(array('uid'=>1))->field('ac_cash,ac_score,ac_finance,ac_cash_lock')->find();
			$from_account=M('account')->where(array('uid'=>$rs['uid']))->field('ac_cash,ac_score,ac_finance,ac_cash_lock')->find();
			if($from_account['ac_cash_lock']<$rs['money']){
				$msg[]='冻结账户有异常！';
				goto error;
			}

	        $from_account['ac_cash_lock']       -=$money;
	        $to_account['ac_cash_lock']         +=$money;     
	        $from_account['crc']            	=$this->crc($from_account);   
	        $to_account['crc']              	=$this->crc($to_account);

	        //转出方异动
	        $data=array();
	        $data['uid']            =$rs['uid'];
	        $data['money']          =$money * -1;
	        $data['w_no']           =$this->create_orderno();
	        $data['status']         =2;     //状态
	        $data['from_uid']       =$rs['uid'];     //1为系统账户
	        $data['from_flag']      =4;     //积分账户
	        $data['from_account']   =$from_account['ac_cash_lock'];

	        $data['to_uid']         =1; 
	        $data['to_flag']        =4;
	        $data['to_account']     =$to_account['ac_cash_lock'];

	        $data['type_id']        =15;     //提现到账
	        $data['ordersno']		=$rs['w_no'];

	        if($sw3=D('Common/ChangeCashLock')->create($data)){
	            $sw3=D('Common/ChangeCashLock')->add();
	        }else{
	            $msg[]=D('Common/ChangeCashLock')->getError();
	            goto error;
	        }        

	        //接收方异动 
	        $to_data=$data;
	        $to_data['uid']            =1;
	        $to_data['money']          =$money;
	        $to_data['w_no']           =$this->create_orderno();

	        $do->startTrans();
	        if($sw4=D('Common/ChangeCashLock')->create($to_data)){
	            $sw4=D('Common/ChangeCashLock')->add();
	        }else{
	            $msg[]=D('Common/ChangeCashLock')->getError();
	            goto error;
	        }
	        
	        if(!$sw5=M('account')->where('uid='.$data['from_uid'])->save($from_account)) goto error;        
	        if(!$sw6=M('account')->where('uid='.$data['to_uid'])->save($to_account)) goto error;
		}elseif(I('post.status')==2){	//驳回
			
			$from_account=M('account')->where(array('uid'=>$rs['uid']))->field('ac_cash,ac_score,ac_finance,ac_cash_lock')->find();
			if($from_account['ac_cash_lock']<$rs['money']){
				$msg[]='冻结账户有异常！';
				goto error;
			}


	        $from_account['ac_cash_lock']       -=$money;
	        $from_account['ac_cash']         	+=$money;     
	        $from_account['crc']            	=$this->crc($from_account);   

	        //转出方异动
	        $data=array();
	        $data['uid']            =$rs['uid'];
	        $data['money']          =$money * -1;
	        $data['w_no']           =$this->create_orderno();
	        $data['status']         =2;     //状态
	        $data['from_uid']       =$rs['uid'];     //1为系统账户
	        $data['from_flag']      =4;     //积分账户
	        $data['from_account']   =$from_account['ac_cash_lock'];

	        $data['to_uid']         =$rs['uid']; 
	        $data['to_flag']        =1;
	        $data['to_account']     =$from_account['ac_cash'];

	        $data['type_id']        =12;     //提现驳回
	        $data['ordersno']		=$rs['w_no'];

	        if($sw3=D('Common/ChangeCashLock')->create($data)){
	            $sw3=D('Common/ChangeCashLock')->add();
	        }else{
	            $msg[]=D('Common/ChangeCashLock')->getError();
	            goto error;
	        }        

	        //接收方异动 
	        $to_data=$data;
	        $to_data['uid']            =$rs['uid'];
	        $to_data['money']          =$money;
	        $to_data['a_no']           =$this->create_orderno();

	        $do->startTrans();
	        if($sw4=D('Common/ChangeCash')->create($to_data)){
	            $sw4=D('Common/ChangeCash')->add();
	        }else{
	            $msg[]=D('Common/ChangeCash')->getError();
	            goto error;
	        }
	        
	        if(!$sw5=M('account')->where('uid='.$data['from_uid'])->save($from_account)) goto error;   		
		}

		
		success:
			$do->commit();
			$result['status']='success';
			$result['msg']='操作成功！';
			$this->ajaxReturn($result);
			
		error:
			$msg[]=$v;
			$do->rollback();
			$result['status']='warning';
			$result['msg']='操作失败！'.@implode('<br>',$msg);
			$this->ajaxReturn($result);

	}		
}