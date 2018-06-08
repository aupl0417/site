<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class AdController extends CommonModulesController {
	protected $name 			='广告管理';	//控制器名称
    protected $formtpl_id		=70;			//表单模板ID
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
        switch (I('get.status')) {
            case 3: //投放中
                $map['status']  =1;
                $map['days']    =['like','%'.date('Y-m-d').'%'];
            break;
            case 4: //待投放
                $map['status']  =1;
                $map['sday']    =['gt',date('Y-m-d')];
            break;
            case 5: //已过期
                $map['status']  =1;
                $map['eday']    =['lt',date('Y-m-d')];
            break;
            
        }

        $map['is_default']	= 0;
    	$this->_index(['map' => $map]);
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a> <div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">详情</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
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
	* 广告详情
	*/
	public function view(){
		$do=D('AdRelation');

		$rs=$do->relation(true)->where(['id' => I('get.id')])->find();
		$this->assign('calendar',calendar(array('sday'=>$rs['sday'],'eday'=>$rs['eday'],'days'=>$rs['days'])));
		
		$this->assign('rs',$rs);
		$this->display();
	}

	/**
	* 导出设置
	*/
	public function export_set(){
		if($this->fcfg['export_fields']) $this->assign('rs',eval(html_entity_decode($this->fcfg['export_fields'])));
		# dump(eval(html_entity_decode($this->fcfg['export_fields'])));
		$this->display();
	}

	/**
	* 检查导出数据
	*/
	public function export_set_save(){
		//检查是否有选择导出字段
		if(!isset($_POST['field']) || empty($_POST['field'])){
			$this->ajaxReturn(['status' =>'warning','msg' =>'导出字段不能为空！']);
		}
		$do = M('formtpl');

		if(false!==$do->where(['id' => $this->formtpl_id])->save(['export_fields' => 'return '.var_export(I('post.'),true).';'])){
			//$this->export_file();

			$this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);
		}else{
			$this->ajaxReturn(['status' =>'warning','msg' =>'操作失败！']);
		}
	}


	/**
	 * 导出广告
	 */
	public function export_file(){
		set_time_limit(0);
		if($this->fcfg['export_fields']){
			//检查是否有选择导出字段
			if(isset($_POST['field']) && !empty($_POST['field'])){
				$field_ids = '';
				//将选中的导出字段排序
				foreach($_POST['field'] as $k => $v){
					$field_ids[] = $v;
				}
				$field_ids = implode(",", $field_ids);
				
				$fields = M('formtpl_fields')->field('name,label')->where(' id IN ('.$field_ids.')')->order('instr("'.$field_ids.'",id)')->select();
				if(count($fields) < 8){
					return false;
				}
				//excel横列排序
				$column = 'A';
				foreach($fields as $k => $v){
					$out_excel_option[$column]['descript'] 	= $v['label'];
					$out_excel_option[$column]['field'] 	= $v['name'];
					$column++;
					$field_names[] = $v['name'];
				}
				$field_names = implode(",", $field_names);
			}else{
				return false;
			}
			# dump($field_ids);exit;
			$cfg = eval(html_entity_decode($this->fcfg['export_fields']));
			# 查询条件
			$map = [];
			if($cfg['status']) $map['status'] = ['in',$cfg['status']];

			if($cfg['device'] || $cfg['position_name']) $where['device'] = ['in',$cfg['device']];
			if($cfg['position_name']) $where['position_name'] = $cfg['position_name'];
			if($where){
				$ad_position = M('ad_position')->where($where)->select();
				foreach ($ad_position as $value) {
					$arr[] = $value['id'];
				}
				if(empty($arr)){
					exit('无符合条件的数据');
					return false;
				}
				$map['position_id'] = ['in',$arr];
			}

			if($cfg['sday']) $map['sday'] = ['egt',$cfg['sday']];
			if($cfg['eday']) $map['eday'] = ['elt',$cfg['eday']];

			if($cfg['psday'] && $cfg['peday']){
				$map['pay_time'] = ['between',[date("Y-m-d H:i:s", strtotime($cfg['psday'])),date("Y-m-d H:i:s",strtotime($cfg['peday']) + 24 * 3600 - 1)]];
			}else if($cfg['psday']){
				$map['pay_time'] = ['egt',date("Y-m-d H:i:s", strtotime($cfg['psday']))];
			}else if($cfg['peday']){
				$map['pay_time'] = ['elt',date("Y-m-d H:i:s",strtotime($cfg['peday']) + 24 * 3600 - 1)];
			}

			if($cfg['snum'] && $cfg['enum']){
				$map['money_pay'] = ['between',[$cfg['snum'],$cfg['enum']]];
			}else if($cfg['snum']){
				$map['money_pay'] = ['egt', $cfg['snum']];
			}else if($cfg['enum']){
				$map['money_pay'] = ['elt', $cfg['enum']];
			}

			if($cfg['nick']) $map['uid'] = M('user')->where(['nick' => $cfg['nick']])->getField('id');
			# print_r($map);print_r($cfg);exit;
			$list	= D('Admin/AdRelation2')->relation(true)->field($field_names)->where($map)->order('id desc')->limit(1000)->select();
			# dump($where);dump($list);exit;
			foreach ($list as $key => $value) {
				$list[$key]['goods_id'] 	= $list[$key]['goods_name'];
				$list[$key]['sucai_id'] 	= $list[$key]['sucai_name'];
				$list[$key]['shop_id'] 		= $list[$key]['shop_name'];
				$list[$key]['type'] 		= ['商品','店铺','站外链'][$list[$key]['type']];
				$list[$key]['a_no'] 		= ' ' . $list[$key]['a_no'];
				$list[$key]['sort'] 		= '位置' . ($list[$key]['sort'] + 1);
				$list[$key]['status'] 		= ['未付款','已付款','违规'][$value['status']];
				$list[$key]['uid'] 			= $value['nick'] ? $value['nick'] : '';
				$list[$key]['position_id'] 	= $value['position_name'] ? $value['position_name'] : '';
				$list[$key]['pay_time'] 	= $value['pay_time'] != '0000-00-00 00:00:00' ? $value['pay_time']: '';
				$list[$key]['is_default'] 	= $value['is_default'] > 0 ? '是': '否';
			}

			D('Admin/Excel')->outExcel($list,$out_excel_option,'广告列表','广告导出');
		}else{
			return false;
		}
	}







}