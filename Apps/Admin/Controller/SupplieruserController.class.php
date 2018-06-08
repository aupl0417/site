<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
use Common\Builder\R;
use Common\Builder\F;
use Think\Exception;
class SupplieruserController extends CommonModulesController {
	protected $name 			='供货商入驻';	//控制器名称
    protected $formtpl_id		=273;			//表单模板ID
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
		$btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">店铺信息</a><div data-url="'.__CONTROLLER__.'/show_examine/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">审核</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
    	$this->assign('fields',$this->plist(null,$btn));
		$this->display();
    }

	/**
     * 查看供货商入驻的审核信息
     * Create by liangfeng
     * 2017-09-12
     */
	public function show_examine(){
		$id = I('get.id');
		$info = M('supplier_user')->where(['id'=>$id])->find();
		$package_info = M('supplier_package')->where(['uid'=>$info['uid']])->order('id asc')->find();
		$package_info['package_select'] = explode(',',$package_info['package']);
		$info['package'] = $package_info;
		$info['user'] = M('user')->where(['id'=>$info['uid']])->find();
		$info['status_name'] = ['1'=>'未提交','2'=>'待审核','3'=>'审核成功','4'=>'审核失败'][$info['status']];
		$info['package']['status_name'] = ['1'=>'未提交','2'=>'待审核','3'=>'审核成功','4'=>'审核失败'][$info['package']['status']];
		//dump($info);
		$this->assign('info',$info);
		
		$area = $this->cache_table('area');
		$this->assign('area',$area);
		
		$banks = F::getBankName();
        $this->assign('banks',$banks);
		$this->display();
	}
	
	/**
     * 供货商入驻雇员审核
     * Create by liangfeng
     * 2017-09-13
     */
	public function ajax_examine(){
		//dump(I('post.'));
		try{
			//获取信息
			$res = M('supplier_user')->find(I('post.id'));			
			if(!$res) throw new Exception('ID错误');
			if($res['status'] != 2) throw new Exception('此申请不是待审核状态');
			$res['package'] = M('supplier_package')->where(['uid'=>$res['uid']])->order('id asc')->find();
			
			$do = M();
			$do->startTrans();
			
			if(false === M('supplier_user')->where(['id'=>$res['id']])->data(['status'=>I('post.status')])->save()){
				$do->rollback();
				throw new Exception('提交失败');
			}
			
			if(false === M('supplier_package')->where(['id'=>$res['package']['id']])->data(['status'=>I('post.status'),'examine_remark'=>I('post.examine_remark')])->save()){
				$do->rollback();
				throw new Exception('提交失败！');
			}
			
            $do->commit();
			
            $res = ['code'=>1,'data'=>$res,'msg'=>'审核成功'];
		}catch (Exception  $e){
			$do->rollback;
			$res = ['code'=>0,'msg' => $e->getMessage()];
		}
		
		$this->ajaxReturn($res);
	}
	
	/**
     * 提现列表
     * Create by liangfeng
     * 2017-09-19
     */
	public function withdrawals(){
		//dump(I('get.'));

		$cfg = I('get.');

		$do=M('supplier_turnover as a');

		if($cfg['uid']) $map['a.uid'] = $cfg['uid'];
		if($cfg['bank_user']) $map['a.bank_user'] = $cfg['bank_user'];
		if($cfg['admin_id']) $map['a.admin_id'] = $cfg['admin_id'];
		if($cfg['id']) $map['a.id'] = $cfg['id'];
		if($cfg['status']) $map['a.status'] = $cfg['status'];

        if(empty($cfg['atime_sday'])) $cfg['atime_sday']	=	'2017-07-01';
        if(empty($cfg['atime_eday'])) $cfg['atime_eday']	=	date('Y-m-d',time()+86400);
        $map['a.atime']	=	['between',[$cfg['atime_sday'],$cfg['atime_eday']]];

        if(empty($cfg['examine_time_sday'])) $cfg['examine_time_sday']	=	'0000-00-00';
        if(empty($cfg['examine_time_eday'])) $cfg['examine_time_eday']	=	date('Y-m-d',time()+86400);
        $map['a.examine_time']	=	['between',[$cfg['examine_time_sday'],$cfg['examine_time_eday']]];

        if(empty($cfg['money_s'])) $cfg['money_s']	=	'0';
        if(empty($cfg['money_e'])) $cfg['money_e']	=	'9999999999';
        $map['a.money']	=	['between',[$cfg['money_s'],$cfg['money_e']]];


        //计算提现总额
        $map2 = $map;
        $map2['a.status'] = 2;
        $sum_money = $do->where($map2)->sum('real_money');
        $sum_money = $sum_money>0?$sum_money:0;
        $this->assign('sum_money',$sum_money);

		$count = $do->field('a.id')->where($map)->count();
		//dump(M()->getLastSql());
		$page= new \Think\Page($count, 10);
		$limit=$page->firstRow.','.$page->listRows;
		$list=$do->field('*,a.id,a.atime,a.status,b.nick')->join('ylh_user as b on a.uid = b.id')->where($map)->order('a.atime desc')->limit($limit)->select();

		foreach ($list as $k=>$v){
			$list[$k]['charge'] = $v['money'] - $v['real_money'];
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
						'title'=>'编号',
						'field'=>'id',
				],
				[
						'title'=>'发起用户',
						'field'=>'nick',
				],
				[
						'title'=>'发起时间',
						'field'=>'atime',
				],
				[
						'title'=>'提现金额',
						'field'=>'money',
				],
				[
						'title'=>'手续费',
						'field'=>'charge',
				],
				[
						'title'=>'到账金额',
						'field'=>'real_money',
				],
				[
						'title'=>'提现账户',
						'field'=>'bank_no',
				],
				[
						'title'=>'开户地区',
						'field'=>'bank_open_address',
				],
				[
						'title'=>'开户名',
						'field'=>'bank_user',
				],
				[
						'title'=>'状态',
						'field'=>'status',
						'function'=>'return status($val["status"],array(1=>array("待审核","btn-info"),2=>array("已结算","btn-success"),3=>array("已驳回","btn-default")));',
				],
				[
						'title'=>'操作时间',
						'field'=>'examine_time',
				],
				[
						'title'=>'拒绝原因',
						'field'=>'reason',
				],
				[
						'title'=>'操作',
						'type'=>'html',
						'html'=>'<div data-url="'.__CONTROLLER__.'/withdrawals_examine/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">审核</div>',
						'td_attr'=>'width="100" class="text-center"',
						'norder'=>1
				]
		];
		$this->assign('fields',$fields);
        $search_fields = [
            [
                'label'=>'用户ID',
                'name'=>'uid',
                'formtype'=>'text',
            ],
            [
                'label'=>'开户名',
                'name'=>'bank_user',
                'formtype'=>'text',
            ],
            [
                'label'=>'雇员id',
                'name'=>'admin_id',
                'formtype'=>'text',
            ],
            [
                'label'=>'编号',
                'name'=>'id',
                'formtype'=>'text',
            ],
            [
                'label'=>'发起时间从',
                'name'=>'atime_sday',
                'formtype'=>'date',
            ],
            [
                'label'=>'发起时间至',
                'name'=>'atime_eday',
                'formtype'=>'date',
            ],
            [
                'label'=>'操作时间从',
                'name'=>'examine_time_sday',
                'formtype'=>'date',
            ],
            [
                'label'=>'操作时间至',
                'name'=>'examine_time_eday',
                'formtype'=>'date',
            ],
            [
                'label'=>'到账金额从',
                'name'=>'money_s',
                'formtype'=>'text',
            ],
            [
                'label'=>'到账金额至',
                'name'=>'money_e',
                'formtype'=>'text',
            ],
            [
                'label'=>'状态',
                'name'=>'status',
                'formtype'=>'select',
                'data'=>"return [
                    'field'	=&gt;[0,1],
                    'data'	=&gt;[
                        [1,'待审核'],
                        [2,'已通过'],
                        [3,'已驳回'],
                    ],
                ];",
            ]

        ];
        $this->assign('search_fields',$search_fields);
		$this->display();
	}
	/**
     * 提现审核页
     * Create by liangfeng
     * 2017-09-19
     */
	public function withdrawals_examine(){
		$id = I('get.id');
		$info = M('supplier_turnover')->find($id);
		$info['status_name'] = ['1'=>'待审核','2'=>'已结算','3'=>'已驳回'][$info['status']];
		$this->assign('info',$info);
		$this->display();
	}
	
	/**
     * 提现审核
     * Create by liangfeng
     * 2017-09-19
     */
	public function ajax_withdrawals_examine(){
		
		try{
	
			//获取信息
			$res = M('supplier_turnover')->find(I('post.id'));		
			
			if(!$res) throw new Exception('ID错误');
			if($res['status'] != 1) throw new Exception('此申请已经通过或者驳回，请勿重复提交');

			$do = M();
			$do->startTrans();
			
			if(false === M('supplier_turnover')->where(['id'=>$res['id']])->data(['admin_id'=>session('admin.id'),'status'=>I('post.status'),'reason'=>I('post.reason'),'examine_time'=>date('Y-m-d H:i:s')])->save()) throw new Exception('操作失败');
			
			
			//如果驳回，将用户提现金额减回去
			if(I('post.status') == 3){
				
				$withdrawals_money = M('supplier_user')->where(['uid'=>$res['uid']])->getField('withdrawals_money');
				$withdrawals_money = $withdrawals_money - $res['money'];
				if(false === M('supplier_user')->where(['uid'=>$res['uid']])->data(['withdrawals_money'=>$withdrawals_money])->save()) throw new Exception('扣除失败');
			}
			
			
			
			//throw new Exception('exit');
			
            $do->commit();
			
            $res = ['code'=>1,'msg'=>'审核成功'];
		}catch (Exception  $e){
			$do->rollback;
			$res = ['code'=>0,'msg' => $e->getMessage()];
		}
		
		$this->ajaxReturn($res);
	}

    /**
     * 导出提现数据
     * Create by liangfeng
     * 2017-09-19
     */
	public function export_data(){
        $cfg = I('get.');

        $fields = [
            ['label'=>'编号','name'=>'id'],
            ['label'=>'发起用户','name'=>'nick'],
            ['label'=>'发起时间','name'=>'atime'],
            ['label'=>'提现金额','name'=>'money'],
            ['label'=>'手续费','name'=>'charge'],
            ['label'=>'到账金额','name'=>'real_money'],
            ['label'=>'提现账户','name'=>'bank_no'],
            ['label'=>'开户地区','name'=>'bank_open_address'],
            ['label'=>'开户名','name'=>'bank_user'],
            ['label'=>'状态','name'=>'status_name'],
            ['label'=>'操作时间','name'=>'examine_time'],
            ['label'=>'拒绝原因','name'=>'reason'],
        ];
        //excel横列排序
        $out_excel_option = [];
        $out_option_orders = 'A';
        foreach($fields as $k => $v){
            $out_excel_option[$out_option_orders]['descript'] = $v['label'];
            $out_excel_option[$out_option_orders]['field'] = $v['name'];
            $out_option_orders++;
        }

        $do=M('supplier_turnover a');
        if($cfg['uid']) $map['a.uid'] = $cfg['uid'];
        if($cfg['bank_user']) $map['a.bank_user'] = $cfg['bank_user'];
        if($cfg['admin_id']) $map['a.admin_id'] = $cfg['admin_id'];
        if($cfg['id']) $map['a.id'] = $cfg['id'];
        if($cfg['status']) $map['a.status'] = $cfg['status'];

        if(empty($cfg['atime_sday'])) $cfg['atime_sday']	=	'2017-07-01';
        if(empty($cfg['atime_eday'])) $cfg['atime_eday']	=	date('Y-m-d',time()+86400);
        $map['a.atime']	=	['between',[$cfg['atime_sday'],$cfg['atime_eday']]];

        if(empty($cfg['examine_time_sday'])) $cfg['examine_time_sday']	=	'2017-07-01';
        if(empty($cfg['examine_time_eday'])) $cfg['examine_time_eday']	=	date('Y-m-d',time()+86400);
        $map['a.examine_time']	=	['between',[$cfg['examine_time_sday'],$cfg['examine_time_eday']]];

        if(empty($cfg['money_s'])) $cfg['money_s']	=	'0';
        if(empty($cfg['money_e'])) $cfg['money_e']	=	'9999999999';
        $map['a.money']	=	['between',[$cfg['money_s'],$cfg['money_e']]];

        $list = $do->field('*,a.id,a.atime,a.status,b.nick')->join('ylh_user as b on a.uid = b.id')->where($map)->order('a.atime desc')->limit(1000)->select();


        foreach($list as $k => $v){
            $list[$k]['charge'] = $v['money'] - $v['real_money'];
            $list[$k]['status_name'] = ['', '待审核', '已通过', '已驳回'][$v['status']];

        }
        //dump($list);
        D('Admin/Excel')->outExcel($list,$out_excel_option,'提现信息');
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