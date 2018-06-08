<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class LuckdrawstatisticslistController extends CommonModulesController {
	protected $name 			='抽奖统计明细';	//控制器名称
    protected $formtpl_id		=204;			//表单模板ID
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
     * 导出设置
     */
    public function export_set(){
        if($this->fcfg['export_fields']) $this->assign('rs',eval(html_entity_decode($this->fcfg['export_fields'])));
        //dump(eval(html_entity_decode($this->fcfg['export_fields'])));
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
     * 导出数据
     */
    public function export_file(){
        //检查是否有选择导出字段
        if(isset($_POST['field']) && !empty($_POST['field'])){
            $field_ids = '';
            //将选中的导出字段排序
            foreach($_POST['field'] as $k => $v){
                $field_ids .= $v.',';
            }
            $field_ids = substr($field_ids,0,strlen($field_ids)-1);

            $fields = M('formtpl_fields')->field('name,label')->where(' id IN ('.$field_ids.')')->order('instr("'.$field_ids.'",id)')->select();

            if(count($fields) < 8){
                return false;
            }
            //excel横列排序
            $out_option_orders = 'A';
            foreach($fields as $k => $v){
                $out_excel_option[$out_option_orders]['descript'] = $v['label'];
                $out_excel_option[$out_option_orders]['field'] = $v['name'];
                $out_option_orders++;
                $field_names .= $v['name'].',';


            }
            $field_names = substr($field_names,0,strlen($field_names)-1);


        }else return false;

        //dump($order_field_names);
        //exit;
        $cfg = eval(html_entity_decode($this->fcfg['export_fields']));

        if($cfg['status'])	$map['status']	=	['in',$cfg['status']];
        if($cfg['type']) 	$map['type']	=	['in',$cfg['type']];

        if(empty($cfg['sday'])) $cfg['sday']	=	'2016-11-12';
        if(empty($cfg['eday'])) $cfg['eday']	=	date('Y-m-d',time()+86400);
        $map[$cfg['day_field']]	=	['between',[$cfg['sday'],$cfg['eday']]];



        if($cfg['shop_name']) $sql[]	=	'shop_id in (select id from '.C('DB_PREFIX').'shop where shop_name="'.$cfg['shop_name'].'")';
        if($cfg['nick']) $sql[]	=	'seller_id in (select id from '.C('DB_PREFIX').'user where nick="'.$cfg['nick'].'")';

        if($sql)	$map['_string']	=	implode(' and ',$sql);

        $list	=	M('luckdraw_statistics')->field('id,'.$field_names)->where($map)->order('id desc')->limit(1000)->select();


        foreach($list as $k => $v){
            //将数据中的字段转换
            foreach($v as $ke => $va){
                if($ke=='status'){
                    $data = array('','退款','卖家拒绝','修改','同意','买家寄出商品','卖家寄出商品',10=>'买家可申诉',11=>'卖家未收到退货',12=>'买家未收到货',20=>'退款已取消',100=>'退款已完成');
                    $list[$k+$add_num][$ke] = ' '.$data[$va];
                }else if($ke=='type'){
                    $data = array('','退货退款','只退款','退运费');
                    $list[$k+$add_num][$ke] = ' '.$data[$va];
                }else if($ke=='express_type'){
                    $data = array('','快递','EMS');
                    $list[$k+$add_num][$ke] = ' '.$data[$va];
                }else if($ke=='inventory_type'){
                    $data = array('非即时结算','即时结算');
                    $list[$k+$add_num][$ke] = ' '.$data[$va];
                }else if($ke=='terminal'){
                    $data = array('PC','WAP','IOS','ANDROID');
                    $list[$k+$add_num][$ke] = ' '.$data[$va];
                }else if($ke=='uid' || $ke=='seller_id'){
                    $user_info = M('user')->cache(true)->field('nick')->where('id = '.$va)->find();
                    $list[$k+$add_num][$ke] = ' '.$user_info['nick'];
                }else if($ke=='shop_id'){
                    $shop_info = M('shop')->cache(true)->field('shop_name')->where('id = '.$va)->find();
                    $list[$k+$add_num][$ke] = ' '.$shop_info['shop_name'];
                }else{
                    $list[$k+$add_num][$ke] = ' '.$va;
                }
            }
            if(isset($list[$k]['s_no'])){
                $res = M('orders_shop')->field($order_field_names)->where('id="'.$v['s_id'].'"')->find();
                $list[$k]['express_price'] = $res['express_price'];
                $list[$k]['goods_price_edit'] = $res['goods_price_edit'];
                $list[$k]['express_price_edit'] = $res['express_price_edit'];
                $list[$k]['pay_price'] = $res['pay_price'];
                $list[$k]['total_price'] = $res['total_price'];
                $list[$k]['pay_time'] = $res['pay_time'];
                $data = array('','余额','唐宝','支付宝','微信');
                $list[$k]['pay_type'] = $data[$res['pay_type']];
            }


        }

        D('Admin/Excel')->outExcel($list,$out_excel_option,'统计信息');
    }
}