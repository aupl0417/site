<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class CouponbatchController extends CommonModulesController {
	protected $name 			='优惠券发行批次';	//控制器名称
    protected $formtpl_id		=120;			//表单模板ID
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
     * 详情页面
     * @param int $_GET['id']	ID
     */
    public function view(){
		$map['id'] = I("get.id");

		$pagelist=pagelist(array(
			'do'		=>$this->fcfg['do'],
			'table'		=>$this->fcfg['modelname'],
			'pagesize'	=>$this->fcfg['pagesize'],
			'order'		=>$this->fcfg['order'],
			'fields'	=>$this->fcfg['fields'],
			'relation'	=>$this->fcfg['action_type']==2?true:'',
			'map'		=>$map,
		));
		if($pagelist['list']){
			$data['title'] = array(
				'text'    => "优惠劵详情",   //主标题
				'subtext' => "",   //副标题
				'x'       => "center",       //标题位置(left,right,center)
			);

			$data['legend'] =array(
				0 =>'发行数量',
				1 =>'领取数量',
				2 =>'使用数量',
			);
			$data["x_title"] = "left";      //legend 位置(left,right,center)
			$data["name"]    = "优惠劵";  //图表用途名称
			$data['data'] = array(//数据
				array(
					'value' => $pagelist['list'][0]['num']?$pagelist['list'][0]['num']:0,
					'name'  => "发行数量",
				),
				array(
					'value' => $pagelist['list'][0]['get_num']?$pagelist['list'][0]['get_num']:0,
					'name'  => "领取数量",
				),
				array(
					'value' => $pagelist['list'][0]['use_num']?$pagelist['list'][0]['use_num']:0,
					'name'  => "使用数量",
				),
			);
		}
	
        $this->assign('data',$data);
        $this->assign('rs',$pagelist['list'][0]);
        $this->display();
    }
    /**
    * 列表
    */
    public function index($param=null){
        $options['map']['type'] = 1;
    	$this->_index($options);
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a> '.('<a data-id="[id]" data-url="'.__CONTROLLER__.'/recom/id/[id]" target="_blank" class="btn btn-sm btn-danger btn-rad btn-trans btn-block m0 btn-recom">推荐</a>').('<a data-id="[id]" data-url="'.__CONTROLLER__.'/view/id/[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">详情</a>'),'td_attr'=>'width="100" class="text-center"','norder'=>1);
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
		$_POST['b_no']=$this->create_orderno('YG');
		$result=$this->_add_save();
		
		/*
        $do=D('Coupon122');
		if($result['id']){
			for($i=0;$i<I('post.num');$i++){
				$data=array();
				$data['b_id']		=$result['id'];
				$data['code']		=$this->create_orderno('YH');
				$data['price']		=I('post.price');
				$data['seller_id']	=I('post.seller_id');
				$data['min_price']	=I('post.min_price');
				$data['sday']		=I('post.sday');
				$data['eday']		=I('post.eday');
				
				if($do->create($data)) $do->add();
			}
		}
		*/

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
		//$result=$this->_edit_save();
		$result['status']='warning';
		$result['msg']='只能删除不能修改！';
		$this->ajaxReturn($result);
	}

	/**
	* 删除选中记录
	*/
	public function delete_select($param=null){	
		//已有被使用的优惠券发行记录不可删除
		$do=M('coupon');
		foreach(I('post.id') as $key=>$val){
			if($do->where(array('b_id'=>$val,'is_use'=>1))->count()>0) unset($_POST['id'][$key]);
			else $do->where(array('b_id'=>$val))->delete();
		}

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
	 * 推荐
	 */
	public function recom() {
	    $id = I('get.id');
	    if ($id > 0) {
	        //是否已推荐
	        $model = D('Common/CouponRecomView');
	        $data = $model->where(['id' => $id])->find();
	        if ($data) {
	            $cate = M('coupon_recom_category')->where(['status' => 1])->order('sort asc')->getField('id,name');
	            $cates = M('coupon_recom_category')->where(['status' => 1])->order('sort asc')->field('id,name')->select();
	            $this->assign('rs', $data);
	            $this->assign('cate', $cate);
	            $this->assign('cates', $cates);
	            $this->display();
	        }
	    }
	}
	
	/**
	 * 推荐保存
	 */
	public function recomSave() {
	    if (IS_POST) {
	        $data = I('post.');
	        $model = D('Common/CouponRecom');
	        if (!$model->create()) {
	            $this->ajaxReturn(['status' => 'warning', 'msg' => $model->getError()]);
	        }
	        
	        if (!$model->add()) {
	            $this->ajaxReturn(['status' => 'warning', 'msg' => '添加失败']);
	        }
	         
	        $this->ajaxReturn(['status' => 'success', 'msg' => '添加成功']);
	    }
	}
}