<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class TotalstransController extends CommonModulesController {
	protected $name 			='交易统计';	//控制器名称
    protected $formtpl_id		=208;			//表单模板ID
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
    * 计算比值
    */
	public function ratio($old,$new){
		$yestoday = $old==0?"1":$old;
        $rate['value'] =  number_format(abs($new-$old) /$yestoday*100,1);
        if ($rate['value']=='0'){
            $rate['color'] = '';
        }else{
            $rate['color'] = ($new-$old)>0?"text-success":"text-danger";
        }
        $rate['sign'] = ($new-$old)>0?"&uarr":"&darr";
		return $rate;
	}

    /**
    * 列表
    */
    public function index($param=null){
        //活动成交数
        $do = M('orders_shop');
         //累计支付宝支付总金额
        $result['total_alipay']  = $do->where(['pay_type' => 3,'_string' => 'date_format(pay_time,"%Y-%m-%d")!="0000-00-00"'])->sum("pay_price");
        //累计现金支付总金额
        $result['money_total'] = $do->where(['pay_type' => 1,'_string' => 'date_format(pay_time,"%Y-%m-%d")!="0000-00-00"'])->sum("pay_price");
        //累计唐宝支付的总额
        $result['total_tangbao'] = $do->where(['pay_type' => 2,'_string' => 'date_format(pay_time,"%Y-%m-%d")!="0000-00-00"'])->sum('pay_price');
       //累计确认收货总金额
        $result['accept_total'] = $do->where(['_string' => 'date_format(receipt_time,"%Y-%m-%d")!="0000-00-00"'])->sum('pay_price');
        //累计成功交易总金额
        $result['success_order_total'] =  $do->where(['_string' => 'date_format(pay_time,"%Y-%m-%d")!="0000-00-00"'])->sum('pay_price');
		
        $do=M('refund');
        //申请退款的总金额
        $result['refund_total']  = $do->sum("money");
        //成功退款的总金额
        $result['refund_success_total'] = $do->where(['_string' => 'date_format(accept_time,"%Y-%m-%d")!="0000-00-00"'])->sum("money");
		
        //交易统计表
        $totals_trans  = M('totals_trans')->field('alipay_num,day_alipay_total,money_num,day_money_total,tangbao_num,day_tangbao_total,order_num,day_order_total,day_average_order,order_success,day_order_success_total,day_average_success_order,accept_num,day_accept_total,day_average_accept,refund_num,day_refund_money,day_average_refund,refund_success_num,refund_success_money,refund_success_average')->order("day desc")->limit(2)->select();
	
		//支付宝比值
		$ratio['alipay_num'] = $this->ratio($totals_trans[1]['alipay_num'],$totals_trans[0]['alipay_num']);
		$ratio['day_alipay_total'] = $this->ratio($totals_trans[1]['day_alipay_total'],$totals_trans[0]['day_alipay_total']);
		//现金比值
		$ratio['money_num'] = $this->ratio($totals_trans[1]['money_num'],$totals_trans[0]['money_num']);
		$ratio['day_money_total'] = $this->ratio($totals_trans[1]['day_money_total'],$totals_trans[0]['day_money_total']);
		//唐宝比值
		$ratio['tangbao_num'] = $this->ratio($totals_trans[1]['tangbao_num'],$totals_trans[0]['tangbao_num']);
		$ratio['day_tangbao_total'] = $this->ratio($totals_trans[1]['day_tangbao_total'],$totals_trans[0]['day_tangbao_total']);
		//下单比值
		$ratio['order_num'] = $this->ratio($totals_trans[1]['order_num'],$totals_trans[0]['order_num']);
		$ratio['day_order_total'] = $this->ratio($totals_trans[1]['day_order_total'],$totals_trans[0]['day_order_total']);
		$ratio['day_average_order'] = $this->ratio($totals_trans[1]['day_average_order'],$totals_trans[0]['day_average_order']);
		//成交比值
		$ratio['order_success'] = $this->ratio($totals_trans[1]['order_success'],$totals_trans[0]['order_success']);
		$ratio['day_order_success_total'] = $this->ratio($totals_trans[1]['day_order_success_total'],$totals_trans[0]['day_order_success_total']);
		$ratio['day_average_success_order'] = $this->ratio($totals_trans[1]['day_average_success_order'],$totals_trans[0]['day_average_success_order']);
		//确认收货比值
		$ratio['accept_num'] = $this->ratio($totals_trans[1]['accept_num'],$totals_trans[0]['accept_num']);
		$ratio['day_accept_total'] = $this->ratio($totals_trans[1]['day_accept_total'],$totals_trans[0]['day_accept_total']);
		$ratio['day_average_accept'] = $this->ratio($totals_trans[1]['day_average_accept'],$totals_trans[0]['day_average_accept']);
		//申请退款比值
		$ratio['refund_num'] = $this->ratio($totals_trans[1]['refund_num'],$totals_trans[0]['refund_num']);
		$ratio['day_refund_money'] = $this->ratio($totals_trans[1]['day_refund_money'],$totals_trans[0]['day_refund_money']);
		$ratio['day_average_refund'] = $this->ratio($totals_trans[1]['day_average_refund'],$totals_trans[0]['day_average_refund']);
		//退款成功比值
		$ratio['refund_success_num'] = $this->ratio($totals_trans[1]['refund_success_num'],$totals_trans[0]['refund_success_num']);
		$ratio['refund_success_money'] = $this->ratio($totals_trans[1]['refund_success_money'],$totals_trans[0]['refund_success_money']);
		$ratio['refund_success_average'] = $this->ratio($totals_trans[1]['refund_success_average'],$totals_trans[0]['refund_success_average']);
		
        $this->assign('result',$result);
        $this->assign('ratio',$ratio);
        $this->assign('totals_trans',$totals_trans);
		$this->display();
    }
    /**
     * 数据明细
     */
	public function detail(){
		$map=is_array($this->map)?$this->map:array();
        if($param['map']) $map=array_merge($map,$param['map']);
        
        if(in_array(CONTROLLER_NAME,['Goods','Help']) && I('get.category_id')){
            $map['category_id'] = ['in',sortid(['table' =>strtolower(CONTROLLER_NAME).'_category','sid' => I('get.category_id')])];
        }
        
        $pagelist=pagelist(array(
            'do'		=>$this->fcfg['do'],
            'table'		=>$this->fcfg['modelname'],
            'pagesize'	=>$this->fcfg['pagesize'],
            'order'		=>$this->fcfg['order'],
            'fields'	=>$this->fcfg['fields'],
            'relation'	=>$this->fcfg['action_type']==2?true:'',
            'map'		=>$map,
        ));
        $this->assign('pagelist',$pagelist);
		//列表字段
        $btn=array('title'=>'操作','type'=>'html','html'=>'<div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">详情</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
        $this->assign('fields',$this->plist(null,$btn));
		$this->display();
	}
	
    /**
     * 详情
     * @param int $_GET['id']	订单ID
     */
    public function view(){
        $do=M('totals_trans');
        $rs=$do->where(['id' => I('get.id')])->find();
		//dump($rs);
        $this->assign('id',I('get.id'));
        $this->assign('rs',$rs);
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