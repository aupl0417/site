<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class OrdesappealController extends CommonModulesController {
	protected $name 			='卖家刷单申诉';	//控制器名称
    protected $formtpl_id		=222;			//表单模板ID
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
     *审核页面
     * @param int $_GET['id']	ID
     */
    public function view(){
		$map['id'] = I('get.id');
		$pagelist=pagelist(array(
			'do'		=>$this->fcfg['do'],
			'table'		=>$this->fcfg['modelname'],
			'pagesize'	=>$this->fcfg['pagesize'],
			'order'		=>$this->fcfg['order'],
			'fields'	=>$this->fcfg['fields'],
			'relation'	=>$this->fcfg['action_type']==2?true:'',
			'map'		=>$map,
		));
//		dump($pagelist['list']['0']);
        if($pagelist['images']){
			$img = explode(',',$rs['images']);
			$pagelist['images'] = $img;
		}

        $this->assign('rs',$pagelist['list']['0']);
        $this->display();
    }
    /**
     * 卖家刷单申诉审核
     * @param int $_POST['id']	ID
     */
    public function add_appeal(){
        $do=M('order_apply');
        $data = I('post.');
        $result = $do->where(['id'=> $data['id']])->find();

        if ($result['status'] ==2 || $result['status'] ==0){
            $this->ajaxReturn(['status' => 'warning','msg' =>'该申诉已经审核过了，不能再次审核！']);
        }
        if(!isset($data['status']) || $data['status'] == ""){
            $this->ajaxReturn(['status' => 'warning','msg' =>'请选择审核结果！']);
        }
        if($data['status']==2){
			if(!$data['c_id']){
				 $this->ajaxReturn(['status' => 'warning','msg' =>'操作失败！']);
			}
			$update['id']       = I('post.id');
			// $update['reason']   = I('post.reason');
			$update['status']   = I('post.status');
			$update['dotime']   = date('Y-m-d H:i:s',time());
			$update['admin_id'] = $_SESSION['admin']['id'];
			
			$log['atime']       = date('Y-m-d H:i:s',time());
			$log['c_id']        = $data['c_id'];
			$log['status']      = I('post.status');
			$log['apply_id']    = $data['id'];
			$log['reason']      = I('post.reason');
			$log['a_uid']       = $_SESSION['admin']['id'];	
			$log['uid']         = $result['seller_id'];
			$log['reason']      = I('post.reason');		
			$log['username']    = $_SESSION['admin']['username'];
			$log['remark']      = "审核通过";
	
			$res['is_shuadan']  = 0;
			$res['id']          = $data['c_id'];
			
			$do->startTrans();
			
			$result = $do->save($update);
			$re_logs = M("orders_apply_logs")->add($log);
			$rs = M("orders_goods_comment")->save($res);
			if ($result && $rs && $re_logs){
				$do->commit();
				$this->ajaxReturn(['status' => 'success','msg' =>'操作成功！']);
			}else{
				 $do->rollback();
				$this->ajaxReturn(['status' => 'warning','msg' =>'操作失败！']);
			}
        }else{
            if ($data['reason'] == ""){
                $this->ajaxReturn(['status' => 'warning','msg' =>'请填写拒绝理由']);
            }
			if(!$data['c_id']){
				 $this->ajaxReturn(['status' => 'warning','msg' =>'操作失败！']);
			}
			$update['id']       = I('post.id');
			// $update['reason']   = I('post.reason');
			$update['status']   = I('post.status');
			$update['dotime']   = date('Y-m-d H:i:s',time());
			$update['admin_id'] = $_SESSION['admin']['id'];
			
			$log['atime']       = date('Y-m-d H:i:s',time());
			$log['c_id']        = $data['c_id'];
			$log['status']      = I('post.status');
			$log['apply_id']    = $data['id'];
			$log['reason']      = I('post.reason');
			$log['a_uid']       = $_SESSION['admin']['id'];	
			$log['uid']         = $result['seller_id'];
			$log['reason']      = I('post.reason');	
			$log['username']    = $_SESSION['admin']['username'];
			$log['remark']      = "审核不通过";
			
			$res['is_shuadan']  = 1;
			$res['id']          = $data['c_id'];
			
			$do->startTrans();
			$result = $do->save($update);
			$re_logs = M("orders_apply_logs")->add($log);
			$rs = M("orders_goods_comment")->save($res);
			if ($result && $rs && $re_logs){
				$do->commit();
				$this->ajaxReturn(['status' => 'success','msg' =>'操作成功！']);
			}else{
				 $do->rollback();
				$this->ajaxReturn(['status' => 'warning','msg' =>'操作失败！']);
			}
        }
    }
    /**
    * 列表
    */
    public function index($param=null){
    	$this->_index();
		$btn=array('title'=>'操作','type'=>'html','html'=>' <div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">审核</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
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
}