<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class TotalspromotionController extends CommonModulesController {
	protected $name 			='促销统计';	//控制器名称
    protected $formtpl_id		=209;			//表单模板ID
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
        $do = M('activity_participate');
        $sql = 'select count(*) as activity_total_num,sum(calc_before_money-calc_after_money) as activity_total_money from ylh_activity_participate where status=1';
		$a = M('activity_participate')->field('count(*) as activity_total_num')->where(['status'=>1])->find();
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
		$totals_promotion = M('totals_promotion')->field('activity_num,activity_pay_num,day_activity_money,day_average_activity,coupon_num,day_coupon_total,use_coupon_num,use_coupon_money,ad_num,ad_money,ad_price_unit,sucai_num')->order("day desc")->limit(2)->select();

		//活动下单比值
		$ratio['activity_num'] = $this->ratio($totals_promotion[1]['activity_num'],$totals_promotion[0]['activity_num']);
		//每天活动成交比值
		$ratio['activity_pay_num'] = $this->ratio($totals_promotion[1]['activity_pay_num'],$totals_promotion[0]['activity_pay_num']);
		$ratio['day_activity_money'] = $this->ratio($totals_promotion[1]['day_activity_money'],$totals_promotion[0]['day_activity_money']);
		$ratio['day_average_activity'] = $this->ratio($totals_promotion[1]['day_average_activity'],$totals_promotion[0]['day_average_activity']);
		//每天优惠券比值
		$ratio['coupon_num'] = $this->ratio($totals_promotion[1]['coupon_num'],$totals_promotion[0]['coupon_num']);
		$ratio['day_coupon_total'] = $this->ratio($totals_promotion[1]['day_coupon_total'],$totals_promotion[0]['day_coupon_total']);
		$ratio['use_coupon_num'] = $this->ratio($totals_promotion[1]['use_coupon_num'],$totals_promotion[0]['use_coupon_num']);
		$ratio['use_coupon_money'] = $this->ratio($totals_promotion[1]['use_coupon_money'],$totals_promotion[0]['use_coupon_money']);
		//每天广告投放比值
		$ratio['ad_num'] = $this->ratio($totals_promotion[1]['ad_num'],$totals_promotion[0]['ad_num']);
		$ratio['ad_money'] = $this->ratio($totals_promotion[1]['ad_money'],$totals_promotion[0]['ad_money']);
		$ratio['ad_price_unit'] = $this->ratio($totals_promotion[1]['ad_price_unit'],$totals_promotion[0]['ad_price_unit']);
		//每天素材比值
		$ratio['sucai_num'] = $this->ratio($totals_promotion[1]['sucai_num'],$totals_promotion[0]['sucai_num']);
		
        $this->assign('result',$result);
        $this->assign('totals_promotion',$totals_promotion);
        $this->assign('ratio',$ratio);
        
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
        $do=M('totals_promotion');
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