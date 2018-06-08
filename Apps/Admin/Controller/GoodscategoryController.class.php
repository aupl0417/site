<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class GoodscategoryController extends CommonModulesController {
	protected $name 			='商品类目';	//控制器名称
    protected $formtpl_id		=85;			//表单模板ID
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
    	$this->_category_list();
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
	public function active_change_select(){
		$result=$this->_active_change_select();

		$this->ajaxReturn($result);		
	}

	/**
	* 排序
	*/
	public function setsort(){
		$result=$this->_setsort();
		$this->ajaxReturn($result);
	}

	/**
	* 类目转移
	*/
	public function sid_change_select(){
		$result=$this->_sid_change_select();
		$this->ajaxReturn($result);
	}

	/**
	 * 属性管理
	 */
	public function attr_manage(){
		$list=get_category([
				'table'		=>'goods_attr',
				'level'		=>2,
				'field'		=>'id,sid,status,attr_name,category_id',
				'map'		=>[['category_id' => I('get.sid')]],
			]);

		//dump($list);
		$this->assign('list',$list);
		$this->display();
	}

	/**
	* 排序
	*/
	public function attr_setsort(){
		$do=M('goods_attr');
		foreach(I('post.ids') as $key=>$val){
			$do->where('id='.$val)->setField('sort',$key);
		}		

		$this->ajaxReturn(['status' => 'success']);
	}

	/**
	* 删除属性
	*/
	public function attr_delete(){
		$ids=sortid([
				'table' 	=>'goods_attr',
				'sid'		=>I('get.id'),
			]);

		if(M('goods_attr')->where(['id' => ['in',$ids]])->delete()){
			$this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);
		}else $this->ajaxReturn(['status' => 'warning','msg' => '操作失败！']);
	}

	/**
	* 修改属性
	*/
	public function attr_edit(){
		$rs=M('goods_attr')->where(['id' => I('get.id')])->find();
		$this->assign('rs',$rs);
		$this->display();
	}
	
	/**
	* 保存修改
	*/
	public function attr_edit_save(){
		$do=D('Goodsattr95');

		if(!$do->create()) $this->ajaxReturn(['status' => 'warning','msg' => '操作失败！'. $do->getError()]);
		if(!$do->save()) $this->ajaxReturn(['status' => 'warning','msg' =>'操作失败！']);
		else $this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);
	}

	/**
	* 添加属性
	*/
	public function attr_add(){
		$this->display();
	}

	/**
	* 修改保存
	*/
	public function attr_add_save(){
		$do=D('Goodsattr95');

		if(!$do->create()) $this->ajaxReturn(['status' => 'warning','msg' => '操作失败！'. $do->getError()]);
		if(!$do->add()) $this->ajaxReturn(['status' => 'warning','msg' =>'操作失败！']);
		else $this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);
	}	

	
	/**
	+------------------------------------------------------------
	+ 参数
	+------------------------------------------------------------
	*/

	/**
	 * 参数管理
	 */
	public function param_manage(){
		$do=D('GoodsParamOptionRelation');
		$list=$do->relation(true)->where(['category_id' => I('get.sid')])->field('id,status,group_name,category_id,sid')->order('sort asc')->select();
		//dump($do->getLastSQL());
		//dump($list);
		$this->assign('list',$list);
		$this->display();
	}

	/**
	* 新增参数分组
	*/
	public function param_group_add(){
		$this->display();
	}

	/**
	* 参数保存
	*/
	public function param_group_add_save(){
		$do=D('Goodsparamgroup87');

		if(!$do->create()) $this->ajaxReturn(['status' => 'warning','msg' => '操作失败！'. $do->getError()]);
		if(!$do->add()) $this->ajaxReturn(['status' => 'warning','msg' =>'操作失败！']);
		else $this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);		
	}

	/**
	* 参数修改
	*/
	public function param_group_edit(){
		$rs=M('goods_param_group')->where(['id' => I('get.id')])->find();
		$this->assign('rs',$rs);

		$this->display();
	}

	/**
	* 保存修改
	*/
	public function param_group_edit_save(){
		$do=D('Goodsparamgroup87');

		if(!$do->create()) $this->ajaxReturn(['status' => 'warning','msg' => '操作失败！'. $do->getError()]);

		if(!$do->save()) $this->ajaxReturn(['status' => 'warning','msg' =>'操作失败！']);
		else $this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);
	}

	/**
	* 删除参数分组
	*/
	public function param_group_delete(){
		if(M('goods_param_group')->where(['id' => I('get.id')])->delete()){
			$this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);
		}else $this->ajaxReturn(['status' => 'warning','msg' => '操作失败！']);
	}	

	/**
	* 新增参数
	*/
	public function param_add(){
		$this->display();
	}

	/**
	* 保存新增参数
	*/
	public function param_add_save(){
		if(in_array(I('post.type'),[1,2]) && I('post.options')=='') $this->ajaxReturn(['status' => 'warning','msg' => '请输入选项内容！']);
		elseif(I('post.type')==3) $_POST['options']='';

		$do=D('Goodsparamgroupoption88');
		if(!$do->create()) $this->ajaxReturn(['status' => 'warning','msg' => '操作失败！'. $do->getError()]);
		if(!$do->add()) $this->ajaxReturn(['status' => 'warning','msg' =>'操作失败！']);
		else $this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);		
	}

	/**
	* 删除参数
	*/
	public function param_delete(){
		if(M('goods_param_group_option')->where(['id' => I('get.id')])->delete()){
			$this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);
		}else $this->ajaxReturn(['status' => 'warning','msg' => '操作失败！']);
	}		

	/**
	* 修改参数
	*/
	public function param_edit(){
		$rs=M('goods_param_group_option')->where(['id' => I('get.id')])->find();
		$this->assign('rs',$rs);

		$this->display();
	}

	/**
	* 保存修改
	*/
	public function param_edit_save(){
		if(in_array(I('post.type'),[1,2]) && I('post.options')=='') $this->ajaxReturn(['status' => 'warning','msg' => '请输入选项内容！']);
		elseif(I('post.type')==3) $_POST['options']='';

		$do=D('Goodsparamgroupoption88');
		if(!$do->create()) $this->ajaxReturn(['status' => 'warning','msg' => '操作失败！'. $do->getError()]);

		if(!$do->save()) $this->ajaxReturn(['status' => 'warning','msg' =>'操作失败！']);
		else $this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);
	}	

	/**
	* 排序
	*/
	public function param_group_setsort(){
		$do=M('goods_param_group');
		foreach(I('post.ids') as $key=>$val){
			$do->where('id='.$val)->setField('sort',$key);
		}		

		$this->ajaxReturn(['status' => 'success']);
	}	

	/**
	* 排序
	*/
	public function param_setsort(){
		$do=M('goods_param_group_option');
		foreach(I('post.ids') as $key=>$val){
			$do->where('id='.$val)->setField('sort',$key);
		}		

		$this->ajaxReturn(['status' => 'success']);
	}
	
	/**
	 * 证书管理
	 */
	public function cert_manage() {
	    $id    =   I('get.sid');
	    $data  =   pagelist([
	        'table'    =>  'GoodsCategoryCertView',
	        'do'       =>  'D',
	        'map'      =>  ['category_id' => $id],
	        'fields'   =>  'id,cert_name,status,category_name,category_id',
	    ]);
	    //$data  =   M('goods_category_cert')->where(['category_id' => $id])->select();
	    $this->assign('data', $data);
	    $this->display();
	}
	
	/**
	 * 添加证书
	 */
	public function certadd() {
	    if (IS_POST) {
	        $data  =   I('post.');
	        $id    =   intval($data['id']);
	        $model =   D('GoodsCategoryCert');
	        if (!$data = $model->create($data)) {
	            $this->ajaxReturn(['status' => 'error', 'msg' => $model->getError()]);
	        }
	        if ($id > 0) {
	            $flag  =   $model->where(['id' => $id])->save();
	        } else {
	            $flag  =   $model->add();
	        }
	        if ($flag) {
	            S('goods_category_cert_lists', null);
	            $this->ajaxReturn(['status' => 'success', 'msg' => '操作成功']);
	        }
	        $this->ajaxReturn(['status' => 'error', 'msg' => '操作失败']);
	    } else {
	        $id    =   I('get.id');
	        $data  =   [];
	        if ($id > 0) {
	            $data  =   M('goods_category_cert')->where(['id' => $id])->find();
	        }
	        $this->assign('data', $data);
	        $this->display();
	    }
	}
	
	/**
	 * 删除资质
	 */
	public function certDel() {
	    if (IS_POST) {
	        $data    =   I('post.');
	        if (M('goods_category_cert')->where(['id' => ['in', $data['id']]])->delete()){
	            S('goods_category_cert_lists', null);
	            $this->ajaxReturn(['status' => 'success', 'msg' => '操作成功']);
	        }
	        $this->ajaxReturn(['status' => 'error', 'msg' => '操作失败']);
	    }
	}
}