<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class TotalsmobilerechargeController extends CommonModulesController {
	protected $name 			='话费流量充值统计';	//控制器名称
    protected $formtpl_id		=258;			//表单模板ID
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
		//总和
		$do = M('mobile_orders_totals');
		$result = $do->field('sum(num_totals) as num_totals,sum(money_totals) as money_totals,sum(score_totals) as score_totals,sum(recharge_success_num) as recharge_success_num,sum(recharge_success_money) as recharge_success_money,sum(recharge_success_avg) as recharge_success_avg,sum(recharge_success_score) as recharge_success_score,sum(fare_totals_num) as fare_totals_num,sum(flow_totals_num) as flow_totals_num,sum(fare_totals_money) as fare_totals_money,sum(flow_totals_money) as flow_totals_money,sum(fare_totals_score) as fare_totals_score,sum(flow_totals_score) as flow_totals_score,sum(flow_totals_score) as flow_totals_score,sum(fare_balance_pay_num) as fare_balance_pay_num,sum(fare_weixin_pay_num) as fare_weixin_pay_num,sum(fare_alipay_pay_num) as fare_alipay_pay_num,sum(fare_bank_pay_num) as fare_bank_pay_num,sum(fare_pc_recharge_num) as fare_pc_recharge_num,sum(fare_wap_recharge_num) as fare_wap_recharge_num,sum(fare_ios_recharge_num) as fare_ios_recharge_num,sum(fare_android_recharge_num) as fare_android_recharge_num,sum(fare_success_num) as fare_success_num,sum(fare_success_money) as fare_success_money,sum(fare_success_score) as fare_success_score,sum(flow_success_num) as flow_success_num,sum(flow_success_money) as flow_success_money,sum(flow_success_score) as flow_success_score,sum(fare_success_avg) as fare_success_avg,sum(flow_success_avg) as flow_success_avg,sum(flow_balance_pay_num) as flow_balance_pay_num,sum(flow_weixin_pay_num) as flow_weixin_pay_num,sum(flow_alipay_pay_num) as flow_alipay_pay_num,sum(flow_bank_pay_num) as flow_bank_pay_num,sum(flow_pc_recharge_num) as flow_pc_recharge_num,sum(flow_wap_recharge_num) as flow_wap_recharge_num,sum(flow_ios_recharge_num) as flow_ios_recharge_num,sum(flow_android_recharge_num) as flow_android_recharge_num,sum(fare_move_operator_num) as fare_move_operator_num,sum(flow_move_operator_num) as flow_move_operator_num,sum(fare_unicom_operator_num) as fare_unicom_operator_num,sum(flow_unicom_operator_num) as flow_unicom_operator_num,sum(fare_telecom_operator_num) as fare_telecom_operator_num,sum(flow_telecom_operator_num) as flow_telecom_operator_num,sum(flow_pay_num) as flow_pay_num,sum(fare_pay_num) as fare_pay_num,sum(pay_totals) as pay_totals')->find();
		
		$data = $do->order('id desc,day')->limit(7)->select();
		$last2 = $data['1'];
		$last1 = $data['0'];
		
		//新增话费充值笔数比值
		$ratio['fare_totals_num'] = $this->ratio($last2['fare_totals_num'],$last1['fare_totals_num']);
		//新增话费充值成功笔数比值
		$ratio['fare_success_num'] = $this->ratio($last2['fare_success_num'],$last1['fare_success_num']);
		//新增流量充值笔数比值
		$ratio['flow_totals_num'] = $this->ratio($last2['flow_totals_num'],$last1['flow_totals_num']);		
		//新增流充值成功笔数比值
		$ratio['flow_success_num'] = $this->ratio($last2['flow_success_num'],$last1['flow_success_num']);
		//新增话费充值金额比值
		$ratio['fare_totals_money'] = $this->ratio($last2['fare_totals_money'],$last1['fare_totals_money']);
		//新增流量充值金额比值
		$ratio['flow_totals_money'] = $this->ratio($last2['flow_totals_money'],$last1['flow_totals_money']);	
		//新增话费充值成功金额比值
		$ratio['fare_success_money'] = $this->ratio($last2['fare_success_money'],$last1['fare_success_money']);
		//新增流量充值成功金额比值
		$ratio['flow_success_money'] = $this->ratio($last2['flow_success_money'],$last1['flow_success_money']);
		//新增话费充值积分比值
		$ratio['fare_totals_score'] = $this->ratio($last2['fare_totals_score'],$last1['fare_totals_score']);
		//新增流量充值积分比值
		$ratio['flow_totals_score'] = $this->ratio($last2['flow_totals_score'],$last1['flow_totals_score']);	
		//新增话费充值成功积分比值
		$ratio['fare_success_score'] = $this->ratio($last2['fare_success_score'],$last1['fare_success_score']);
		//新增流量充值成功积分比值
		$ratio['flow_success_score'] = $this->ratio($last2['flow_success_score'],$last1['flow_success_score']);

		//话费新增支付宝充值笔数比值
		$ratio['fare_alipay_pay_num'] = $this->ratio($last2['fare_alipay_pay_num'],$last1['fare_alipay_pay_num']);
		//话费新增支付宝充值金额比值
		$ratio['fare_alipay_pay_money'] = $this->ratio($last2['fare_alipay_pay_money'],$last1['fare_alipay_pay_money']);		
		//话费新增余额充值笔数比值
		$ratio['fare_balance_pay_num'] = $this->ratio($last2['fare_balance_pay_num'],$last1['fare_balance_pay_num']);
		//话费新增余额充值金额比值
		$ratio['fare_balance_pay_money'] = $this->ratio($last2['fare_balance_pay_money'],$last1['fare_balance_pay_money']);	
		//话费新增微信充值笔数比值
		$ratio['fare_weixin_pay_num'] = $this->ratio($last2['fare_weixin_pay_num'],$last1['fare_weixin_pay_num']);
		//话费新增微信充值金额比值
		$ratio['fare_weixin_pay_money'] = $this->ratio($last2['fare_weixin_pay_money'],$last1['fare_weixin_pay_money']);
		//话费新增银联充值笔数比值
		$ratio['fare_bank_pay_num'] = $this->ratio($last2['fare_bank_pay_num'],$last1['fare_bank_pay_num']);
		//话费新增银联充值金额比值
		$ratio['fare_bank_pay_money'] = $this->ratio($last2['fare_bank_pay_money'],$last1['fare_bank_pay_money']);

		//流量新增支付宝充值笔数比值
		$ratio['flow_alipay_pay_num'] = $this->ratio($last2['flow_alipay_pay_num'],$last1['flow_alipay_pay_num']);
		//流量新增支付宝充值金额比值
		$ratio['flow_alipay_pay_moeny'] = $this->ratio($last2['flow_alipay_pay_moeny'],$last1['flow_alipay_pay_moeny']);		
		//流量新增余额充值笔数比值
		$ratio['flow_balance_pay_num'] = $this->ratio($last2['flow_balance_pay_num'],$last1['flow_balance_pay_num']);
		//流量新增余额充值金额比值
		$ratio['flow_balance_pay_money'] = $this->ratio($last2['flow_balance_pay_money'],$last1['flow_balance_pay_money']);	
		//流量新增微信充值笔数比值
		$ratio['flow_weixin_pay_num'] = $this->ratio($last2['flow_weixin_pay_num'],$last1['flow_weixin_pay_num']);
		//流量新增微信充值金额比值
		$ratio['flow_weixin_pay_money'] = $this->ratio($last2['flow_weixin_pay_money'],$last1['flow_weixin_pay_money']);
		//流量新增银联充值笔数比值
		$ratio['flow_bank_pay_num'] = $this->ratio($last2['flow_bank_pay_num'],$last1['flow_bank_pay_num']);
		//流量新增银联充值金额比值
		$ratio['flow_bank_pay_money'] = $this->ratio($last2['flow_bank_pay_money'],$last1['flow_bank_pay_money']);

		//成功率
		//充值总笔数成功率
		$success_rate['totals_rate'] = number_format($result['recharge_success_num']/$result['pay_totals']*100,2);
		$success_rate['fare_rate']   = number_format($result['fare_success_num']/$result['fare_pay_num']*100,2);
		$success_rate['flow_rate']   = number_format($result['flow_success_num']/$result['flow_pay_num']*100,2);
		
		$x_day;
		foreach($data as $key=>$val){
			$x_day[$key]    	 			= $val['day']?$val['day']:0;
			$x_num_totals[$key]				= $val['num_totals']?$val['num_totals']:0;
			$x_money_totals[$key]			= $val['money_totals']?$val['money_totals']:0;
			$x_recharge_success_num[$key]	= $val['recharge_success_num']?$val['recharge_success_num']:0;
			$x_recharge_success_money[$key]	= $val['recharge_success_money']?$val['recharge_success_money']:0;
			$pc_recharge_money[$key]		= $val['pc_recharge_money']?$val['pc_recharge_money']:0;
			$wap_recharge_money[$key]		= $val['wap_recharge_money']?$val['wap_recharge_money']:0;
			$ios_recharge_money[$key]		= $val['ios_recharge_money']?$val['ios_recharge_money']:0;
			$android_recharge_money[$key]	= $val['android_recharge_money']?$val['android_recharge_money']:0;
			$fare_totals_money[$key]		= $val['fare_totals_money']?$val['fare_totals_money']:0;
			$flow_totals_money[$key]		= $val['flow_totals_money']?$val['flow_totals_money']:0;
			$fare_success_money[$key]		= $val['fare_success_money']?$val['fare_success_money']:0;
			$flow_success_money[$key]		= $val['flow_success_money']?$val['flow_success_money']:0;
		}
		
		//七天数据统计
		$test['title'] = "手机充值七天数据统计";//标题
		$test['top'] = "12%";		//表格距离上部百分比,可以不填写
		$test['left'] = "5%";       //表格距离左部百分比,可以不填写
		$test['right'] = "5%";      //表格距离右部百分比,可以不填写
		$test['bottom'] = "5%";     //表格距离下部百分比,可以不填写
		$test['xAxis'] = $x_day;
		$test['x_title'] =array(  //要统计的数据标题
/* 			0 =>'充值笔数',
			1 =>'成功充值笔数', */
			0 =>'充值总金额',
			1 =>'成功充值总金额',
			2 =>'pc充值金额',
			3 =>'wap充值金额',
			4 =>'ios充值金额',
			5 =>'android充值金额',
			6 =>'话费充值总金额',
			7 =>'话费成功充值总金额',
			8 =>'流量充值总金额',
			9 =>'流量充值成功总金额',
		);
		$test['data'] = array(   //图表中的数据

			array(
				'name' => "充值总金额",  //x_title
				'type' => "bar", //line:折线图，bar:柱状图
				'stack'=> '充值金额',
				'data' => $x_money_totals,
			),		
			array(
				'name' => "成功充值总金额",  //x_title
				'type' => "bar", //line:折线图，bar:柱状图
				'stack'=> '充值金额',
				'data' => $x_recharge_success_money,
			),
			array(
				'name' => "pc充值金额",  //x_title
				'type' => "bar", //line:折线图，bar:柱状图
				'stack'=> '下单渠道',
				'data' => $pc_recharge_money,
			),		
			array(
				'name' => "wap充值金额",  //x_title
				'type' => "bar", //line:折线图，bar:柱状图
				'stack'=> '下单渠道',
				'data' => $wap_recharge_money,
			),
			array(
				'name' => "ios充值金额",  //x_title
				'type' => "bar", //line:折线图，bar:柱状图
				'stack'=> '下单渠道',
				'data' => $ios_recharge_money,
			),		
			array(
				'name' => "android充值金额",  //x_title
				'type' => "bar", //line:折线图，bar:柱状图
				'stack'=> '下单渠道',
				'data' => $android_recharge_money,
			),
			array(
				'name' => "话费充值总金额",  //x_title
				'type' => "bar", //line:折线图，bar:柱状图
				'stack'=> '话费充值',
				'data' => $fare_totals_money,
			),		
			array(
				'name' => "话费成功充值总金额",  //x_title
				'type' => "bar", //line:折线图，bar:柱状图
				'stack'=> '话费充值',
				'data' => $fare_success_money,
			),
			array(
				'name' => "流量充值总金额",  //x_title
				'type' => "bar", //line:折线图，bar:柱状图
				'stack'=> '流量充值',
				'data' => $flow_totals_money,
			),		
			array(
				'name' => "流量充值成功总金额",  //x_title
				'type' => "bar", //line:折线图，bar:柱状图
				'stack'=> '流量充值',
				'data' => $flow_success_money,
			),
		);
		
		//话费支付方式
		$fare_pay['title'] = array(
			'text'    => "话费支付方式",   //主标题
			'subtext' => "",   //副标题
			'x'       => "center",       //标题位置(left,right,center)
		);

		$fare_pay['legend'] =array(
			0 =>'余额',
			1 =>'支付宝',
			2 =>'微信',
			3 =>'网银',
		);
		$fare_pay["x_title"] = "left";      //legend 位置(left,right,center)
		$fare_pay["name"]    = "话费支付方式";  //图表用途名称
		$fare_pay['data'] = array(//数据
			array(
				'value' => $result['fare_balance_pay_num']?$result['fare_balance_pay_num']:0,
				'name'  => "余额",
			),
			array(
				'value' => $result['fare_alipay_pay_num']?$result['fare_alipay_pay_num']:0,
				'name'  => "支付宝",
			),
			array(
				'value' => $result['fare_weixin_pay_num']?$result['fare_weixin_pay_num']:0,
				'name'  => "微信",
			),
			array(
				'value' => $result['fare_bank_pay_num']?$result['fare_bank_pay_num']:0,
				'name'  => "网银",
			),
		);
		//流量支付方式
		$flow_pay['title'] = array(
			'text'    => "流量支付方式",   //主标题
			'subtext' => "",   //副标题
			'x'       => "center",       //标题位置(left,right,center)
		);

		$flow_pay['legend'] =array(
			0 =>'余额',
			1 =>'支付宝',
			2 =>'微信',
			3 =>'网银',
		);
		$flow_pay["x_title"] = "left";      //legend 位置(left,right,center)
		$flow_pay["name"]    = "流量支付方式";  //图表用途名称
		$flow_pay['data'] = array(//数据
			array(
				'value' => $result['flow_balance_pay_num']?$result['flow_balance_pay_num']:0,
				'name'  => "余额",
			),
			array(
				'value' => $result['flow_alipay_pay_num']?$result['flow_alipay_pay_num']:0,
				'name'  => "支付宝",
			),
			array(
				'value' => $result['flow_weixin_pay_num']?$result['flow_weixin_pay_num']:0,
				'name'  => "微信",
			),
			array(
				'value' => $result['flow_bank_pay_num']?$result['flow_bank_pay_num']:0,
				'name'  => "网银",
			),
		);
				
		//话费下单渠道
		$fare_type['title'] = array(
			'text'    => "话费下单渠道",   //主标题
			'subtext' => "",   //副标题
			'x'       => "center",       //标题位置(left,right,center)
		);

		$fare_type['legend'] =array(
			0 =>'pc',
			1 =>'wap',
			2 =>'ios',
			3 =>'android',
		);
		$fare_type["x_title"] = "left";      //legend 位置(left,right,center)
		$fare_type["name"]    = "话费下单渠道";  //图表用途名称
		$fare_type['data'] = array(//数据
			array(
				'value' => $result['fare_pc_recharge_num']?$result['fare_pc_recharge_num']:0,
				'name'  => "pc",
			),
			array(
				'value' => $result['fare_wap_recharge_num']?$result['fare_wap_recharge_num']:0,
				'name'  => "wap",
			),
			array(
				'value' => $result['fare_ios_recharge_num']?$result['fare_ios_recharge_num']:0,
				'name'  => "ios",
			),
			array(
				'value' => $result['fare_android_recharge_num']?$result['fare_android_recharge_num']:0,
				'name'  => "android",
			),
		);
		
		//流量下单渠道
		$flow_type['title'] = array(
			'text'    => "流量下单渠道",   //主标题
			'subtext' => "",   //副标题
			'x'       => "center",       //标题位置(left,right,center)
		);

		$flow_type['legend'] =array(
			0 =>'pc',
			1 =>'wap',
			2 =>'ios',
			3 =>'android',
		);
		$flow_type["x_title"] = "left";      //legend 位置(left,right,center)
		$flow_type["name"]    = "流量下单渠道";  //图表用途名称
		$flow_type['data'] = array(//数据
			array(
				'value' => $result['flow_pc_recharge_num']?$result['flow_pc_recharge_num']:0,
				'name'  => "pc",
			),
			array(
				'value' => $result['flow_wap_recharge_num']?$result['flow_wap_recharge_num']:0,
				'name'  => "wap",
			),
			array(
				'value' => $result['flow_ios_recharge_num']?$result['flow_ios_recharge_num']:0,
				'name'  => "ios",
			),
			array(
				'value' => $result['flow_android_recharge_num']?$result['flow_android_recharge_num']:0,
				'name'  => "android",
			),
		);
		
		//话费成功充值运营商
		$fare['title'] = array(
			'text'    => "话费成功充值运营商",   //主标题
			'subtext' => "",   //副标题
			'x'       => "center",       //标题位置(left,right,center)
		);

		$fare['legend'] =array(
			0 =>'移动',
			1 =>'联通',
			2 =>'电信',
		);
		$fare["x_title"] = "left";      //legend 位置(left,right,center)
		$fare["name"]    = "话费运营商";  //图表用途名称
		$fare['data'] = array(//数据
			array(
				'value' => $result['fare_move_operator_num']?$result['fare_move_operator_num']:0,
				'name'  => "移动",
			),
			array(
				'value' => $result['fare_unicom_operator_num']?$result['fare_unicom_operator_num']:0,
				'name'  => "联通",
			),
			array(
				'value' => $result['fare_telecom_operator_num']?$result['fare_telecom_operator_num']:0,
				'name'  => "电信",
			),
		);
		//流量成功充值运营商
		$flow['title'] = array(
			'text'    => "流量成功充值运营商",   //主标题
			'subtext' => "",   //副标题
			'x'       => "center",       //标题位置(left,right,center)
		);

		$flow['legend'] =array(
			0 =>'移动',
			1 =>'联通',
			2 =>'电信',
		);
		$flow["x_title"] = "left";      //legend 位置(left,right,center)
		$flow["name"]    = "流量运营商";  //图表用途名称
		$flow['data'] = array(//数据
			array(
				'value' => $result['flow_move_operator_num']?$result['flow_move_operator_num']:0,
				'name'  => "移动",
			),
			array(
				'value' => $result['flow_unicom_operator_num']?$result['flow_unicom_operator_num']:0,
				'name'  => "联通",
			),
			array(
				'value' => $result['flow_telecom_operator_num']?$result['flow_telecom_operator_num']:0,
				'name'  => "电信",
			),
		);
		
		$this->assign('day',$test);
        $this->assign('fare',$fare);
        $this->assign('flow',$flow);		
        $this->assign('fare_type',$fare_type);	
        $this->assign('fare_pay',$fare_pay);
        $this->assign('flow_type',$flow_type);
        $this->assign('flow_pay',$flow_pay);
		$this->assign('last1',$last1);
        $this->assign('ratio',$ratio);		
		$this->assign('result',$result);
		$this->assign('success_rate',$success_rate);
		$this->display();
    }
    /**
    * 列表
    */
    public function detail($param=null){
    	$this->_index();
		$btn=array('title'=>'操作','type'=>'html','html'=>'<div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">详情</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
        $this->assign('fields',$this->plist(null,$btn));
		$this->display();
    }
    /**
     * 详情
     * @param int $_GET['id']	订单ID
     */
    public function view(){
        $do = M('mobile_orders_totals');
		$rs = $do->where(['id' => I('get.id')])->find();
		//话费成功充值运营商
		$type1['title'] = array(
			'text'    => "话费成功充值运营商",   //主标题
			'subtext' => "",   //副标题
			'x'       => "center",       //标题位置(left,right,center)
		);

		$type1['legend'] =array(
			0 =>'移动',
			1 =>'联通',
			2 =>'电信',
		);
		$type1["x_title"] = "left";      //legend 位置(left,right,center)
		$type1["name"]    = "运营商";  //图表用途名称
		$type1['data'] = array(//数据

			array(
				'value' => $rs['fare_move_operator_num']?$rs['fare_move_operator_num']:0,
				'name'  => "移动",
			),
			array(
				'value' => $rs['fare_unicom_operator_num']?$rs['fare_unicom_operator_num']:0,
				'name'  => "联通",
			),
			array(
				'value' => $rs['fare_telecom_operator_num']?$rs['fare_telecom_operator_num']:0,
				'name'  => "电信",
			),
		);
		
		//话费支付方式
		$type3['title'] = array(
			'text'    => "话费充值支付方式",   //主标题
			'subtext' => "",   //副标题
			'x'       => "center",       //标题位置(left,right,center)
		);

		$type3['legend'] =array(
			0 =>'余额',
			1 =>'支付宝',
			2 =>'微信',
			3 =>'网银',
		);
		$type3["x_title"] = "left";      //legend 位置(left,right,center)
		$type3["name"]    = "支付方式";  //图表用途名称
		$type3['data'] = array(//数据
			array(
				'value' => $rs['fare_balance_pay_num']?$rs['fare_balance_pay_num']:0,
				'name'  => "余额",
			),
			array(
				'value' => $rs['fare_alipay_pay_num']?$rs['fare_alipay_pay_num']:0,
				'name'  => "支付宝",
			),
			array(
				'value' => $rs['fare_weixin_pay_num']?$rs['fare_weixin_pay_num']:0,
				'name'  => "微信",
			),
			array(
				'value' => $rs['fare_bank_pay_num']?$rs['fare_bank_pay_num']:0,
				'name'  => "网银",
			),
		);
		
		//话费充值下单渠道
		$type2['title'] = array(
			'text'    => "话费充值下单渠道",   //主标题
			'subtext' => "",   //副标题
			'x'       => "center",       //标题位置(left,right,center)
		);

		$type2['legend'] =array(
			0 =>'pc',
			1 =>'wap',
			2 =>'ios',
			3 =>'android',
		);
		$type2["x_title"] = "left";      //legend 位置(left,right,center)
		$type2["name"]    = "下单渠道";  //图表用途名称
		$type2['data'] = array(//数据
			array(
				'value' => $rs['fare_pc_recharge_num']?$rs['fare_pc_recharge_num']:0,
				'name'  => "pc",
			),
			array(
				'value' => $rs['fare_wap_recharge_num']?$rs['fare_wap_recharge_num']:0,
				'name'  => "wap",
			),
			array(
				'value' => $rs['fare_ios_recharge_num']?$rs['fare_ios_recharge_num']:0,
				'name'  => "ios",
			),
			array(
				'value' => $rs['fare_android_recharge_num']?$rs['fare_android_recharge_num']:0,
				'name'  => "android",
			),
		);	


		//流量成功充值运营商
		$type4['title'] = array(
			'text'    => "流量成功充值运营商",   //主标题
			'subtext' => "",   //副标题
			'x'       => "center",       //标题位置(left,right,center)
		);

		$type4['legend'] =array(
			0 =>'移动',
			1 =>'联通',
			2 =>'电信',
		);
		$type4["x_title"] = "left";      //legend 位置(left,right,center)
		$type4["name"]    = "运营商";  //图表用途名称
		$type4['data'] = array(//数据

			array(
				'value' => $rs['flow_move_operator_num']?$rs['flow_move_operator_num']:0,
				'name'  => "移动",
			),
			array(
				'value' => $rs['flow_unicom_operator_num']?$rs['flow_unicom_operator_num']:0,
				'name'  => "联通",
			),
			array(
				'value' => $rs['flow_telecom_operator_num']?$rs['flow_telecom_operator_num']:0,
				'name'  => "电信",
			),
		);
		
		//流量支付方式
		$type5['title'] = array(
			'text'    => "流量充值支付方式",   //主标题
			'subtext' => "",   //副标题
			'x'       => "center",       //标题位置(left,right,center)
		);

		$type5['legend'] =array(
			0 =>'余额',
			1 =>'支付宝',
			2 =>'微信',
			3 =>'网银',
		);
		$type5["x_title"] = "left";      //legend 位置(left,right,center)
		$type5["name"]    = "支付方式";  //图表用途名称
		$type5['data'] = array(//数据
			array(
				'value' => $rs['flow_balance_pay_num']?$rs['flow_balance_pay_num']:0,
				'name'  => "余额",
			),
			array(
				'value' => $rs['flow_alipay_pay_num']?$rs['flow_alipay_pay_num']:0,
				'name'  => "支付宝",
			),
			array(
				'value' => $rs['flow_weixin_pay_num']?$rs['flow_weixin_pay_num']:0,
				'name'  => "微信",
			),
			array(
				'value' => $rs['flow_bank_pay_num']?$rs['flow_bank_pay_num']:0,
				'name'  => "网银",
			),
		);
		
		//流量充值下单渠道
		$type6['title'] = array(
			'text'    => "流量充值下单渠道",   //主标题
			'subtext' => "",   //副标题
			'x'       => "center",       //标题位置(left,right,center)
		);

		$type6['legend'] =array(
			0 =>'pc',
			1 =>'wap',
			2 =>'ios',
			3 =>'android',
		);
		$type6["x_title"] = "left";      //legend 位置(left,right,center)
		$type6["name"]    = "下单渠道";  //图表用途名称
		$type6['data'] = array(//数据
			array(
				'value' => $rs['flow_pc_recharge_num']?$rs['flow_pc_recharge_num']:0,
				'name'  => "pc",
			),
			array(
				'value' => $rs['flow_wap_recharge_num']?$rs['flow_wap_recharge_num']:0,
				'name'  => "wap",
			),
			array(
				'value' => $rs['flow_ios_recharge_num']?$rs['flow_ios_recharge_num']:0,
				'name'  => "ios",
			),
			array(
				'value' => $rs['flow_android_recharge_num']?$rs['flow_android_recharge_num']:0,
				'name'  => "android",
			),
		);		
        $this->assign('type1',$type1);        
        $this->assign('type2',$type2);        
        $this->assign('type3',$type3);
        $this->assign('type4',$type4);        
        $this->assign('type5',$type5);        
        $this->assign('type6',$type6);
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