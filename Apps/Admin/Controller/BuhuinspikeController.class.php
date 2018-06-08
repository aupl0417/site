<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class BuhuinspikeController extends CommonModulesController {
	protected $name 			='补回购买多件累积升级';	//控制器名称
    protected $formtpl_id		=180;			//表单模板ID
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
        $map = [
            'type_id' => 5,
            'buy_num' => ['gt', 1],
            'status'  => 1,
            'max_num' => ['egt', 1],
        ];
    	$this->_index(['map' => $map]);
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">操作</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
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
	
	/**
	 * 显示页面
	 */
	public function view() {
	    $id = I('get.id');
	    $data = $this->getData($id);
	    if($data) {
	        $this->assign('rs', $data);
	        $this->display();
	    }
	}
	
	/**
	 * 提交数据
	 */
    public function post() {
	    $rs    =   $this->getData(I('post.id'));
	    if ($rs == false) $this->ajaxReturn(['code' => 0, 'msg' => '订单错误']);
	    $data  =   [
	       'orderID'   =>  $rs['s_no'],
	       'money'     =>  $rs['goods_price_edit'],
	       'securityID'=>  'ASDSQWES2564DSA54SA21DAS',
	    ];
	    $res   =   $this->curl('/Erp/addSpecialMoney', $data, 1);
	    if ($res['code'] == 1) {
	        $activity = M('activity')->where(['shop_id' => $rs['shop_id'], 'type_id' => 7])->order('id desc')->find();
	        if ($activity) {   //参与累积升级活动
	            $aData = [
	               'activity_id'   => $activity['id'],
	                's_no'         => $rs['s_no'],
	                'uid'          => $rs['uid'],
	                'status'       => 1,
	                'max_num'      => $activity['max_num'],
	                'buy_num'      => ($rs['buy_num'] - $rs['max_num']),
	                'calc_before_money'    =>  $rs['goods_price_edit'],
	                'calc_after_money'     =>  $rs['goods_price_edit'],
	                'remark'               =>  '享受了消费金额累积升级活动',
	                'type_id'              =>  $activity['type_id'],
	                'full_value'           =>  $rs['goods_price_edit'],
	                'shop_id'              =>  $rs['shop_id'],
	            ];
	            if (M('activity_participate')->add($aData) == false) {
	                $this->ajaxReturn(['code' => 0, 'msg' => '参与活动失败']);
	            }
	        }
	    }
	    $this->ajaxReturn($res);
	}
	
	/**
	 * 获取数据
	 * @param unknown $id
	 */
	private function getData($id) {
	    $do=D($this->fcfg['modelname']);
	    
	    $map = [
	       'type_id' => 5,
	       'max_num' => 1,
	       'buy_num' => ['gt', 1],
	       'status'  => 1,
	       'id'      => $id,
	    ];
	    
	    $data = $do->where($map)->find();
	    return $data;
	}
}