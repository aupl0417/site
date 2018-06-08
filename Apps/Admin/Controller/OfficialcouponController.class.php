<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class OfficialcouponController extends CommonModulesController {
	protected $name 			='官方优惠券';	//控制器名称
    protected $formtpl_id		=228;			//表单模板ID
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
        $options['map']['type'] = 2;
        $this->_index($options);
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
        $_POST['b_no']=$this->create_orderno('YT');
        if(I('post.sday') < date('Y-m-d')) $this->ajaxReturn(['status' => 'warning','msg' => '生效日期必须>=当前日期！']);
        if(I('post.sday') > I('post.eday')) $this->ajaxReturn(['status' => 'warning','msg' => '失效日期必须大于生效日期！']);
        if((I('post.price') * 2) > I('post.min_price')) $this->ajaxReturn(['status' => 'warning','msg' => '最低使用限额须>='.(I('post.price') * 2).'元！']);

        $map['price']       = I('post.price');
        $map['channel']     = I('post.channel');
        $map['face_type']   = I('post.face_type');
        $map['type']        = I('post.type');
        $map['use_type']    = I('post.use_type');

        if($rs = M('coupon_batch')->where($map)->field('sday,eday')->find()){
            if($rs['sday'] > I('post.eday') || $rs['eday'] < I('post.sday')){
            }else {
                $this->ajaxReturn(['status' => 'warning', 'msg' => '该有效时间段内已存在相同类型面值的优惠券！']);
            }
        }

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
        if((I('post.price') * 2) > I('post.min_price')) $this->ajaxReturn(['status' => 'warning','msg' => '最低使用限额须>='.(I('post.price') * 2).'元！']);

        $map['price']       = I('post.price');
        $map['channel']     = I('post.channel');
        $map['face_type']   = I('post.face_type');
        $map['type']        = I('post.type');
        $map['use_type']    = I('post.use_type');
        $map['id']          = ['neq',I('post.id')];

        if($rs = M('coupon_batch')->where($map)->field('sday,eday')->find()){
            if($rs['sday'] > I('post.eday') || $rs['eday'] < I('post.sday')){
            }else {
                $this->ajaxReturn(['status' => 'warning', 'msg' => '该有效时间段内已存在相同类型面值的优惠券！']);
            }
        }
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
     * 查看使用场景
     */
	public function use_type(){
	    $rs = M('coupon_batch')->where(['id' => I('get.id')])->field('id,use_type,shop_ids,goods_ids,category_ids')->find();

        switch($rs['use_type']){
            case 2:
                if($rs['shop_ids']) {
                    $do = D('Shop116Relation');
                    $list = $do->relation(true)->where(['id' => ['in', $rs['shop_ids']]])->field('id,shop_logo,shop_name,mobile,uid')->select();
                    $this->assign('list', $list);
                }

                $tpl = 'shop_ids';
                break;
            case 3:
                if($rs['goods_ids']) {
                    $do = D('Goods86Relation');
                    $list = $do->relation(true)->where(['id' => ['in',$rs['goods_ids']]])->field('id,status,images,goods_name,price,num,sale_num,shop_id,seller_id')->select();
                    $this->assign('list',$list);
                }

                $tpl = 'goods_ids';
                break;
            case 4:
                if($rs['category_ids']) {
                    $do = M('goods_category');
                    $list = $do->where(['id' => ['in',$rs['category_ids']]])->field('id,category_name')->select();
                    $this->assign('list',$list);
                }

                $tpl = 'category_ids';
                break;
            default:
                echo '通用型优惠券！';
                exit();
        }

        $this->display($tpl);
    }
}