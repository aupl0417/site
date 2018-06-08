<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class DaigouController extends CommonModulesController {
	protected $name 			='代购申请';	//控制器名称
    protected $formtpl_id		=174;			//表单模板ID
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
     * 代购审核页面
     * @param int $_GET['id']	ID
     */
    public function view(){
		$daigou = getSiteConfig('daigou');
		$this->assign('daigou_cost_ratio',$daigou["daigou_cost_ratio"]);
		$this->assign('daigou_max_cost',$daigou["daigou_max_cost"]);
		$this->assign('daigou_min_cost',$daigou["daigou_min_cost"]);
	   
	    $do=M('daigou');
        $rs = $do->where(['id' => I('get.id')])->find();
        $do=M('user');
        $user_name = $do->where(['id' => $rs['uid']])->field('nick')->find();
        $rs['uid'] = $user_name['nick'];
        if ($rs['status'] == 1){
            $rs['result'] = "待审核";
        }else if($rs['status'] == 2){
            $rs['result'] = "审核通过";
		}else if($rs['status'] == 0){
            $rs['result'] = "已删除";
        }else{
            $rs['result'] = "被拒绝";
        }
        if($rs['images']){
			$img = explode(',',$rs['images']);
			$rs['images'] = $img;
		}
		
        $do=M('goods');
        $goods_name = $do->where(['is_daigou' => 1,'status'=>1])->field('id,goods_name')->select();
        $this->assign('goods',$goods_name);
        $this->assign('rs',$rs);
        $this->display();
    }
    /**
     * 代购审核
     * @param int $_GET['id']	ID
     */
    public function audit_daigou(){
        $do=M('daigou');
        $data = I('post.');
        $result = $do->where(['id'=> $data['id']])->find();

        if ($result['status'] ==2 || $result['status'] ==3){
            $this->ajaxReturn(['status' => 'warning','msg' =>'该代购已经审核过了，不能再次审核！']);
        }
        if(!isset($data['status']) || $data['status'] == ""){
            $this->ajaxReturn(['status' => 'warning','msg' =>'请选择审核结果！']);
        }
        if($data['status']==2){
            if ($data['goods_id'] == ""){
                $this->ajaxReturn(['status' => 'warning','msg' =>'请选择商品']);
            }
            $goods_attr = M("goods_attr_list");
            $goods_attr_id = $goods_attr->where(['goods_id' => $data['goods_id']])->field('id')->find();
            $data['attr_list_id'] = $goods_attr_id['id'];
			
			//获取用户手机号码，发送审核通过短信
			if(I('post.is_sms') == 1){
				$user = M("user");
				$tel = $user->where(['id' => $result['uid']])->getField('mobile');
				//dump($user->getLastSql());
				$tpl_id = 21;   //短信模板ID
				$sms_data['mobile'] = $tel;
				$sms_data['content']= $this->sms_tpl($tpl_id,['{goods_name}'],[$result['goods_name']]);
				sms_send($sms_data);
			}
        }else{
            if ($data['reason'] == ""){
                $this->ajaxReturn(['status' => 'warning','msg' =>'请填写拒绝理由']);
            }
        }
		
        $data['dotime'] = date('Y-m-d H:i:s',time());
        $data['admin_id'] = $_SESSION['admin']['id'];
        $result = $do->save($data);
        if ($result){
            $this->ajaxReturn(['status' => 'success','msg' =>'操作成功！']);
        }else{
            $this->ajaxReturn(['status' => 'warning','msg' =>'操作失败！']);
        }
    }
    /**
    * 商品列表
    */
	public function goods_list(){
		if(I('get.goods_name')){
			$map['goods_name'] =array('like','%'.I('get.goods_name')."%");
		} 
		$map['is_daigou'] = 1;
		$map['status'] = 1;
        $pagelist = pagelist(array(
            'table'         =>'goods',
            'do'            =>'M',
            'map'           =>$map,
            'pagesize'      =>15,
            'order'         =>'id desc',
            'ajax'          =>1,
        ));
		
	//	dump($pagelist);
        $this->assign('pagelist',$pagelist);

		$this->display();
	} 
	
	
    /**
    * 列表
    */
    public function index($param=null){
    	$this->_index();
    	//列表字段
//     	dump($this->_data );
		$btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a> <div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">审核</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
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
}