<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class ExpresstplController extends CommonModulesController {
	protected $name 			='运费模板';	//控制器名称
    protected $formtpl_id		=156;			//表单模板ID
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
        if(session('admin.id') != 100180788) $map['uid'] = ['neq',692355];
    	$this->_index(['map' => $map]);
		$this->display();
    }

    /**
    * 添加记录
    */
    public function add($param=null){
    	$this->display();
    }
	
	# /**
	# * 保存新增记录
	# */
	# public function add_save($param=null){
	# 	$result=$this->_add_save();

	# 	$this->ajaxReturn($result);
	# }

	/**
	* 修改记录
	*/
	public function edit($param=null){
		$do=D('Common/ExpressTplRelation');
		$rs=$do->relation(true)->where(array('id' => I('get.id')))->find();
		$area=$this->cache_table('area');

		foreach($rs['express_area'] as $i =>$v){
			$ids=explode(',', $v['city_ids']);
			foreach($ids as $c){
				$v['city'][]=$area[$c];
			}

			$rs['express_area'][$i]['city']=implode(',', $v['city']);
		}	

		if($rs['town']) $id=$rs['town'];
		elseif($rs['district']) $id=$rs['district'];
		else $id=$rs['city'];

		$rs['select_city']=nav_sort(array('table'=>'area','icon' => ' > ','field' =>'id,sid,a_name','key' => 'a_name','id' =>$id));

		$this->assign('rs',$rs);

		$this->display();
	}

	/**
	* 保存修改记录
	*/
	public function edit_save($param=null){
		C('TOKEN_ON',false);
		$do=D('Common/ExpressTpl');

		
		$data =$do->create();

		foreach($_POST['express_city_ids'] as $key=>$val){
			$tmp=array();
			$tmp = array(
					'type'			=>1,
					'uid'			=>$_POST['uid'],
					'city_ids'		=>$val,
					'first_unit'	=>$_POST['express_first_unit'][$key],
					'first_price'	=>$_POST['express_first_price'][$key],
					'next_unit'		=>$_POST['express_next_unit'][$key],
					'next_price'	=>$_POST['express_next_price'][$key],
				
				);
			if(!empty($_POST['express_id'][$key])) $tmp['id']=$_POST['express_id'][$key];

			$data['express_area'][]=$tmp;
		}

		foreach($_POST['ems_city_ids'] as $key=>$val){
			$tmp=array();
			$tmp = array(
					'type'			=>2,
					'uid'			=>$_POST['uid'],
					'city_ids'		=>$val,
					'first_unit'	=>$_POST['ems_first_unit'][$key],
					'first_price'	=>$_POST['ems_first_price'][$key],
					'next_unit'		=>$_POST['ems_next_unit'][$key],
					'next_price'	=>$_POST['ems_next_price'][$key],
				);
			if(!empty($_POST['ems_id'][$key])) $tmp['id']=$_POST['ems_id'][$key];

			$data['express_area'][]=$tmp;
		}

		$do=D('Common/ExpressTplRelation');
		$res=$do->relation(true)->save($data);

		if($res!==false) $this->ajaxReturn(array('code'=>1,'msg'=>'操作成功！','status'=>'success'));
		else $this->ajaxReturn(array('code'=>0,'msg' =>'操作失败' ,'status'=>'warning'));
	}

	
	
	/**
	* 修改地区运费
	*/
	public function area_edit(){

		$this->display();
	}

	

	public function city(){
		$map['sid']=I('sid')?I('sid'):0;

        $cache_name='area_'.$map['sid'];
        $do = M('area');
        $rs = $do->cache(true,C('CACHE_LEVEL.L'))->where($map)->order('sort asc')->field('id,a_name,a_postcode')->select();
        $this->assign('city',$rs);
        
		$this->display();
	}

	public function get_city(){
		$map['sid']=I('sid')?I('sid'):0;

        $cache_name='area_'.$map['sid'];
        $do = M('area');
        $rs = $do->cache(true,C('CACHE_LEVEL.L'))->where($map)->order('sort asc')->field('id,a_name,a_postcode')->select();
        $this->assign('city',$rs);

		$this->ajaxReturn(['code'=> 1,'data' => $rs]);
	}


	/**
	* 缓存数据表数据
	*/
	public function cache_table($table){
		$cache_table=[
			'admin_sort'		=>'id,group_name',
			'api_category'		=>'id,category_name',
			'area'				=>'id,a_name',
			'config_sort'		=>'id,name',
			'express_category'	=>'id,category_name',
			'express_company'	=>'id,sub_name',
			'goods_cfg'			=>'id,cfg_name',
			'help_category'		=>'id,category_name',
			'msg_category'		=>'id,category_name',
			'modules'			=>'id,module_name',
			'news_category'		=>'id,category_name',
			'search_keyword'	=>'id,keyword',
			'shop_type'			=>'id,type_name',
			'shop_notdomain'	=>'id,domain',
			'shop_notname'		=>'id,name',
			'user_level'		=>'id,level_name',
			'goods_category'	=>'id,category_name',
		];

		$list=S('table_'.$table);
		if(empty($list)){
			$do=M($table);
			$list=$do->cache('table_'.$table,0)->getField($cache_table[$table],true);
		}

		return $list;
	}
}