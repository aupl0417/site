<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
use Think\Exception;

class GoodscomplaintsController extends CommonModulesController {
	protected $name 			='商品投诉';	//控制器名称
    protected $formtpl_id		=212;			//表单模板ID
    protected $fcfg				=array();		//表单配置
    protected $do 				='';			//实例化模型
    protected $map				=array();		//where查询条件
    protected $type = [
        1 => '虚假宣传',
        2 => '商品信息有误',
        3 => '滥发信息',
        4 => '商品更换宝贝',
        5 => '商标/品牌侵权',
        6 => '价格违规',
    ];
    protected $weights = [  //扣分
        1 => 5,
        2 => 5,
        3 => 5,
        4 => 5,
        5 => 5,
        6 => 5,
    ];
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
    	$this->_index();
        $btn=array('title'=>'操作','type'=>'html','html'=>'<div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">回复</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
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
     * 回复
     */
	public function replay() {
        try {
            $data = I('post.');
            $do   = M('goods_complaints');
            $rs   = $this->getData($data['id']);
            $do->startTrans();
            $cData['reply_content'] =   $data['replay_content'];
            $cData['down_weights']  =   $data['down_weights'];
            $cData['status']        =   2;
            $cData['id']            =   $data['id'];
            $cData['employee_id']   =   session('admin.id');
            switch ($cData['down_weights']) {
                case 1: //降权
                    //商家被举报累积次数
                    if (M('shop')->where(['id' => $rs['shop_id']])->setInc('complaints_num', 1) == false) throw new Exception('累积举报次数失败');
                    if (M('goods')->where(['id' => $rs['goods_id']])->setDec('pr', $this->weights[$rs['type']]) == false) throw new Exception('降权失败');
                    break;
                case 2: //违规下架
                    //商家被举报累积次数
                    $sellerId = M('shop')->where(['id' => $rs['shop_id']])->cache(true)->getField('uid');
                    if (M('shop')->where(['id' => $rs['shop_id']])->setInc('complaints_num', 1) == false) throw new Exception('累积举报次数失败');
                    $sql = 'UPDATE ' . C('DATA_CACHE_PREFIX') . 'goods SET status = 4, pr = pr-' . $this->weights[$rs['type']] . ' WHERE id = ' . $rs['goods_id'];
                    if(M()->execute($sql) == false)  throw new Exception('下架失败');
                    //if (M('goods')->where(['id' => $rs['goods_id']])->save(['status' => 4, 'pr' => 'pr-' . $this->weights[$rs['type']]]) == false) throw new Exception('下架失败');
                    $iData = [
                        'a_uid'         =>  session('admin.id'),
                        'uid'           =>  $sellerId,//卖家ID
                        'shop_id'       =>  $rs['shop_id'],
                        'goods_id'      =>  $rs['goods_id'],
                        'status'        =>  1,
                        'reason'        =>  $rs['content'],
                        'illegl_point'  =>  $this->weights[$rs['type']],
                        'dotime'        =>  date('Y-m-d H:i:s', NOW_TIME),
                        'remark'        =>  '用户举报',
                    ];
                    $iId = M('goods_illegl')->add($iData);
                    if ($iId == false) throw new Exception('创建违规数据失败');
                    $iLogs = [
                        'illegl_id' =>  $iId,
                        'uid'       =>  $sellerId,
                        'a_uid'     =>  session('admin.id'),
                        'status'    =>  1,
                        'images'    =>  $rs['images'],
                        'remark'    =>  '用户举报',
                    ];
                    if (M('goods_illegl_logs')->add($iLogs) == false) throw new Exception('创建违规日志失败');
                    break;
                default:
            }
            if ($do->save($cData) == false) throw new Exception('操作失败');
            $do->commit();
            $this->ajaxReturn(['status' => 'success', 'msg' => '回复成功']);
        } catch (Exception $e) {
            $do->rollback();
            $this->ajaxReturn(['status' => 'warning', 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 详情
     */
    public function view() {
        $data = $this->getData(I('get.id'));
        $this->assign('type', $this->type);
        $this->assign('rs', $data);
	    $this->display();
    }

    /**
     * 获取数据
     *
     * @param $id
     * @return mixed
     */
    private function getData($id) {
        $model = D('Goodscomplaints212View');
        $data = $model->where(['id' => $id])->find();
        return $data;
    }
}