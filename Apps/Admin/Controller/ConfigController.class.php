<?php
namespace Admin\Controller;
use Think\Controller;
class ConfigController extends CommonController {
    public function index(){
		$do=D('ConfigSort');
		$list=$do->all([['active'=>1],['active'=>1]]);

		//print_r($list);
		$this->assign('list',$list);
		
		$this->display();
    }
	
	
	//参数分组
	public function sort(){
		$do=D('ConfigSort');
		$list=$do->all();
		$this->assign('list',$list);
		$this->display();
	}
	
	public function sort_add(){
		
		$this->display();		
	}
	
	//保存分组
	public function sort_add_save(){
		$do=D('ConfigSort');
		
		if($do->create() && $do->add()){
			$this->ajaxReturn(array('status'=>'success','msg'=>'添加成功！'));
		}else{
			$this->ajaxReturn(array('status'=>'warning','msg'=>'添加失败！'));
		}
	}
	
	//修改分组
	public function sort_edit(){
		$do=M('config_sort');
		$rs=$do->where(array('id'=>I('get.id')))->find();
		$this->assign('rs',$rs);
		$this->display();
	}
	
	public function sort_edit_save(){
		$do=D('ConfigSort');
		
		if($do->create() && $do->save()){
			$this->ajaxReturn(array('status'=>'success','msg'=>'修改成功！'));
		}else{
			$this->ajaxReturn(array('status'=>'warning','msg'=>$do->getError()));
		}		
	}
	
	//排序
	public function setsort(){
		$do=M('config_sort');
		foreach(I('post.ids') as $key=>$val){
			$do->where('id='.$val)->setField('sort',$key);
		}
		$this->ajaxReturn(array('status'=>'success'));
	}
	
	//删除分类
	public function sort_delete(){
		$this->_sort_delete();


	}
	//删除字段分组
	public function _sort_delete(){
		$do=M('config_sort');
		$list = $do->where(array('sid'=>I('get.id')))->select();

		if(empty($list)){   //如果子分组为空，则config下就没有需要删除的值
			$config=D('Config');
			foreach($list as $val){
				$config->where(array('sid'=>$val['id']))->delete();
			}

			$do->where(array('sid'=>I('get.id')))->delete();
		}

		$rs1 = $do->where(array('id'=>I('get.id')))->delete();

		if($rs1){
			$this->ajaxReturn(array('status'=>'success','msg'=>'删除成功！'));
		}else{
			$this->ajaxReturn(array('status'=>'warning','msg'=>'删除失败！'));
		}


	}
	
	
	//参数管理
	public function item(){
		$do=D('ConfigSort');
		$list=$do->all();
		$this->assign('list',$list);
		$this->display();
	}

	//保存参数
	public function item_add_save(){
		$do=D('Config');

		$_POST['config']='return '.var_export(I('post.'),true).';';
		if($do->create() && $do->add()){
			$this->ajaxReturn(array('status'=>'success','msg'=>'添加成功！'));
		}else{
			$this->ajaxReturn(array('status'=>'warning','msg'=>$do->getError()));
		}
	}

	//修改参数管理
	public function item_edit(){
		$do=M('config');
		$rs=$do->where(array('id'=>I('get.id')))->find();

		$rs['config']=eval(html_entity_decode($rs['config']));

		$this->assign('rs',$rs);

		$this->display();
	}

	//保存更改的参数管理
	public function item_edit_save(){
		$do=D('Config');
		$_POST['config']='return '.var_export(I('post.'),true).';';
		if($do->create() && $do->save()){
			$this->ajaxReturn(array('status'=>'success','msg'=>'修改成功！'));
		}else{
			$this->ajaxReturn(array('status'=>'warning','msg'=>$do->getError()));
		}
	}

	//管理排序
	public function set_item_sort(){
		$do=M('config');
		foreach(I('post.ids') as $key=>$val){
			$do->where('id='.$val)->setField('sort',$key);
		}
		$this->ajaxReturn(array('status'=>'success'));
	}

	//网络设置 修改参数值
	public function edit_item_value(){
		$do=M('Config');
		$where['sid'] =  I('post.sid');
		foreach( I('post.') as $k =>$v){
			$where['name'] = $k;
			$data['value'] = is_array($v) ? implode(',', $v) : $v;
			$do->where($where)->data($data)->save();
		}

		S('cfg_site',null); //集群无法使用 S(null)
		$this->ajaxReturn(array('status'=>'success','msg'=>'保存成功！'));
	}

	//删除参数
	public function item_delete(){
		$do=M('Config');
		$where['id'] = I('get.id');

		$rs = $do->where($where)->delete();
		if($rs){
			$this->ajaxReturn(array('status'=>'success','msg'=>'删除成功！'));
		}else{
			$this->ajaxReturn(array('status'=>'false','msg'=>'删除失败！'));
		}
	}
}
