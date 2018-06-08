<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
use Xs\xs;
use Xs\XSDocument;
import('Vendor.Xs.xs');
class SynonymController extends CommonModulesController {
	protected $name 			='同义词管理';	//控制器名称
    protected $formtpl_id		=202;			//表单模板ID
    protected $fcfg				=array();		//表单配置
    protected $do 				='';			//实例化模型
    protected $map				=array();		//where查询条件
	
	protected $xs;  //迅搜句柄
    protected $index; //迅搜索引句柄
    protected $search; //迅搜搜索句柄
    protected $project = 'goodscfg'; //项目

    /**
    * 初始化
    */
    public function _initialize() {
    	parent::_initialize();

    	//初始化表单模板
    	$this->_initform();

		//初始化讯搜
		$this->xs = new xs($this->project);
        $this->index = $this->xs->index;
        $this->search = $this->xs->search;
    	//dump($this->fcfg);

    }

    /**
    * 列表
    */
    public function index($param=null){
    	$this->_index();
		$btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a> <div data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-update">更新</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
		$this->assign('fields',$this->plist(null,$btn));
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
		$do = M('synonym');
		if(I("post.word") == I("post.synonym")){
			$this->ajaxReturn(['status'=>'warning','msg'=>'同义词和关键词不能一致，添加失败！']);
		}
		$res = $do->where(['_string' => '(word = "'.I("post.word").'" and synonym="'.I("post.synonym").'") or (word ="'.I('post.synonym').'" and synonym="'.I('post.word').'")'])->field('id')->find();
		if($res){
			$this->ajaxReturn(['status'=>'warning','msg'=>'该同义词已经存在，添加失败！']);
		}else{
			$result=$this->_add_save();
			
			$this->index->addSynonym(I('post.word'),I('post.synonym')); 

			$this->ajaxReturn($result);
		}
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
		$do = M('synonym');
		if(I("post.word") == I("post.synonym")){
			$this->ajaxReturn(['status'=>'warning','msg'=>'同义词和关键词不能一致，修改失败！']);
		}
		$res = $do->where(['_string' => 'id!='.I("post.id").' and (word = "'.I("post.word").'" and synonym="'.I("post.synonym").'") or (word ="'.I('post.synonym').'" and synonym="'.I('post.word').'")'])->field('id')->find();
	//	dump($do->getLastSql());
		if($res){
			$this->ajaxReturn(['status'=>'warning','msg'=>'该同义词已经存在，修改失败！']);
		}else{
			$rs = $do->where(['id' => I('post.id')])->field('id,word,synonym')->find();
			$this->index->delSynonym($rs['word'],$rs['synonym']);
			
			$result=$this->_edit_save();
			
			$this->index->addSynonym(I('post.word'),I('post.synonym')); 

			$this->ajaxReturn($result);
		}
	}

	/**
	* 删除选中记录
	*/
	public function delete_select($param=null){
		$do = M('synonym');
		$this->index->openBuffer(128); //缓冲大小200M
		foreach(I('post.id') as $key=>$val){
			$rs = $do->where(['id' => $val])->getField('word,synonym');
			$this->index->delSynonym($rs['word'],$rs['synonym']);
		}
        $this->index->closeBuffer();
		$result=$this->_delete_select();
		$this->ajaxReturn($result);
	}
	
	/**
	* 批量添加
	*/
	public function batch_add($param=null){
		$do = M("synonym_category");
		$rs = $do->where(['status' => 1])->field('id,category_name')->select();
		$this->assign("rs",$rs);
		$this->display();
	}

	/**
	* 批量保存记录
	*/
	public function save_batch_add($param=null){
		$do = M('synonym');
		$add_data['sid']    = I('post.sid');
		$add_data['status'] = I('post.status')?I('post.status'):0;

		$data = explode(chr(10),I('post.data'));
		//$data = explode(" ",I('post.data'));exit();
	
		$this->index->openBuffer(128); //缓冲大小200M
		foreach($data as $val){
			$val = explode(',',$val);
			$add_data['word']    = trim($val[0]);
			$add_data['synonym'] = trim($val[1]);
			if(($add_data['word'] == $add_data['synonym']) || empty($add_data['word']) || empty($add_data['synonym'])){
				continue;
			}

			$res = $do->where(['_string' => '(word = "'.$add_data['word'].'" and synonym="'.$add_data['synonym'].'") or (word ="'.$add_data['synonym'].'" and synonym="'.$add_data['word'].'")'])->field('id')->find();
			if($res){
				continue;
			}
			$this->index->addSynonym($add_data['word'],$add_data['synonym']); 
			$result=$do->add($add_data);
		}
		$this->index->closeBuffer();
		if($result !== false){
			$this->ajaxReturn(['status'=>'success','msg'=>'添加成功！']);
		}
	}
	
	/**
    * 更新记录
    */
    public function update_word($param=null){
		$do = M('synonym');
		$rs = $do->where(['id' => I('get.id')])->field('word,synonym')->find();
		if($rs){
			$result = $this->index->addSynonym($rs['word'],$rs['synonym']); 
			$this->ajaxReturn(['status'=>'success','msg'=>'添加成功！']);
/* 			if($result !== false){
				$this->ajaxReturn(['status'=>'success','msg'=>'添加成功！']);
			} */
		}else{
			$this->ajaxReturn(['status'=>'warning','msg'=>'记录不存在，更新失败！']);
		}
    }
	
	/**
	* 批量更改状态
	*/
	public function active_change_select($param=null){
		$result=$this->_active_change_select();

		$this->ajaxReturn($result);		
	}
}