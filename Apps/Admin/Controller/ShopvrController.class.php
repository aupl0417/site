<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class ShopvrController extends CommonModulesController {
	protected $name 			='商家违规记录';	//控制器名称
    protected $formtpl_id		=256;			//表单模板ID
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
    	$this->_index();
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">审核</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
    	$this->assign('fields',$this->plist(null,$btn));
		$this->display();
    }
    
    /**
     * 待申诉
     * @param unknown $param
     */
    public function su($param=null){
    	$param['map']=['status'=>0];
    	$this->_index($param);
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">审核</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
    	$this->assign('fields',$this->plist(null,$btn));
    	$this->display('index');
    }
    
    /**
     * 待审核
     * @param unknown $param
     */
    public function sh($param=null){
    	$param['map']=['status'=>1];
    	$this->_index($param);
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">审核</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
    	$this->assign('fields',$this->plist(null,$btn));
    	$this->display('index');
    }
    
    /**
     * 已判定
     * @param unknown $param
     */
    public function pd($param=null){
    	$param['map']=['status'=>2];
    	$this->_index($param);
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">审核</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
		
		$btn['html'] .= '<div data-url="'.__CONTROLLER__.'/plus_point/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view2">抵消扣分</div>';
		
    	$this->assign('fields',$this->plist(null,$btn));
    	$this->display('index');
    }
    
    /**
     * 处罚取消
     * @param unknown $param
     */
    public function qx($param=null){
    	$param['map']=['status'=>3];
    	$this->_index($param);
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">审核</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
    	$this->assign('fields',$this->plist(null,$btn));
    	$this->display('index');
    }
    
    /**
     * 申诉补充
     * @param unknown $param
     */
	 /*
    public function resu(){
    	$param['map']=['status'=>4];
    	$this->_index($param);
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a> <div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">审核</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
    	$this->assign('fields',$this->plist(null,$btn));
    	$this->display('index');
    }
	*/
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
	 * 根据严重程度获取违规规则
	 */
	public function get_wrongdoing(){
		$id=I('get.id',0);
		$list=M('shop_rules')->field('id,reason,remark')->where(['status'=>1,'type'=>$id])->select();
		$this->ajaxReturn($list,'JSON');
	}
	
	public function view(){
		$do = D('Shopvr256');
		$rs = $do->where(['id' => I('get.id')])->find();
		if($rs['goods_id'] != 0){
			$rs['goods_info'] = M('goods')->find($rs['goods_id']);
		}
		
		$rs['nick']=M('user')->where(['id'=>$rs['uid']])->getField('nick');
		$rs['logs']=D('Shopvrlogs')->where(['shop_vr_id'=>$rs['id']])->order('atime DESC')->select();
		$this->assign('rs',$rs);
		$this->display();
	}
	
	/**
     * subject: 添加审核日志
     * author: liangfeng
     * day: 2017-06-02 
     */
	public function logs_add(){
		$status=I('post.status');
		$remark=I('post.remark');
		$id=I('post.id',0,'int');
		
		$shop_vr_info = M('shop_vr')->where(['id'=>$id])->find();
		
		if($shop_vr_info['status'] == 2 || $shop_vr_info['status'] == 3){
			$this->ajaxReturn(['status' => 'warning','msg' => '已取消违规或已生效的违规记录不允许再次操作！']);
		}
		
		$do = M();
		$do->startTrans();
		
		if(false===D('Shopvrlogs')->add(['a_uid'=>session('admin.id'),'shop_vr_id'=>$id,'status'=>$status,'remark'=>$remark])) goto error;
		
		
		//处罚取消
		if($status == 3){
			M('shop_vr')->where(['id'=>$id])->setField('status',$status);
			if(I('post.goods_id') != 0){
				//商品自动上架
				M('goods')->where(['id' => I('post.goods_id')])->save(['status' => 1]);
				
				$illegl_id = M('goods_illegl')->where(['goods_id'=>I('post.goods_id'),'status'=>['in','1,2,3']])->getField('id');
				//商品违规变更为取消
				M('goods_illegl')->where(['id'=>$shop_vr_info['goods_illegl_id']])->data(['status'=>0])->save();
				$illegl_data['illegl_id'] = $shop_vr_info['goods_illegl_id'];
				$illegl_data['uid'] = I('post.uid');
				$illegl_data['a_uid'] = session('admin.id');
				$illegl_data['status'] = 0;
				$illegl_data['remark'] = '取消违规';
				M('goods_illegl_logs')->data($illegl_data)->add();
			}
		}
		
		$do->commit();	
		//处罚生效
		if($status == 2){
			$res = $this->doApi2('/ShopVr/illegl',['shop_vr_id'=>$id]);
		}
		
		
		$this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);
	
		
		error:
			$do->rollback();
			$result['status']='warning';
			$result['msg']='操作失败！'.$msg;
			$this->ajaxReturn($result);
		
		//$this->ajaxReturn(['status' => 'warning','msg' => '操作失败！']);
	}
	
	/**
     * subject: 抵消扣分页面
     * author: liangfeng
     * day: 2017-06-07
     */
	public function plus_point(){
		$res = M('shop_vr')->find(I('id'));
		$this->assign('rs',$res);
		$this->display();
	}
	/**
     * subject: 抵消扣分
     * author: liangfeng
     * day: 2017-06-07
     */
	public function ajax_plus_point(){
		$plus_point = I('post.plus_point',0,'int');
		
		$res = M('shop_vr')->field('uid,point')->where(['id'=>I('post.id')])->find();
		if($plus_point > $res['point']){
			$this->ajaxReturn(['status' => 'warning','msg' => '不能大于扣分值！']);
		}
		//dump($res);
		
		$do = M();
		$do->startTrans();
		
		if(false === M('shop_vr')->where(['id'=>I('post.id')])->data(['plus_point'=>$plus_point])->save()) goto error;
		
		//统计店铺一年所有的扣分
		$year = date('Y',time());
		$points = M('shop_vr')->field('sum(point) as total_point,sum(plus_point) as total_plus_point')->where(['_string'=>'date_format(atime,"%Y")="'.$year.'"','status'=>2,'uid'=>$res['uid']])->find();

		$total_point = $points['total_point'] ? $points['total_point'] : 0 ;
		$total_plus_point = $points['total_plus_point'] ? $points['total_plus_point'] : 0 ;		
		$dec_point = $total_point-$total_plus_point;
	
		//更新店铺扣分
		if(false===M('shop')->where(['uid'=>$res['uid']])->save(['illegl_point'=>$dec_point])) goto error;
		
		$do->commit();	
		$this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);
		
		
		error:
			$do->rollback();
			$this->ajaxReturn(['status' => 'warning','msg' => '操作失败！']);
		
	}
	/**
	 * 商家违规分数
	 */
	public function vrpoint(){
		$year = date('Y',time());
		$do=M('shop_vr a');
		$shop_name=I('get.shop_name');
		if ($shop_name) $map['a.shop_name']=['like','%'.$shop_name.'%'];
		$map['a.status']=2;
		$map['_string'] = 'date_format(a.atime,"%Y")="'.$year.'"';
		$group='a.shop_name';
		$count = $do->field('a.id')->where($map)->group($group)->select();
		$count = count($count);
		$page= new \Think\Page($count, 10);
		$limit=$page->firstRow.','.$page->listRows;
		$list=$do->field('a.shop_name,a.mobile,b.nick,sum(a.point) as point,sum(a.plus_point) as plus_point,count(a.id) as cs,b.shop_id')->join('left join '.C('DB_PREFIX').'user b on a.uid=b.id')->where($map)->order('point DESC')->group($group)->limit($limit)->select();
		foreach ($list as $k=>$v){
			$list[$k]['shop_url']=shop_url($v['shop_id']);
			$list[$k]['real_point']=$v['point']-$v['plus_point'];
		}
		$result['list']=$list;
		$result['listnum']=count($list);
		$result['allnum']=$count;
		$result['page']=$page->show_btn();
		$result['allpage']=$page->allpage();
		$this->assign('pagelist',$result);
		
		$fields=[
				[
						'title'=>'选择',
						'type'=>'html',
						'html'=>'<input type="checkbox" class="i-red-square" name="id[]" id="id[]" value="[id]">',
						'td_attr'=>'width="60" class="text-center"',
						'norder'=>1
				],
				[
						'title'=>'店铺名称',
						'field'=>'shop_name',
				],
				[
						'title'=>'用户ID',
						'field'=>'nick',
				],
				[
						'title'=>'用户手机号',
						'field'=>'mobile',
				],
				[
						'title'=>'违规分数',
						'field'=>'point',
				],
				[
						'title'=>'抵消分数',
						'field'=>'plus_point',
				],
				[
						'title'=>'实际扣分',
						'field'=>'real_point',
				],
				[
						'title'=>'违规次数',
						'field'=>'cs',
				],
				/*
				[
						'title'=>'建议处罚',
						'field'=>'point',
						'function'=>"if(\$val['point']>0 and \$val['point']<12) return '不作处理';if(\$val['point']>=12 and \$val['point']<24) return '屏蔽店铺所有商品的展示';if(\$val['point']>=24 and \$val['point']<36) return '暂停店铺营业半个月';if(\$val['point']>=36 and \$val['point']<48) return '暂停店铺营业三个月';if(\$val['point']>=48) return '直接关店，该帐号一年内不得开店';"
				],
				*/
				[
						'title'=>'操作',
						'type'=>'html',
						'html'=>'<a href="[shop_url]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0" target="_blank">进入店铺</a>',
						'td_attr'=>'width="100" class="text-center"',
						'norder'=>1
				]
		];
		$this->assign('fields',$fields);
		$this->display();
	}
}