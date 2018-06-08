<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class RechargeController extends CommonModulesController {
	protected $name 			='充值管理';	//控制器名称
    protected $formtpl_id		=77;			//表单模板ID
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
		if(I('post.is_out_excel') == 1){
			//dump($_POST);
			$map['id'] = array('in',I('post.ids'));
			$param['map'] = $map;
			$param['is_out_excel'] = 1;
			$param['out_excel_title'] = '充值记录';
			$param['data_trans'] = array(
				'uid' => array(
							'type' => 'sql',
							'table' => 'user',
							'field' => 'nick',
							'where' => 'id=',
						),
				'status' => array(
							'type' => 'type',
							'data' => array('未付款','已付款'),
						),
				'pay_type' => array(
							'type' => 'type',
							'data' => array('','支付宝','微信'),
						),
			);
			//['uid'] = "M('user')->field('nick')->where('id=***')->find()";
			
			$this->_index($param);
		}else{
			$this->_index();
			$this->display();
		}
    	
		
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
	* 导出设置
	*/
	public function export_set(){
		//dump(eval(html_entity_decode($this->fcfg['export_fields'])));
		if($this->fcfg['export_fields']) $this->assign('rs',eval(html_entity_decode($this->fcfg['export_fields'])));
		//dump(eval(html_entity_decode($this->fcfg['export_fields'])));
		$this->display();
	}
	/**
	* 检查导出数据
	*/
	public function export_set_save(){
		//检查是否有选择导出字段
		if(!isset($_POST['field']) || empty($_POST['field'])){
			$this->ajaxReturn(['status' =>'warning','msg' =>'导出字段不能为空！']);
		}
		$do = M('formtpl');

		if(false!==$do->where(['id' => $this->formtpl_id])->save(['export_fields' => 'return '.var_export(I('post.'),true).';'])){
			//$this->export_file();

			$this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);
		}else{
			$this->ajaxReturn(['status' =>'warning','msg' =>'操作失败！']);
		}
	}

	/**
	* 导出数据
	*/
	public function export_file(){

		if($this->fcfg['export_fields']){
			//检查是否有选择导出字段
			if(isset($_POST['field']) && !empty($_POST['field'])){
				$field_ids = '';
				//将选中的导出字段排序
				foreach($_POST['field'] as $k => $v){
					$field_ids .= $v.',';
				}
				$field_ids = substr($field_ids,0,strlen($field_ids)-1); 
				
				$fields = M('formtpl_fields')->field('name,label')->where(' id IN ('.$field_ids.')')->order('instr("'.$field_ids.'",id)')->select();
				//excel横列排序
				$out_option_orders = 'A';
				foreach($fields as $k => $v){
					$out_excel_option[$out_option_orders]['descript'] = $v['label'];
					$out_excel_option[$out_option_orders]['field'] = $v['name'];
					$out_option_orders++;
					$field_names .= $v['name'].',';
				}
				$field_names = substr($field_names,0,strlen($field_names)-1); 
			}else return false;

			$cfg = eval(html_entity_decode($this->fcfg['export_fields']));

			if($cfg['status'])	$map['status']	=	['in',$cfg['status']];
			if($cfg['pay_type'])	$map['pay_type']	=	['in',$cfg['pay_type']];

			if(empty($cfg['sday'])) $cfg['sday']	=	'2016-07-01';
			if(empty($cfg['eday'])) $cfg['eday']	=	date('Y-m-d',time()+86400);
			$map[$cfg['day_field']]	=	['between',[$cfg['sday'],$cfg['eday']]];

			if(empty($cfg['snum'])) $cfg['snum']	=	0;
			if(empty($cfg['enum'])) $cfg['enum']	=	10000000;
			$map[$cfg['num_field']]	=	['between',[$cfg['snum'],$cfg['enum']]];

			if($cfg['nick']) $sql[]	=	'uid in (select id from '.C('DB_PREFIX').'user where nick="'.$cfg['nick'].'")';

			if($sql)	$map['_string']	=	implode(' and ',$sql);
			$list	=	M('recharge')->field($field_names)->where($map)->order('id desc')->limit(1000)->select();
			//dump($list);
			foreach($list as $k => $v){
				foreach($v as $ke => $va){
					if($ke=='status'){
						$data = array('未付款','已付款');
						$list[$k][$ke] = ' '.$data[$va];
					}else if($ke=='pay_type'){
						$data = array('','支付宝','微信');
						$list[$k][$ke] = ' '.$data[$va];
					}else if($ke=='uid'){
						$user_info = M('user')->cache(true)->field('nick')->where('id = '.$va)->find();
						$list[$k][$ke] = ' '.$user_info['nick'];
					}else{
						$list[$k][$ke] = ' '.$va;
					}
					
				}
				
			}
			
			D('Admin/Excel')->outExcel($list,$out_excel_option,'充值订单');
		}else return false;

	}
}