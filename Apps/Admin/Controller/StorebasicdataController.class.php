<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class StorebasicdataController extends CommonModulesController {
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
        //用户
        $do = M('user');
        $result['total_member'] = $do->count();
        //店铺
        $do = M('shop');
        $result['normal_store'] = $do->where(['_string' => 'status=1'])->count();
        //商品
        $do = M('goods');
        $result['online_goods_num']   = $do->where(["_string" => "status=1"])->count();
        //品牌推广总数
        $do=M('brand_ext');
        $result['brand_num']  = $do->count();
        //累计评价
        $do=M('orders_goods_comment');
        $result['comment_total_num']  = $do->where(['status' => 1])->count();
        
        //统计表
        $do=M('totals');
        $totals  = $do->order("day desc")->limit(2)->select();
        //用户
        $yestoday = $totals[1]['member']==0?"1":$totals[1]['member'];
        $rate['member'] =  number_format(abs($totals[0]['member']-$totals[1]['member']) /$yestoday*100,1);
        if ($rate['member']=='0'){
            $rate['member_color'] = '';
        }else{
            $rate['member_color'] = ($totals[0]['member']-$totals[1]['member'])>0?"text-success":"text-danger";
        }
        $rate['member_sign'] = ($totals[0]['member']-$totals[1]['member'])>0?"&uarr":"&darr";
        //申请开店用户
        $yestoday = $totals[1]['open_store_user']==0?"1":$totals[1]['open_store_user'];
        $rate['apply_open'] =  number_format(abs($totals[0]['open_store_user']-$totals[1]['open_store_user'])/$yestoday*100,1);
        if ($rate['apply_open']=='0'){
            $rate['apply_open_color'] = '';
        }else{
            $rate['apply_open_color'] = ($totals[0]['open_store_user']-$totals[1]['open_store_user'])>0?"text-success":"text-danger";
        }
        $rate['apply_open_sign'] = ($totals[0]['open_store_user']-$totals[1]['open_store_user'])>0?"&uarr":"&darr";
        //开店成功用户
        $yestoday = $totals[1]['open_store_success']==0?"1":$totals[1]['open_store_success'];
        $rate['success_open'] =  number_format(abs($totals[0]['open_store_success']-$totals[1]['open_store_success'])/$yestoday*100,1);
        $rate['success_open_sign'] = ($totals[0]['open_store_success']-$totals[1]['open_store_success'])>0?"&uarr":"&darr";
        if ($rate['success_open']=='0'){
            $rate['success_open_color'] = '';
        }else{
            $rate['success_open_color'] = ($totals[0]['open_store_success']-$totals[1]['open_store_success'])>0?"text-success":"text-danger";
        }
        //关闭店铺用户
        $yestoday = $totals[1]['close_store']==0?"1":$totals[1]['close_store'];
        $rate['close_store'] =  number_format(abs($totals[0]['close_store']-$totals[1]['close_store'])/$yestoday*100,1);
        if ($rate['close_store']=='0'){
            $rate['close_store_color'] = '';
        }else{
            $rate['close_store_color'] = ($totals[0]['close_store']-$totals[1]['close_store'])>0?"text-success":"text-danger";
        }
        $rate['close_store_sign'] = ($totals[0]['close_store']-$totals[1]['close_store'])>0?"&uarr":"&darr";
        //新增商品数量
        $yestoday = $totals[1]['goods_num']==0?"1":$totals[1]['goods_num'];
        $rate['goods_num'] =  number_format(abs($totals[0]['goods_num']-$totals[1]['goods_num']) /$yestoday*100,1);
        if ($rate['goods_num']=='0'){
            $rate['goods_num_color'] = '';
        }else{
            $rate['goods_num_color'] = ($totals[0]['goods_num']-$totals[1]['goods_num'])>0?"text-success":"text-danger";
        }
        $rate['goods_num_sign'] = ($totals[0]['goods_num']-$totals[1]['goods_num'])>0?"&uarr":"&darr";
        //违规商品数量
        $yestoday = $totals[1]['illegal_goods_num']==0?"1":$totals[1]['illegal_goods_num'];
        $rate['illegal_goods_num'] =  number_format(abs($totals[0]['illegal_goods_num']-$totals[1]['illegal_goods_num']) /$yestoday*100,1);
        if ($rate['illegal_goods_num']=='0'){
            $rate['illegal_goods_num_color'] = '';
        }else{
            $rate['illegal_goods_num_color'] = ($totals[0]['illegal_goods_num']-$totals[1]['illegal_goods_num'])>0?"text-success":"text-danger";
        }
        $rate['illegal_goods_num_sign'] = ($totals[0]['illegal_goods_num']-$totals[1]['illegal_goods_num'])>0?"&uarr":"&darr";
        //新增品牌推广
        $yestoday = $totals[1]['brand_num']==0?"1":$totals[1]['brand_num'];
        $rate['brand_num'] =  number_format(abs($totals[0]['brand_num']-$totals[1]['brand_num']) /$yestoday*100,1);
        if ($rate['brand_num']=='0'){
            $rate['brand_num_color'] = '';
        }else{
            $rate['brand_num_color'] = ($totals[0]['brand_num']-$totals[1]['brand_num'])>0?"text-success":"text-danger";
        }
        $rate['brand_num_sign'] = ($totals[0]['brand_num']-$totals[1]['brand_num'])>0?"&uarr":"&darr";
        //每天新增品牌推广总额
        $yestoday = $totals[1]['day_brand_money']==0?"1":$totals[1]['day_brand_money'];
        $rate['day_brand_money'] =  number_format(abs($totals[0]['day_brand_money']-$totals[1]['day_brand_money']) /$yestoday*100,1);
        if ($rate['day_brand_money']=='0'){
            $rate['day_brand_money_color'] = '';
        }else{
            $rate['day_brand_money_color'] = ($totals[0]['day_brand_money']-$totals[1]['day_brand_money'])>0?"text-success":"text-danger";
        }
        $rate['day_brand_money_sign'] = ($totals[0]['day_brand_money']-$totals[1]['day_brand_money'])>0?"&uarr":"&darr";
        //新增评价
        $yestoday = $totals[1]['comment_num']==0?"1":$totals[1]['comment_num'];
        $rate['comment_num'] =  number_format(abs($totals[0]['comment_num']-$totals[1]['comment_num']) /$yestoday*100,1);
        if ($rate['comment_num']=='0'){
            $rate['comment_num_color'] = '';
        }else{
            $rate['comment_num_color'] = ($totals[0]['comment_num']-$totals[1]['comment_num'])>0?"text-success":"text-danger";
        }
        $rate['comment_num_sign'] = ($totals[0]['comment_num']-$totals[1]['comment_num'])>0?"&uarr":"&darr";
        
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