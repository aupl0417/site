<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class StorepromotiondataController extends CommonModulesController {
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
        $do = M('activity_participate');
        $sql = 'select count(*) as activity_total_num,sum(calc_before_money-calc_after_money) as activity_total_money from ylh_activity_participate where status=1';
        $activity_num = $do->query($sql);
        //广告投放笔数和总金额
        $do = M('ad');
        $sql = 'select count(*) as ad_total_num,sum(money) as ad_total_money from ylh_ad where date_format(pay_time,"%Y-%m-%d")!="0000-00-00"';
        $ad_num = $do->query($sql);
        //品牌推广总数
        $do=M('coupon');
        //累计领取的优惠券数量,金额
        $sql = 'select count(*) as coupon_total_num,sum(price) as coupon_total_money from ylh_coupon where date_format(get_time,"%Y-%m-%d")!="0000-00-00"';
        $total_get = $do->query($sql);
         //累计使用优惠券次数,总额
        $sql = 'select count(*) as use_coupon_total_num,sum(price) as use_coupon_total_money from ylh_coupon where date_format(use_time,"%Y-%m-%d")!="0000-00-00"';
        $total_use = $do->query($sql);
        
        $result = array_merge_recursive($activity_num[0],$ad_num[0],$total_get[0],$total_use[0]);
        //累计通过审核的素材
        $do = M('ad_sucai');
        $result['sucai_total_num']  = $do->where(['status' => 1])->count();
        
        //统计表
        $do=M('totals');
        //饼图显示
        $totals  = $do->order("day desc")->limit(2)->select();
        if (!$totals[0]['day_alipay_total']){
            $totals[0]['day_alipay_total'] = 0;
        }
        if (!$totals[0]['day_money_total']){
            $totals[0]['day_money_total'] = 0;
        }
        if (!$totals[0]['day_tangbao_total']){
            $totals[0]['day_tangbao_total'] = 0;
        }
        
        //活动下单数量
        $yestoday = $totals[1]['activity_num']==0?"1":$totals[1]['activity_num'];
        $rate['activity_num'] =  number_format(abs($totals[0]['activity_num']-$totals[1]['activity_num']) /$yestoday*100,1);
        if ($rate['activity_num']=='0'){
            $rate['activity_num_color'] = '';
        }else{
            $rate['activity_num_color'] = ($totals[0]['activity_num']-$totals[1]['activity_num'])>0?"text-success":"text-danger";
        }
        $rate['activity_num_sign'] = ($totals[0]['activity_num']-$totals[1]['activity_num'])>0?"&uarr":"&darr";
        //每天活动成交数量
        $yestoday = $totals[1]['activity_pay_num']==0?"1":$totals[1]['activity_pay_num'];
        $rate['activity_pay_num'] =  number_format(abs($totals[0]['activity_pay_num']-$totals[1]['activity_pay_num']) /$yestoday*100,1);
        if ($rate['activity_pay_num']=='0'){
            $rate['activity_pay_num_color'] = '';
        }else{
            $rate['activity_pay_num_color'] = ($totals[0]['activity_pay_num']-$totals[1]['activity_pay_num'])>0?"text-success":"text-danger";
        }
        $rate['activity_pay_num_sign'] = ($totals[0]['activity_pay_num']-$totals[1]['activity_pay_num'])>0?"&uarr":"&darr";
        //每天活动成交总金额
        $yestoday = $totals[1]['day_activity_money']==0?"1":$totals[1]['day_activity_money'];
        $rate['day_activity_money'] =  number_format(abs($totals[0]['day_activity_money']-$totals[1]['day_activity_money']) /$yestoday*100,1);
        if ($rate['day_activity_money']=='0'){
            $rate['day_activity_money_color'] = '';
        }else{
            $rate['day_activity_money_color'] = ($totals[0]['day_activity_money']-$totals[1]['day_activity_money'])>0?"text-success":"text-danger";
        }
        $rate['day_activity_money_sign'] = ($totals[0]['day_activity_money']-$totals[1]['day_activity_money'])>0?"&uarr":"&darr";
        //每天活动成交平均价
        $yestoday = $totals[1]['day_average_activity']==0?"1":$totals[1]['day_average_activity'];
        $rate['day_average_activity'] =  number_format(abs($totals[0]['day_average_activity']-$totals[1]['day_average_activity'])/$yestoday*100,1);
        if ($rate['day_average_activity']=='0'){
            $rate['day_average_activity_color'] = '';
        }else{
            $rate['day_average_activity_color'] = ($totals[0]['day_average_activity']-$totals[1]['day_average_activity'])>0?"text-success":"text-danger";
        }
        $rate['day_average_activity_sign'] = ($totals[0]['day_average_activity']-$totals[1]['day_average_activity'])>0?"&uarr":"&darr";
        //每天领取优惠券的数量
        $yestoday = $totals[1]['coupon_num']==0?"1":$totals[1]['coupon_num'];
        $rate['coupon_num'] =  number_format(abs($totals[0]['coupon_num']-$totals[1]['coupon_num']) /$yestoday*100,1);
        if ($rate['coupon_num']=='0'){
            $rate['coupon_num_color'] = '';
        }else{
            $rate['coupon_num_color'] = ($totals[0]['coupon_num']-$totals[1]['coupon_num'])>0?"text-success":"text-danger";
        }
        $rate['coupon_num_sign'] = ($totals[0]['coupon_num']-$totals[1]['coupon_num'])>0?"&uarr":"&darr";
        //每天被领取的总金额
        $yestoday = $totals[1]['day_coupon_total']==0?"1":$totals[1]['day_coupon_total'];
        $rate['day_coupon_total'] =  number_format(abs($totals[0]['day_coupon_total']-$totals[1]['day_coupon_total']) /$yestoday*100,1);
        if ($rate['day_coupon_total']=='0'){
            $rate['day_coupon_total_color'] = '';
        }else{
            $rate['day_coupon_total_color'] = ($totals[0]['day_coupon_total']-$totals[1]['day_coupon_total'])>0?"text-success":"text-danger";
        }
        $rate['day_coupon_total_sign'] = ($totals[0]['day_coupon_total']-$totals[1]['day_coupon_total'])>0?"&uarr":"&darr";
        //上一天被使用的优惠券数量
        $yestoday = $totals[1]['use_coupon_num']==0?"1":$totals[1]['use_coupon_num'];
        $rate['use_coupon_num'] =  number_format(abs($totals[0]['use_coupon_num']-$totals[1]['use_coupon_num']) /$yestoday*100,1);
        if ($rate['use_coupon_num']=='0'){
            $rate['use_coupon_num_color'] = '';
        }else{
            $rate['use_coupon_num_color'] = ($totals[0]['use_coupon_num']-$totals[1]['use_coupon_num'])>0?"text-success":"text-danger";
        }
        $rate['use_coupon_num_sign'] = ($totals[0]['use_coupon_num']-$totals[1]['use_coupon_num'])>0?"&uarr":"&darr";
        //每天被使用优惠券总额
        $yestoday = $totals[1]['use_coupon_money']==0?"1":$totals[1]['use_coupon_money'];
        $rate['use_coupon_money'] =  number_format(abs($totals[0]['use_coupon_money']-$totals[1]['use_coupon_money']) /$yestoday*100,1);
        if ($rate['use_coupon_money']=='0'){
            $rate['use_coupon_money_color'] = '';
        }else{
            $rate['use_coupon_money_color'] = ($totals[0]['use_coupon_money']-$totals[1]['use_coupon_money'])>0?"text-success":"text-danger";
        }
        $rate['use_coupon_money_sign'] = ($totals[0]['use_coupon_money']-$totals[1]['use_coupon_money'])>0?"&uarr":"&darr";
        //上一日新增广告投放笔数
        $yestoday = $totals[1]['ad_num']==0?"1":$totals[1]['ad_num'];
        $rate['ad_num'] =  number_format(abs($totals[0]['ad_num']-$totals[1]['ad_num']) /$yestoday*100,1);
        if ($rate['ad_num']=='0'){
            $rate['ad_num_color'] = '';
        }else{
            $rate['ad_num_color'] = ($totals[0]['ad_num']-$totals[1]['ad_num'])>0?"text-success":"text-danger";
        }
        $rate['ad_num_sign'] = ($totals[0]['ad_num']-$totals[1]['ad_num'])>0?"&uarr":"&darr";
        //上一日新增广告投放金额
        $yestoday = $totals[1]['ad_money']==0?"1":$totals[1]['ad_money'];
        $rate['ad_money'] =  number_format(abs($totals[0]['ad_money']-$totals[1]['ad_money']) /$yestoday*100,1);
        if ($rate['ad_money']=='0'){
            $rate['ad_money_color'] = '';
        }else{
            $rate['ad_money_color'] = ($totals[0]['ad_money']-$totals[1]['ad_money'])>0?"text-success":"text-danger";
        }
        $rate['ad_money_sign'] = ($totals[0]['ad_money']-$totals[1]['ad_money'])>0?"&uarr":"&darr";
        //上一日新增广告投放的平均单价
        $yestoday = $totals[1]['ad_price_unit']==0?"1":$totals[1]['ad_price_unit'];
        $rate['ad_price_unit'] =  number_format(abs($totals[0]['ad_price_unit']-$totals[1]['ad_price_unit']) /$yestoday*100,1);
        if ($rate['ad_price_unit']=='0'){
            $rate['ad_price_unit_color'] = '';
        }else{
            $rate['ad_price_unit_color'] = ($totals[0]['ad_price_unit']-$totals[1]['ad_price_unit'])>0?"text-success":"text-danger";
        }
        $rate['ad_price_unit_sign'] = ($totals[0]['ad_price_unit']-$totals[1]['ad_price_unit'])>0?"&uarr":"&darr";
        //上一日新增投放素材
        $yestoday = $totals[1]['sucai_num']==0?"1":$totals[1]['sucai_num'];
        $rate['sucai_num'] =  number_format(abs($totals[0]['sucai_num']-$totals[1]['sucai_num']) /$yestoday*100,1);
        if ($rate['sucai_num']=='0'){
            $rate['sucai_num_color'] = '';
        }else{
            $rate['sucai_num_color'] = ($totals[0]['sucai_num']-$totals[1]['sucai_num'])>0?"text-success":"text-danger";
        }
        $rate['sucai_num_sign'] = ($totals[0]['sucai_num']-$totals[1]['sucai_num'])>0?"&uarr":"&darr";
        
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