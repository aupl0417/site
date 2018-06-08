<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class AccountController extends CommonModulesController {
	protected $name 			='账户管理';	//控制器名称
    protected $formtpl_id		=71;			//表单模板ID
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
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a> <div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">充值</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
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
		$data=array(
				'ac_cash'		=>I('post.ac_cash'),
				'ac_score'		=>I('post.ac_score'),
				'ac_finance'	=>I('post.ac_finance'),
				'ac_cash_lock'	=>I('post.ac_cash_lock'),
			);

		$_POST['crc']=$this->crc($data);
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
		$data=array(
				'ac_cash'		=>I('post.ac_cash'),
				'ac_score'		=>I('post.ac_score'),
				'ac_finance'	=>I('post.ac_finance'),
				'ac_cash_lock'	=>I('post.ac_cash_lock'),
			);

		$_POST['crc']=$this->crc($data);
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
		$do=D('AccountRelation');
		$rs=$do->relation(true)->where(array('id'=>I('get.id')))->find();
		//dump($rs);

		$this->assign('rs',$rs);
		$this->display();
	}

	/** 
	* 保存审核记录
	*/
	public function logs_add(){

		$do=D('AccountRelation');
		$rs=$do->relation(true)->where(array('uid'=>I('post.uid')))->find();	

		$do->startTrans();
		
		$data=array(
				'ac_cash'		=>$rs['ac_cash'],
				'ac_score'		=>$rs['ac_score'],
				'ac_finance'	=>$rs['ac_finance'],
				'ac_cash_lock'	=>$rs['ac_cash_lock'],
			);
		
		$money=I('post.money');
		
		$flag=array('','ac_cash','ac_score','ac_finance','ac_cash_lock');
		$flag_model=array('','ChangeCash','ChangeScore','ChangeFinance','ChangeCashLock');
		$flag_no=array('','a_no','c_no','d_no','w_no');
		switch(I('post.type')){
			case 0:	//增加
				$data[$flag[I('post.flag')]]+=$money;
				
				//从admin转至用户
				$a_account=$this->check_account(1);
				$a_account['data'][$flag[I('post.flag')]]-=$money;
				
				//转出方异动
				$f_data=array();
				$f_data['uid']            =1;
				$f_data['money']          =$money * -1;
				$f_data[$flag_no[I('post.flag')]]           =$this->create_orderno();
				$f_data['status']         =2;     //状态
				$f_data['from_uid']       =1;     //1为系统账户
				$f_data['from_flag']      =I('post.flag');     //账户类型
				$f_data['from_account']   =$a_account['data'][$flag[I('post.flag')]];

				$f_data['to_uid']         =I('post.uid'); 
				$f_data['to_flag']        =I('post.flag');
				$f_data['to_account']     =$data[$flag[I('post.flag')]];

				$f_data['type_id']        =17;     //雇员充值
				$f_data['remark']		  =I('post.remark');

				$do->startTrans();
				if($sw1=D('Common/'.$flag_model[I('post.flag')])->create($f_data)){
					$sw1=D('Common/'.$flag_model[I('post.flag')])->add();
				}else{
					$msg[]=D('Common/'.$flag_model[I('post.flag')])->getError();
					goto error;
				}        
						

				//接收方异动 
				$t_data=$f_data;
				$t_data['uid']            =I('post.uid');
				$t_data['money']          =$money;
				$t_data[$flag_no[I('post.flag')]]           =$this->create_orderno();

				$do->startTrans();
				if($sw2=D('Common/'.$flag_model[I('post.flag')])->create($t_data)){
					$sw2=D('Common/'.$flag_model[I('post.flag')])->add();
				}else{
					$msg[]=D('Common/'.$flag_model[I('post.flag')])->getError();
					goto error;
				}	
				
				

				$a_account['crc']=$this->crc($a_account['data']);
				if(!$sw5=M('account')->where(array('uid'=>1))->save($a_account['data'])){
					goto error;
				}				
			break;
			case 1:	//减少
				$data[$flag[I('post.flag')]]-=$money;
				
				//从admin转至用户
				$a_account=$this->check_account(1);
				
				$a_account['data'][$flag[I('post.flag')]]+=$money;
				
				//转出方异动
				$f_data=array();
				$f_data['uid']            =I('post.uid');
				$f_data['money']          =$money * -1;
				$f_data[$flag_no[I('post.flag')]]           =$this->create_orderno();
				$f_data['status']         =2;     //状态
				$f_data['from_uid']       =I('post.uid');     //1为系统账户
				$f_data['from_flag']      =I('post.flag');     //账户类型
				$f_data['from_account']   =$data[$flag[I('post.flag')]];

				$f_data['to_uid']         =1; 
				$f_data['to_flag']        =I('post.flag');
				$f_data['to_account']     =$a_account['data'][$flag[I('post.flag')]];

				$f_data['type_id']        =18;     //雇员充值
				$f_data['remark']		  =I('post.remark');

				$do->startTrans();
				if($sw1=D('Common/'.$flag_model[I('post.flag')])->create($f_data)){
					$sw1=D('Common/'.$flag_model[I('post.flag')])->add();
				}else{
					$msg[]=D('Common/'.$flag_model[I('post.flag')])->getError();
					goto error;
				}        

				//接收方异动 
				$t_data=$f_data;
				$t_data['uid']            =1;
				$t_data['money']          =$money;
				$t_data[$flag_no[I('post.flag')]]           =$this->create_orderno();

				$do->startTrans();
				if($sw2=D('Common/'.$flag_model[I('post.flag')])->create($t_data)){
					$sw2=D('Common/'.$flag_model[I('post.flag')])->add();
				}else{
					$msg[]=D('Common/'.$flag_model[I('post.flag')])->getError();
					goto error;
				}
				
				$a_account['crc']=$this->crc($a_account['data']);
				if(!$sw5=M('account')->where(array('uid'=>1))->save($a_account['data'])){
					goto error;
				}				
			break;
			case 2:	//设置金额，不记录异动
				$data[$flag[I('post.flag')]]=$money;
			break;
		}

		$data['crc']=$this->crc($data);
		
		if(!$sw3=M('account')->where(array('uid'=>I('post.uid')))->save($data)){
			goto error;
		}		
		
		$do=D('AccountLogs');
		$_POST['a_uid']=session('admin.id');
		$_POST['account']=$data[$flag[I('post.flag')]];		
		if($sw4=$do->create()) $sw4=$do->add();
		else{
			$msg[]=$do->getError();
			goto error;
		}

		success:
			$do->commit();
			$result['status']='success';
			$result['msg']='操作成功！';
			$this->ajaxReturn($result);
		error:
			$do->rollback();
			$result['status']='warning';
			$result['msg']='操作失败！'.@implode('<br>',$msg);
			$this->ajaxReturn($result);

	}		

}