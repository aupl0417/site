<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class RefundController extends CommonModulesController {
	protected $name 			='退款管理';	//控制器名称
    protected $formtpl_id		=149;			//表单模板ID
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
    	$this->_index(['map'=>['orders_status' => ['lt', 4]]]);

    	$btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a> <div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">详情</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
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
	* 退款详情
	*/
	public function view(){
		$do=D('Common/RefundRelation');

		$rs=$do->relation(true)->where(['id' => I('get.id')])->find();

		$rs['logs']=D('Common/RefundLogsRelation')->relation(true)->where(['r_id' => $rs['id']])->order('id desc')->select();

		$this->assign('rs',$rs);
		$this->display();
	}

	/**
	* 添加日志
	*/
	public function logs_add(){
		if(I('post.remark')=='') $this->ajaxReturn(['status' => 'warning','msg' =>'请输入留言或备注！']);

		$rs=M('refund')->where(['id' => I('post.r_id')])->find();
		$data 	=	[
			'ip'		=>get_client_ip(),
			'atime'		=>date('Y-m-d H:i:s'),
			'r_id'		=>$rs['id'],
			'r_no'		=>$rs['r_no'],
			'status'	=>$rs['status'],
			'type'		=>$rs['type'],
			'a_uid'		=>session('admin.id'),
			'remark'	=>I('post.remark')
		];

		$insid=M('refund_logs')->add($data);
		if($insid) $this->ajaxReturn(['status' => 'success','msg' =>'操作成功！']);

		else $this->ajaxReturn(['status' => 'warning','msg' =>'操作失败！']);
	}
	
	/**
	* 导出设置
	*/
	public function export_set(){
		if($this->fcfg['export_fields']) $this->assign('rs',eval(html_entity_decode($this->fcfg['export_fields'])));
		
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
		//检查是否有选择导出字段
		if(isset($_POST['field']) && !empty($_POST['field'])){
			$field_ids = '';
			//将选中的导出字段排序
			foreach($_POST['field'] as $k => $v){
				$field_ids .= $v.',';
			}
			$field_ids = substr($field_ids,0,strlen($field_ids)-1); 
			
			$fields = M('formtpl_fields')->field('name,label')->where(' id IN ('.$field_ids.')')->order('instr("'.$field_ids.'",id)')->select();
			if(count($fields) < 8){
				return false;
			}
			//excel横列排序
			$out_option_orders = 'A';
			foreach($fields as $k => $v){
				$out_excel_option[$out_option_orders]['descript'] = $v['label'];
				$out_excel_option[$out_option_orders]['field'] = $v['name'];
				$out_option_orders++;
				$field_names .= $v['name'].',';
				
				if($v['name'] == 's_no'){
					$order_fields = array(
						array('label'=>'运费金额','field'=>'express_price'),
						array('label'=>'商品金额','field'=>'goods_price_edit'),
						array('label'=>'修改后的运费','field'=>'express_price_edit'),
						array('label'=>'实付金额','field'=>'pay_price'),
						array('label'=>'订单总金额','field'=>'total_price'),
						array('label'=>'支付时间','field'=>'pay_time'),
						array('label'=>'支付方式','field'=>'pay_type'),
						array('label'=>'库存结算方式','field'=>'inventory_type'),
					);
					foreach($order_fields as $va){
						$out_excel_option[$out_option_orders]['descript'] = $va['label'];
						$out_excel_option[$out_option_orders]['field'] = $va['field'];
						$out_option_orders++;
						$order_field_names .= $va['field'].',';
					}
				}
			}
			$field_names = substr($field_names,0,strlen($field_names)-1); 
			$order_field_names = substr($order_field_names,0,strlen($order_field_names)-1); 
			
		}else return false;

		//dump($order_field_names);
		//exit;
		$cfg = eval(html_entity_decode($this->fcfg['export_fields']));

		if($cfg['status'])	$map['status']	=	['in',$cfg['status']];
		if($cfg['type']) 	$map['type']	=	['in',$cfg['type']];

		if(empty($cfg['sday'])) $cfg['sday']	=	'2016-07-01';
		if(empty($cfg['eday'])) $cfg['eday']	=	date('Y-m-d',time()+86400);
		$map[$cfg['day_field']]	=	['between',[$cfg['sday'],$cfg['eday']]];

		if(empty($cfg['snum'])) $cfg['snum']	=	0;
		if(empty($cfg['enum'])) $cfg['enum']	=	10000000;
		$map[$cfg['num_field']]	=	['between',[$cfg['snum'],$cfg['enum']]];

		if($cfg['shop_name']) $sql[]	=	'shop_id in (select id from '.C('DB_PREFIX').'shop where shop_name="'.$cfg['shop_name'].'")';
		if($cfg['nick']) $sql[]	=	'seller_id in (select id from '.C('DB_PREFIX').'user where nick="'.$cfg['nick'].'")';

		if($sql)	$map['_string']	=	implode(' and ',$sql);
		$list	=	M('refund')->field('id,s_id,'.$field_names)->where($map)->order('id desc')->limit(1000)->select();
		//dump($list);
		//dump(M()->getlastsql());
		//exit;
		foreach($list as $k => $v){
			//将数据中的字段转换
			foreach($v as $ke => $va){
				if($ke=='status'){
					$data = array('','退款','卖家拒绝','修改','同意','买家寄出商品','卖家寄出商品',10=>'买家可申诉',11=>'卖家未收到退货',12=>'买家未收到货',20=>'退款已取消',100=>'退款已完成');
					$list[$k+$add_num][$ke] = ' '.$data[$va];
				}else if($ke=='type'){
					$data = array('','退货退款','只退款','退运费');
					$list[$k+$add_num][$ke] = ' '.$data[$va];
				}else if($ke=='express_type'){
					$data = array('','快递','EMS');
					$list[$k+$add_num][$ke] = ' '.$data[$va];
				}else if($ke=='inventory_type'){
					$data = array('非即时结算','即时结算');
					$list[$k+$add_num][$ke] = ' '.$data[$va];
				}else if($ke=='terminal'){
					$data = array('PC','WAP','IOS','ANDROID');
					$list[$k+$add_num][$ke] = ' '.$data[$va];
				}else if($ke=='uid' || $ke=='seller_id'){
					$user_info = M('user')->cache(true)->field('nick')->where('id = '.$va)->find();
					$list[$k+$add_num][$ke] = ' '.$user_info['nick'];
				}else if($ke=='shop_id'){
					$shop_info = M('shop')->cache(true)->field('shop_name')->where('id = '.$va)->find();
					$list[$k+$add_num][$ke] = ' '.$shop_info['shop_name'];
				}else{
					$list[$k+$add_num][$ke] = ' '.$va;
				}
			}
			if(isset($list[$k]['s_no'])){
				$res = M('orders_shop')->field($order_field_names)->where('id="'.$v['s_id'].'"')->find();
				$list[$k]['express_price'] = $res['express_price'];
				$list[$k]['goods_price_edit'] = $res['goods_price_edit'];
				$list[$k]['express_price_edit'] = $res['express_price_edit'];
				$list[$k]['pay_price'] = $res['pay_price'];
				$list[$k]['total_price'] = $res['total_price'];
				$list[$k]['pay_time'] = $res['pay_time'];
				$data = array('非即时结算','即时结算');
				$list[$k]['inventory_type'] = $data[$res['inventory_type']];
				$data = array('','余额','唐宝','支付宝','微信');
				$list[$k]['pay_type'] = $data[$res['pay_type']];
				
			}
			
			
		}
		
		
		//dump($out_excel_option);
		//dump($list);
	
		D('Admin/Excel')->outExcel($list,$out_excel_option,'退款信息');
	}
}