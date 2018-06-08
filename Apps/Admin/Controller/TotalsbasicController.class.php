<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class TotalsbasicController extends CommonModulesController {
	protected $name 			='基础统计';	//控制器名称
    protected $formtpl_id		=207;			//表单模板ID
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
        //当前用户
        $result['total_member'] = M('user')->count();
        //当前正常营业店铺
        $result['normal_store'] = M('shop')->where(['_string' => 'status=1'])->count();
        //当前在线出售商品
        $result['online_goods_num']   = M('goods')->where(["_string" => "status=1"])->count();
        //当前品牌推广总数
        $result['brand_num']  = M('brand_ext')->count();
        //当前评价总数
        $result['comment_total_num']  = M('orders_goods_comment')->where(['status' => 1])->count();
        
        //基础统计表
        $totals_basic  = M('totals_basic')->field('member,open_store_user,open_store_success,close_store,goods_num,illegal_goods_num,comment_num')->order("day desc")->limit(2)->select();
		//推广统计表
		$totals_promotion  = M('totals_promotion')->field('brand_num,day_brand_money')->order("day desc")->limit(2)->select();
		
		//新增用户比值
		$ratio['member'] = $this->ratio($totals_basic[1]['member'],$totals_basic[0]['member']);
		//新增申请开店比值
		$ratio['apply_open'] = $this->ratio($totals_basic[1]['open_store_user'],$totals_basic[0]['open_store_user']);
		//新增开店成功比值
		$ratio['success_open'] = $this->ratio($totals_basic[1]['open_store_success'],$totals_basic[0]['open_store_success']);
		//被关闭店铺比值
		$ratio['close_store'] = $this->ratio($totals_basic[1]['close_store'],$totals_basic[0]['close_store']);
		//新增商品数量比值
		$ratio['goods_num'] = $this->ratio($totals_basic[1]['goods_num'],$totals_basic[0]['goods_num']);
		//违规商品数量比值
		$ratio['illegal_goods_num'] = $this->ratio($totals_basic[1]['illegal_goods_num'],$totals_basic[0]['illegal_goods_num']);
		//新增品牌推广数量比值
		$ratio['brand_num'] = $this->ratio($totals_promotion[1]['brand_num'],$totals_promotion[0]['brand_num']);
		//新增品牌推广总额比值
		$ratio['day_brand_money'] = $this->ratio($totals_promotion[1]['day_brand_money'],$totals_promotion[0]['day_brand_money']);
		//新增评价比值
		$ratio['comment_num'] = $this->ratio($totals_basic[1]['comment_num'],$totals_basic[0]['comment_num']);
		
        $this->assign('result',$result);
        $this->assign('ratio',$ratio);
        $this->assign('totals_basic',$totals_basic);
        $this->assign('totals_promotion',$totals_promotion);
		
      
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
		//dump($pagelist);
        $this->assign('pagelist',$pagelist);
		$btn=array('title'=>'操作','type'=>'html','html'=>'<div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">详情</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
        $this->assign('fields',$this->plist(null,$btn));
		$this->display();
	}
    /**
     * 详情
     * @param int $_GET['id']	订单ID
     */
    public function view(){
        $do=M('totals_basic');
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