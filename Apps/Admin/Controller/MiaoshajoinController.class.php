<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class MiaoshajoinController extends CommonModulesController {
	protected $name 			='秒杀报名审核';	//控制器名称
    protected $formtpl_id		=168;			//表单模板ID
    protected $fcfg				=array();		//表单配置
    protected $do 				='';			//实例化模型
    protected $map				=array();		//where查询条件
    protected $activity_id      =250;           //秒杀活动ID
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
        $map['activity_id'] = $this->activity_id;
    	$this->_index(['map' => $map]);

        $btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a> <div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">审核</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
        $this->assign('fields',$this->plist(null,$btn));

        //dump($this->plist(null,$btn));

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
		//$result=$this->_add_save();
        $do=D($this->fcfg['verify_model']);
        $this->post_cmp();

        if(I('post.images')){
            $tmp[] = I('post.images');
            $_POST['images'] = serialize($tmp);
        }

        if(isset($_POST['is_bat']) && I('post.is_bat') != 1){   //批量添加,逗号隔开
            $n = 0;
            $bat = I('post.'.I('post.is_bat'));
            $bat = explode(',',$bat);
            foreach($bat as $val){
                $_POST[I('post.is_bat')] = $val;
                if ($do->create() && $do->add()) {
                    $n++;
                }
            }

            if($n > 0) {
                $result['status']   = 'success';
                $result['msg']      = '批量添加了'.$n.'个';
            }
            else {
                $result['status']   = 'warning';
                $result['msg']      = '添加失败！';

            }

        }else {
            if ($do->create() && $do->add()) {
                $result['status'] = 'success';
                $result['msg'] = '添加成功！';
                $result['id'] = $do->getLastInsID();
            } else {
                $result['status'] = 'warning';
                $result['msg'] = '操作失败！' . $do->getError();
            }
        }
		$this->ajaxReturn($result);
	}

	/**
	* 修改记录
	*/
	public function edit($param=null){
		//$this->_edit();
        //启用一张活动图片
        $do=M($this->fcfg['table']);
        $rs=$do->where('id='.I('get.id'))->find();
        if($rs['images']) {
            $tmp = unserialize(html_entity_decode($rs['images']));
            $rs['images'] = $tmp[0];
        }

        if($param['data']) $rs=array_merge($rs,$param['data']);
        $this->assign('rs',$rs);

		$this->display();
	}

	/**
	* 保存修改记录
	*/
	public function edit_save($param=null){
		//$result=$this->_edit_save();

        $do=D($this->fcfg['verify_model']);
        $this->post_cmp();

        if(I('post.images')){
            $tmp[] = I('post.images');
            $_POST['images'] = serialize($tmp);
        }

        if($do->create() && $do->save() !== false){
            $result['status']='success';
            $result['msg']='添加成功！';
        }else{
            $result['status']='warning';
            $result['msg']='操作失败！'.$do->getError();
        }

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

	/**
     * 详情
     */
	public function view(){
	    $do = D('Officialactivityjoin168Relation');
        $rs = $do->relation(true)->where(['id' => I('get.id')])->find();

        if($rs['images']) $rs['images'] = unserialize(html_entity_decode($rs['images']));
        //dump($rs);
        $this->assign('rs',$rs);

        $this->display();
    }

    /**
     * 保存审核记录
     */
    public function logs_add(){
        if(I('post.status')==2 && I('post.reason')==''){
            $this->ajaxReturn(array('status'=>'warning','msg'=>'请输入拒绝原因！'));
        }
        $this->do->create();
        if(false !== $this->do->save()){
            $result['status']='success';
            $result['msg']='操作成功！';
        }else{
            $result['status']='warning';
            $result['msg']='操作失败！';
        }
        $this->ajaxReturn($result);
    }

}