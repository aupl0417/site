<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class WinninglistController extends CommonModulesController {
	protected $name 			='中奖记录';	//控制器名称
    protected $formtpl_id		=186;			//表单模板ID
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
		$this->ac_type = array(0=>'其他活动',1=>'转盘送壕礼');
		$this->prize_type = array(1=>'实物',2=>'积分');
		$this->is_receive = array(1=>'未领取',2=>'已领取',3=>'已过期');
		$this->is_deliver = array(1=>'未发货',2=>'已发货');

    }

    /**
    * 列表
    */
    public function index($param=null){
    	//$this->_index();
        if(I('get.winning_type') == 2){
            $map['score_no']= ['gt',0];
        }else if(I('get.winning_type') == 1){
            $map['pay_express_no']= ['gt',0];
        }
        $this->_index(['map' => $map]);
		$btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a> <div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">详情</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
		$this->assign('fields',$this->plist(null,$btn)); 
		$this->display();
    }
	/**
	* 详情
	* @param int $_GET['id']	订单ID
	*/
	public function view(){
		$data = M('winning_list')->find(I('get.id'));
		
		$luckdraw_info = M('luckdraw_list')->where('no = '.$data['no'])->find();
		$data['prize_type'] = $luckdraw_info['prize_type'];
		$data['score'] = $luckdraw_info['score'];
		
		$user_info = M('user')->find($data['uid']);
		$data['nick'] = $user_info['nick'];
		
		
		$data['ac_type'] = $this->ac_type[$data['ac_type']];
		$data['prize_type_title'] = $this->prize_type[$data['prize_type']];
		$data['is_receive_title'] = $this->is_receive[$data['is_receive']];
		$data['is_deliver_title'] = $this->is_deliver[$data['is_deliver']];
		
		
		
		
		
		if($data['province'] > 0){
			$area =	$this->cache_table('area');
			$data['province']	=	$area[$data['province']];
			$data['city']		=	$area[$data['city']];
			$data['district']	=	$area[$data['district']];
			$data['town']		=	$area[$data['town']];
			
			//查询快递公司
			$express_company = M('express_company')->field('id,company')->where('status = 1')->order('category_id asc,sort asc')->select();
			
			$this->assign('express_company',$express_company);
		}else{
			$data['province'] = $data['city'] = $data['district'] = $data['town'] = '未选择';
		}
		//dump($data);
		$this->assign('data',$data);
		
		$this->display();
	}
	/**
	* 奖品发放
	* @param int $_POST['id']	中奖记录ID
	*/
	public function deliver(){
		
		$data['id'] = I('post.id');
		$data['express_company_id'] = I('post.express_company_id');
		$data['express_company'] = I('post.express_company');
		$data['express_code'] = I('post.express_code');
		$data['is_deliver'] = 2;
		
		if(I('post.action') == 1){
			$data['express_time'] = date('Y-m-d H:i:s');
		}
		$res = M('winning_list')->save($data);

		if($res){
			$this->ajaxReturn(['code'=>1,'msg'=>'发放成功']);
		}else if($res === 0){
			$this->ajaxReturn(['code'=>0,'msg'=>'发货信息没有变更']);
		}else{
			$this->ajaxReturn(['code'=>0,'msg'=>'修改失败']);
		}
		
	}
	/**
	* 物流跟踪
	*/
	public function query_express(){
		$cache_name='query_express_'.I('get.no');
		if(S($cache_name) == false){
			$winning_info = M('winning_list')->field('express_company_id,express_code,express_time')->where('no = '.I('get.no'))->find();
			$rs = M('express_company')->field('company,logo,code,website,tel')->find($winning_info['express_company_id']);
			$rs['express_code'] = $winning_info['express_code'];
			$rs['express_time'] = $winning_info['express_time'];
			if($rs){
				
				
				$url='https://www.kuaidi100.com/query?type='.$rs['code'].'&postid='.$winning_info['express_code'];
				$res=$this->curl_get($url);
				$res=json_decode($res,true);

				if($res) {
					$rs['express'] = $res;
					//S($cache_name, $rs);
				}
				
			}else{
				//找不到快递公司
				
			}		
		}else{
			$rs = S($cache_name);
		}
		$this->assign('rs',$rs);
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