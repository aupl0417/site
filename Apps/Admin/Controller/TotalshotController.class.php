<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class TotalshotController extends CommonModulesController {
	protected $name 			='热销统计';	//控制器名称
    protected $formtpl_id		=210;			//表单模板ID
    protected $fcfg				=array();		//表单配置
    protected $do 				='';			//实例化模型
    protected $map				=array();		//where查询条件
	private $day;

    /**
    * 初始化
    */
    public function _initialize() {
    	parent::_initialize();

    	//初始化表单模板
    	$this->_initform();

    	//获取提交的日期
		$select_day = strtotime(I('get.day'));
		if($select_day){
			$this->day =  date('Y-m-d',$select_day);
		}else{
			$this->day  =   date('Y-m-d',time()-86400);
		}
		$this->day7  =   date('Y-m-d',strtotime($this->day)-86400*6);
    }

    /**
    * 热销统计
    */
    public function index($param=null){
		if(isset($_GET['order'])){
			$order = I('get.order').' desc';
		}else{
			$order = 'id asc';
		}
		$list = M('totals_hot')->cache(true)->field('goods_name,goods_price,order_num,buy_num,buy_money,order_buy_percen')->where(['day'=>$this->day])->order($order)->select();
		foreach($list as $k => $v){
			$list[$k]['order_buy_percen'] = $v['order_buy_percen']*100;
		}
		$this->assign('list',$list);
		$this->display();
    }
	/**
    * 热门类别
    */
	public function cate(){
		$list = M('')->table('ylh_orders_goods as z')->field('z.goods_id,f.category_id,count(z.id) as total_orders_pay_num,sum(z.num) as total_goods_sale_num,sum(z.total_price) as total_money_pay')->join('ylh_goods as f ON z.goods_id = f.id','left')->where(['z.goods_name'=>['notlike','%运费%'],'_string'=>'date_format(z.atime,"%Y-%m-%d")>"'.$this->day7.'" and date_format(z.atime,"%Y-%m-%d")<="'.$this->day.'"'])->group('f.category_id')->order('total_orders_pay_num desc,total_goods_sale_num desc')->limit(10)->select();
		foreach($list as $k => $v){
			$list[$k]['cate_name'] = M('goods_category')->where(['id'=>$v['category_id']])->getField('category_name');
		}
		$this->assign('list',$list);
		$this->display();
		
	}
	/**
    * 热门店铺
    */
	public function shop($param=null){
		$list = M('totals_shop')->cache(true)->field('shop_id,sum(orders_pay_num) as total_orders_pay_num,sum(goods_sale_num) as total_goods_sale_num,sum(money_pay) as total_money_pay')->where(['_string'=>'date_format(day,"%Y-%m-%d")>"'.$this->day7.'" and date_format(day,"%Y-%m-%d")<="'.$this->day.'"'])->group('shop_id')->order('total_orders_pay_num desc,total_goods_sale_num desc')->limit(10)->select();
		foreach($list as $k=>$v){
			if($v['total_orders_pay_num'] == 0 && $v['total_goods_sale_num'] == 0 && $v['total_money_pay'] == 0){
				unset($list[$k]);
			}else{
				$list[$k]['shop_name'] = M('shop')->where(['id'=>$v['shop_id']])->getField('shop_name');
			}
		}
		
		$this->assign('list',$list);
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