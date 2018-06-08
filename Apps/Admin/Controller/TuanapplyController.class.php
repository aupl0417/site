<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class TuanapplyController extends CommonModulesController {
	protected $name 			='拼团购 - 申请';	//控制器名称
    protected $formtpl_id		=218;			//表单模板ID
    protected $fcfg				=array();		//表单配置
    protected $do 				='';			//实例化模型
    protected $map				=array();		//where查询条件
    //private $token = '';
	public $api_cfg = array(
		'appid'         => 11,
        'access_key'    => 'a322182508a9200fae7cdceeb29bc049',
        'secret_key'    => 'e1061393a4ddd0b805d92da83477be48',
        'sign_code'     => '53d45f1a00761ef0bae02fdf73d3b284',
	);

	/**
    * 初始化
    */
	public function _initialize(){
		parent::_initialize();

    	//初始化表单模板
    	$this->_initform();

    	//dump($this->fcfg);
		
		$res = $this->doApi2('/Auth/token',$this->api_cfg);
        if($res['code'] == 1){
            $this->token = $res['data']['token'];
        }else{
        	var_dump($res);
            echo $res['msg'] . '222';
            exit();
        }
	}

    /**
    * 列表
    */
    public function index($param=null){
    	$this->_index();
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<div data-url="'.__CONTROLLER__.'/audit/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-audit">审核</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
    	$this->assign('fields',$this->plist(null,$btn));
		$this->display();
    }

    /**
    * 添加记录
    */
    public function add($param=null){
    	# $this->display();
    }
	
	/**
	* 保存新增记录
	*/
	public function add_save($param=null){
		# $result=$this->_add_save();

		# $this->ajaxReturn($result);
	}

	/**
	* 修改记录
	*/
	public function edit($param=null){
		# $this->_edit();
		# $this->display();
	}

	/**
	* 保存修改记录
	*/
	public function edit_save($param=null){
		# $result=$this->_edit_save();

		# $this->ajaxReturn($result);
	}

	/**
	 * 审核
	 */
	public function audit(){
		$model = D('Admin/Tuanapply218Relation');
		$one = $model->relation(true)->find(I('get.id'));
		
		$one['category_name'] = M('tuan_category')->where(['id'=>$one['category_id']])->getField('category_name');
		
		$one['goods'] = D('GoodsRelation')->relationField('goods_attr_list','id,attr_name,num')->relationField('user','id')->relationField('shop','id')->relation(true)->find($one['goods_id']);
		
		foreach($one['tuan_apply_list'] as $k=>$v){
			foreach($one['goods']['attr_list'] as $va){
				if($v['goods_attr_list_id'] == $va['id']){
					$one['tuan_apply_list'][$k]['attr_name'] = $va['attr_name'];
					$one['tuan_apply_list'][$k]['attr_num'] = $va['num'];
				}
			}
		}
		
		//查询商品信息
		//dump($one);
		//查询店铺信息
		

		$this->assign('one',$one);
		$this->assign('rs',$one);
		$this->display();
	}

	/**
	 * 审核 保存
	 */
	public function audit_save(){
		IS_POST or die();
		$data = I('post.');
		$data['admin_id'] = session('admin.id');
		$data['token'] = $this->token;
		$res = $this->doApi2('/TuanApply/audit', $data);
		$this->ajaxReturn($res);
	}

	/**
	* 删除选中记录
	*/
	public function delete_select($param=null){
		# $result=$this->_delete_select();
		# $this->ajaxReturn($result);
	}

	/**
	* 批量更改状态
	*/
	public function active_change_select($param=null){
		# $result=$this->_active_change_select();

		# $this->ajaxReturn($result);		
	}
}