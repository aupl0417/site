<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class StoretransactiondataController extends CommonModulesController {
	protected $name 			='totals';	//控制器名称
    protected $formtpl_id		=139;			//表单模板ID
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
        
        //统计表
        $do=M('totals');
        $totals  = $do->order("day desc")->limit(2)->select();

        //上一日支付宝付款数
        $yestoday = $totals[1]['alipay_num']==0?"1":$totals[1]['alipay_num'];
        $rate['alipay_num'] =  number_format(abs($totals[0]['alipay_num']-$totals[1]['alipay_num']) /$yestoday*100,1);
        if ($rate['alipay_num']=='0'){
            $rate['alipay_num_color'] = '';
        }else{
            $rate['alipay_num_color'] = ($totals[0]['alipay_num']-$totals[1]['alipay_num'])>0?"text-success":"text-danger";
        }
        $rate['alipay_num_sign'] = ($totals[0]['alipay_num']-$totals[1]['alipay_num'])>0?"&uarr":"&darr";
        //上一日支付宝付款总额
        $yestoday = $totals[1]['day_alipay_total']==0?"1":$totals[1]['day_alipay_total'];
        $rate['day_alipay_total'] =  number_format(abs($totals[0]['day_alipay_total']-$totals[1]['day_alipay_total']) /$yestoday*100,1);
        if ($rate['day_alipay_total']=='0'){
            $rate['day_alipay_total_color'] = '';
        }else{
            $rate['day_alipay_total_color'] = ($totals[0]['day_alipay_total']-$totals[1]['day_alipay_total'])>0?"text-success":"text-danger";
        }
        $rate['day_alipay_total_sign'] = ($totals[0]['day_alipay_total']-$totals[1]['day_alipay_total'])>0?"&uarr":"&darr";
        //上一日现金付款数
        $yestoday = $totals[1]['money_num']==0?"1":$totals[1]['money_num'];
        $rate['money_num'] =  number_format(abs($totals[0]['money_num']-$totals[1]['money_num']) /$yestoday*100,1);
        if ($rate['money_num']=='0'){
            $rate['money_num_color'] = '';
        }else{
            $rate['money_num_color'] = ($totals[0]['money_num']-$totals[1]['money_num'])>0?"text-success":"text-danger";
        }
        $rate['money_num_sign'] = ($totals[0]['money_num']-$totals[1]['money_num'])>0?"&uarr":"&darr";
        //上一日现金付款总额
        $yestoday = $totals[1]['day_money_total']==0?"1":$totals[1]['day_money_total'];
        $rate['day_money_total'] =  number_format(abs($totals[0]['day_money_total']-$totals[1]['day_money_total'])/$yestoday*100,1);
        if ($rate['day_money_total']=='0'){
            $rate['day_money_total_color'] = '';
        }else{
            $rate['day_money_total_color'] = ($totals[0]['day_money_total']-$totals[1]['day_money_total'])>0?"text-success":"text-danger";
        }
        $rate['day_money_total_sign'] = ($totals[0]['day_money_total']-$totals[1]['day_money_total'])>0?"&uarr":"&darr";
        //上一日唐宝支付数量
        $yestoday = $totals[1]['tangbao_num']==0?"1":$totals[1]['tangbao_num'];
        $rate['tangbao_num'] =  number_format(abs($totals[0]['tangbao_num']-$totals[1]['tangbao_num']) /$yestoday*100,1);
        if ($rate['tangbao_num']=='0'){
            $rate['tangbao_num_color'] = '';
        }else{
            $rate['tangbao_num_color'] = ($totals[0]['tangbao_num']-$totals[1]['tangbao_num'])>0?"text-success":"text-danger";
        }
        $rate['tangbao_num_sign'] = ($totals[0]['tangbao_num']-$totals[1]['tangbao_num'])>0?"&uarr":"&darr";
        //上一日糖宝支付总金额
        $yestoday = $totals[1]['day_tangbao_total']==0?"1":$totals[1]['day_tangbao_total'];
        $rate['day_tangbao_total'] =  number_format(abs($totals[0]['day_tangbao_total']-$totals[1]['day_tangbao_total']) /$yestoday*100,1);
        if ($rate['day_tangbao_total']=='0'){
            $rate['day_tangbao_total_color'] = '';
        }else{
            $rate['day_tangbao_total_color'] = ($totals[0]['day_tangbao_total']-$totals[1]['day_tangbao_total'])>0?"text-success":"text-danger";
        }
        $rate['day_tangbao_total_sign'] = ($totals[0]['day_tangbao_total']-$totals[1]['day_tangbao_total'])>0?"&uarr":"&darr";
        //上一天下单数量
        $yestoday = $totals[1]['order_num']==0?"1":$totals[1]['order_num'];
        $rate['order_num'] =  number_format(abs($totals[0]['order_num']-$totals[1]['order_num']) /$yestoday*100,1);
        if ($rate['order_num']=='0'){
            $rate['order_num_color'] = '';
        }else{
            $rate['order_num_color'] = ($totals[0]['order_num']-$totals[1]['order_num'])>0?"text-success":"text-danger";
        }
        $rate['order_num_sign'] = ($totals[0]['order_num']-$totals[1]['order_num'])>0?"&uarr":"&darr";
        //上一天下单总额
        $yestoday = $totals[1]['day_order_total']==0?"1":$totals[1]['day_order_total'];
        $rate['day_order_total'] =  number_format(abs($totals[0]['day_order_total']-$totals[1]['day_order_total']) /$yestoday*100,1);
        if ($rate['day_order_total']=='0'){
            $rate['day_order_total_color'] = '';
        }else{
            $rate['day_order_total_color'] = ($totals[0]['day_order_total']-$totals[1]['day_order_total'])>0?"text-success":"text-danger";
        }
        $rate['day_order_total_sign'] = ($totals[0]['day_order_total']-$totals[1]['day_order_total'])>0?"&uarr":"&darr";
        //上一日下单平均价
        $yestoday = $totals[1]['day_average_order']==0?"1":$totals[1]['day_average_order'];
        $rate['day_average_order'] =  number_format(abs($totals[0]['day_average_order']-$totals[1]['day_average_order']) /$yestoday*100,1);
        if ($rate['day_average_order']=='0'){
            $rate['day_average_order_color'] = '';
        }else{
            $rate['day_average_order_color'] = ($totals[0]['day_average_order']-$totals[1]['day_average_order'])>0?"text-success":"text-danger";
        }
        $rate['day_average_order_sign'] = ($totals[0]['day_average_order']-$totals[1]['day_average_order'])>0?"&uarr":"&darr";
        //上一日成交笔数
        $yestoday = $totals[1]['order_success']==0?"1":$totals[1]['order_success'];
        $rate['order_success'] =  number_format(abs($totals[0]['order_success']-$totals[1]['order_success']) /$yestoday*100,1);
        if ($rate['order_success']=='0'){
            $rate['order_success_color'] = '';
        }else{
            $rate['order_success_color'] = ($totals[0]['order_success']-$totals[1]['order_success'])>0?"text-success":"text-danger";
        }
        $rate['order_success_sign'] = ($totals[0]['order_success']-$totals[1]['order_success'])>0?"&uarr":"&darr";
        //上一日成交总金额
        $yestoday = $totals[1]['day_order_success_total']==0?"1":$totals[1]['day_order_success_total'];
        $rate['day_order_success_total'] =  number_format(abs($totals[0]['day_order_success_total']-$totals[1]['day_order_success_total']) /$yestoday*100,1);
        if ($rate['day_order_success_total']=='0'){
            $rate['day_order_success_total_color'] = '';
        }else{
            $rate['day_order_success_total_color'] = ($totals[0]['day_order_success_total']-$totals[1]['day_order_success_total'])>0?"text-success":"text-danger";
        }
        $rate['day_order_success_total_sign'] = ($totals[0]['day_order_success_total']-$totals[1]['day_order_success_total'])>0?"&uarr":"&darr";
        //上一日成交平均单价
        $yestoday = $totals[1]['day_average_success_order']==0?"1":$totals[1]['day_average_success_order'];
        $rate['day_average_success_order'] =  number_format(abs($totals[0]['day_average_success_order']-$totals[1]['day_average_success_order']) /$yestoday*100,1);
        if ($rate['day_average_success_order']=='0'){
            $rate['day_average_success_order_color'] = '';
        }else{
            $rate['day_average_success_order_color'] = ($totals[0]['day_average_success_order']-$totals[1]['day_average_success_order'])>0?"text-success":"text-danger";
        }
        $rate['day_average_success_order_sign'] = ($totals[0]['day_average_success_order']-$totals[1]['day_average_success_order'])>0?"&uarr":"&darr";
        //上一天确认收货数量
        $yestoday = $totals[1]['accept_num']==0?"1":$totals[1]['accept_num'];
        $rate['accept_num'] =  number_format(abs($totals[0]['accept_num']-$totals[1]['accept_num']) /$yestoday*100,1);
        if ($rate['accept_num']=='0'){
            $rate['accept_num_color'] = '';
        }else{
            $rate['accept_num_color'] = ($totals[0]['accept_num']-$totals[1]['accept_num'])>0?"text-success":"text-danger";
        }
        $rate['accept_num_sign'] = ($totals[0]['accept_num']-$totals[1]['accept_num'])>0?"&uarr":"&darr";
        //上一日确认收货总额
        $yestoday = $totals[1]['day_accept_total']==0?"1":$totals[1]['day_accept_total'];
        $rate['day_accept_total'] =  number_format(abs($totals[0]['day_accept_total']-$totals[1]['day_accept_total']) /$yestoday*100,1);
        if ($rate['day_accept_total']=='0'){
            $rate['day_accept_total_color'] = '';
        }else{
            $rate['day_accept_total_color'] = ($totals[0]['day_accept_total']-$totals[1]['day_accept_total'])>0?"text-success":"text-danger";
        }
        $rate['day_accept_total_sign'] = ($totals[0]['day_accept_total']-$totals[1]['day_accept_total'])>0?"&uarr":"&darr";
        //上一日确认收货平均单价
        $yestoday = $totals[1]['day_average_accept']==0?"1":$totals[1]['day_average_accept'];
        $rate['day_average_accept'] =  number_format(abs($totals[0]['day_average_accept']-$totals[1]['day_average_accept']) /$yestoday*100,1);
        if ($rate['day_average_accept']=='0'){
            $rate['day_average_accept_color'] = '';
        }else{
            $rate['day_average_accept_color'] = ($totals[0]['day_average_accept']-$totals[1]['day_average_accept'])>0?"text-success":"text-danger";
        }
        $rate['day_average_accept_sign'] = ($totals[0]['day_average_accept']-$totals[1]['day_average_accept'])>0?"&uarr":"&darr";
        //上一天申请退款数量
        $yestoday = $totals[1]['refund_num']==0?"1":$totals[1]['refund_num'];
        $rate['refund_num'] =  number_format(abs($totals[0]['refund_num']-$totals[1]['refund_num'])/$yestoday*100,1);
        if ($rate['refund_num']=='0'){
            $rate['refund_num_color'] = '';
        }else{
            $rate['refund_num_color'] = ($totals[0]['refund_num']-$totals[1]['refund_num'])>0?"text-success":"text-danger";
        }
        $rate['refund_num_sign'] = ($totals[0]['refund_num']-$totals[1]['refund_num'])>0?"&uarr":"&darr";
        //上一天申请退款总额
        $yestoday = $totals[1]['day_refund_money']==0?"1":$totals[1]['day_refund_money'];
        $rate['day_refund_money'] =  number_format(abs($totals[0]['day_refund_money']-$totals[1]['day_refund_money']) /$yestoday*100,1);
        if ($rate['day_refund_money']=='0'){
            $rate['day_refund_money_color'] = '';
        }else{
            $rate['day_refund_money_color'] = ($totals[0]['day_refund_money']-$totals[1]['day_refund_money'])>0?"text-success":"text-danger";
        }
        $rate['day_refund_money_sign'] = ($totals[0]['day_refund_money']-$totals[1]['day_refund_money'])>0?"&uarr":"&darr";
        //上一天申请退款平均价
        $yestoday = $totals[1]['day_average_refund']==0?"1":$totals[1]['day_average_refund'];
        $rate['day_average_refund'] =  number_format(abs($totals[0]['day_average_refund']-$totals[1]['day_average_refund']) /$yestoday*100,1);
        if ($rate['day_average_refund']=='0'){
            $rate['day_average_refund_color'] = '';
        }else{
            $rate['day_average_refund_color'] = ($totals[0]['day_average_refund']-$totals[1]['day_average_refund'])>0?"text-success":"text-danger";
        }
        $rate['day_average_refund_sign'] = ($totals[0]['day_average_refund']-$totals[1]['day_average_refund'])>0?"&uarr":"&darr";
        //上一天退款成功数量
        $yestoday = $totals[1]['refund_success_num']==0?"1":$totals[1]['refund_success_num'];
        $rate['refund_success_num'] =  number_format(abs($totals[0]['refund_success_num']-$totals[1]['refund_success_num']) /$yestoday*100,1);
        if ($rate['refund_success_num']=='0'){
            $rate['refund_success_num_color'] = '';
        }else{
            $rate['refund_success_num_color'] = ($totals[0]['refund_success_num']-$totals[1]['refund_success_num'])>0?"text-success":"text-danger";
        }
        $rate['refund_success_num_sign'] = ($totals[0]['refund_success_num']-$totals[1]['refund_success_num'])>0?"&uarr":"&darr";
        //上一天退款成功总金额
        $yestoday = $totals[1]['refund_success_money']==0?"1":$totals[1]['refund_success_money'];
        $rate['refund_success_money'] =  number_format(abs($totals[0]['refund_success_money']-$totals[1]['refund_success_money']) /$yestoday*100,1);
        if ($rate['refund_success_money']=='0'){
            $rate['refund_success_money_color'] = '';
        }else{
            $rate['refund_success_money_color'] = ($totals[0]['refund_success_money']-$totals[1]['refund_success_money'])>0?"text-success":"text-danger";
        }
        $rate['refund_success_money_sign'] = ($totals[0]['refund_success_money']-$totals[1]['refund_success_money'])>0?"&uarr":"&darr";
        //上一天退款成功平均价
        $yestoday = $totals[1]['ad_num']==0?"1":$totals[1]['ad_num'];
        $rate['ad_num'] =  number_format(abs($totals[0]['ad_num']-$totals[1]['ad_num']) /$yestoday*100,1);
        if ($rate['ad_num']=='0'){
            $rate['ad_num_color'] = '';
        }else{
            $rate['ad_num_color'] = ($totals[0]['ad_num']-$totals[1]['ad_num'])>0?"text-success":"text-danger";
        }
        $rate['ad_num_sign'] = ($totals[0]['ad_num']-$totals[1]['ad_num'])>0?"&uarr":"&darr";
        
        $this->assign('result',$result);
        $this->assign('totals',$totals);
        $this->assign('rate',$rate);
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
        $do=M('totals');
        $rs=$do->where(['id' => I('get.id')])->find();
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