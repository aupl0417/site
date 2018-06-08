<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
vendor('Kdniao.ExpressBill#class'); //引入快递鸟接口
class OrdersshopController extends CommonModulesController {
	protected $name 			='商家订单管理';	//控制器名称
    protected $formtpl_id		=148;			//表单模板ID
    protected $fcfg				=array();		//表单配置
    protected $do 				='';			//实例化模型
    protected $map				=array();		//where查询条件
    protected $kdn; //快递鸟句柄

    /**
    * 初始化
    */
    public function _initialize() {
    	parent::_initialize();

    	//初始化表单模板
    	$this->_initform();

    	//dump($this->fcfg);

        $this->kdn =new \Kdniao\ExpressBill(C('cfg.kdniao'));

    }

    /**
    * 列表
    */
    public function index($param=null){

        if(I('get.linkname')){
            $map['_string'][]	= 'o_id in (select id from '.C('DB_PREFIX').'orders where linkname like "%'.I('get.linkname').'%")';
        }
        if(I('get.mobile')){
            $map['_string'][]	= 'o_id in (select id from '.C('DB_PREFIX').'orders where mobile like "%'.I('get.mobile').'%")';
        }
        if(I('get.tel')){
            $map['_string'][]	= 'o_id in (select id from '.C('DB_PREFIX').'orders where tel like "%'.I('get.tel').'%")';
        }
        if(I('get.street')){
            $map['_string'][]	= 'o_id in (select id from '.C('DB_PREFIX').'orders where street like "%'.I('get.street').'%")';
        }

        if(I('post.province')){
            $province=M('area')->where(['a_name' => I('get.province')])->getField('id');
            if(!$province){
                $this->display();
                exit;
            }

            $map['_string']		= 'o_id in (select id from '.C('DB_PREFIX').'orders where province='.$province.')';
        }

        if(I('post.city')){
            $city=M('area')->where(['a_name' => I('get.city')])->getField('id');
            if(!$city){
                $this->display();
                exit;
            }

            $map['_string']		= 'o_id in (select id from '.C('DB_PREFIX').'orders where city='.$city.')';
        }

        if($map['_string']) $map['_string']=implode(' and ',$map['_string']);

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
	* 订单详情
	* @param int $_GET['id']	订单ID
	*/
	public function view(){
		$do=D('OrdersShopViewRelation');

		$rs=$do->relation(true)->where(['id' => I('get.id')])->find();

		$area =	$this->cache_table('area');
		$rs['orders']['province']	=	$area[$rs['orders']['province']];
		$rs['orders']['city']		=	$area[$rs['orders']['city']];
		$rs['orders']['district']	=	$area[$rs['orders']['district']];
		$rs['orders']['town']		=	$area[$rs['orders']['town']];
		//dump($rs);

        //C('DEBUG_API',true);
        $rs['erp_status']   = $this->doApi('/Erp/check_orders_status',['s_no' => $rs['s_no']]);
        //dump($rs['erp_status']);

		$this->assign('rs',$rs);
		$this->display();
	}

    /**
     * 修复订单，订单实际已扣款成功却未修改状态的情况下进行修复
     */
    public function orders_fix(){
        $do = M('orders_shop');
        $rs = $do->where(['s_no' => I('post.s_no')])->find();
        if(!in_array($rs['status'],array(0,1,10))){
            $this->ajaxReturn(['status' => 'warning','msg' => '该状态下不充许执行此操作！']);
        }

        //C('DEBUG_API',true);
        //$user = M('user')->where(['id' => $rs['uid']])->field('openid')->find();
        //$res = $this->doApi('/Erp/orders_fix',['openid' => $user['openid'],'s_no' => $rs['s_no']]);
		//$res = A('Rest2/Orders')->check_ordres_in_erp(['s_no' => $rs['s_no'],'token' => $this->token]);
		$res = $this->doApi2('/Orders/check_ordres_in_erp',['s_no' => $rs['s_no']]);

        if($res['code'] == 1) $this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);
        else $this->ajaxReturn(['status' => 'warning','msg' => '操作失败！']);
    }

	/**
	* 订单日志
	*/
	public function orders_logs(){
		$orders=new \Common\Controller\OrdersController(array('s_no'=>I('get.s_no')));
		$res=$orders->orders_logs(0);	
		//dump($res);

		$this->assign('list',$res);
		$this->display();
	}	
	/**
	* 物流跟踪
	*/
	public function query_express(){
		$orders=new \Common\Controller\OrdersController(array('s_no'=>I('get.s_no')));
		$res=$orders->query_express_aliyun();
		//dump($res);
		//dump($res);
		$this->assign('rs',$res);
		$this->display();
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
		set_time_limit(0);
		if($this->fcfg['export_fields']){
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
				
				//订单商品表的字段
				$orders_goods_field = array(
					array('label'=>'分账金额','name'=>'inventory_monry'),
					array('label'=>'成本价','name'=>'cost_price'),
					array('label'=>'录入时间','name'=>'purchase_time'),
					array('label'=>'订单利润','name'=>'profit_price'),
					array('label'=>'财务录入运费退款','name'=>'refund_express_price'),
					array('label'=>'财务录入退款总金额','name'=>'refund_totals_price'),
					array('label'=>'录入退款时间','name'=>'refund_time'),
				);
				foreach($orders_goods_field as $v){
					$out_excel_option[$out_option_orders]['descript'] = $v['label'];
					$out_excel_option[$out_option_orders]['field'] = $v['name'];
					$out_option_orders++;
				}
				
				
			}else return false;
			//dump($field_names);exit;
			$cfg = eval(html_entity_decode($this->fcfg['export_fields']));
			
			
			
			//dump($fields);exit;
			//dump($cfg);exit;
			if($cfg['inventory_type']) $map['inventory_type'] = ['in',$cfg['inventory_type']];
			if($cfg['status'])	$map['status']	=	['in',$cfg['status']];
			if($cfg['terminal']) 	$map['terminal']	=	['in',$cfg['terminal']];
			if($cfg['pay_type'])	$map['pay_type']	=	['in',$cfg['pay_type']];

			if(empty($cfg['sday'])) $cfg['sday']	=	'2016-07-01';
			if(empty($cfg['eday'])) $cfg['eday']	=	date('Y-m-d',time()+86400);
			$map[$cfg['day_field']]	=	['between',[$cfg['sday'],$cfg['eday']]];

			if(empty($cfg['snum'])) $cfg['snum']	=	0;
			if(empty($cfg['enum'])) $cfg['enum']	=	10000000;
			$map[$cfg['num_field']]	=	['between',[$cfg['snum'],$cfg['enum']]];
			//自营选择
			if($cfg['shop_type']){
				//只选择非自营
				if($cfg['shop_type'][0] == '2'){
					$sql[] = 'shop_id in (select id from '.C('DB_PREFIX').'shop where type_id != 1)';
					
				//选择全部
				}else if($cfg['shop_type'][1] == '2'){
				
				//只选择自营
				}else{
					$sql[] = 'shop_id in (select id from '.C('DB_PREFIX').'shop where type_id = 1)';
				}
			}else{
				$sql[] = 'shop_id in (select id from '.C('DB_PREFIX').'shop where type_id < 1)';
			}
			
			//代购选择
			if($cfg['is_daigou']){
				//只选择代购
				if($cfg['is_daigou'][0] == '1'){
					$map['daigou_cost']	=	['gt',0];
				//选择全部
				}else if($cfg['is_daigou'][1] == '1'){
				
				//只选择自营
				}else{
					$map['daigou_cost']	=	['eq',0];
				}
			}else{
				$map['daigou_cost']	=	['lt',0];
			}
			
			if($cfg['shop_name']) $sql[]	=	'shop_id in (select id from '.C('DB_PREFIX').'shop where shop_name="'.$cfg['shop_name'].'")';
			if($cfg['nick']) $sql[]	=	'seller_id in (select id from '.C('DB_PREFIX').'user where nick="'.$cfg['nick'].'")';

			if($sql)	$map['_string']	=	implode(' and ',$sql);
			$list	=	M('orders_shop')->field('id,'.$field_names)->where($map)->order('id desc')->limit(1000)->select();
			//dump(M('orders_shop')->getlastsql());exit;
			$add_num = 0;
			foreach($list as $k => $v){
				//将数据中的字段转换
				foreach($v as $ke => $va){
					if($ke=='status'){
						$data = array('已删除','已拍下','已付款','已发货','已收货','已评价','已归档','','','','已关闭','已关闭');
						$list[$k+$add_num][$ke] = $data[$va];
					}else if($ke=='pay_type'){
						$data = array('','余额','唐宝','微信','','支付宝','','银联');
						$list[$k+$add_num][$ke] = $data[$va];
					}else if($ke=='express_type'){
						$data = array('','快递','EMS');
						$list[$k+$add_num][$ke] = $data[$va];
					}else if($ke=='inventory_type'){
						$data = array('扣除货款模式','扣除库存积分模式');
						$list[$k+$add_num][$ke] = $data[$va];
					}else if($ke=='terminal'){
						$data = array('PC','WAP','IOS','ANDROID');
						$list[$k+$add_num][$ke] = $data[$va];
					}else if($ke=='uid' || $ke=='seller_id'){
						$user_info = M('user')->cache(true)->field('nick')->where('id = '.$va)->find();
						$list[$k+$add_num][$ke] = $user_info['nick'];
					}else if($ke=='shop_id'){
						$shop_info = M('shop')->cache(true)->field('shop_name')->where('id = '.$va)->find();
						$list[$k+$add_num][$ke] = $shop_info['shop_name'];
					}else{
						$list[$k+$add_num][$ke] = ''.(string)$va.'';
					}
				}
				//真正的利润
				$profit_price = 0;
				//分账金额
				$inventory_monry = 0;
				//订单商品最早的成本录入时间
				$first_purchase_time = 0;
				
				$order_goods = M('orders_goods')->field('id,goods_name,attr_name,price,num,total_price_edit,score_ratio,score,cost_price,profit_price,refund_express_price,refund_totals_price,purchase_time,refund_time')->where('s_id = '.$v['id'])->select();
				foreach($order_goods as $ke => $va){
					//商品真正的利润
					$va['profit_price'] = $va['profit_price']+$va['refund_express_price']+$va['refund_totals_price'];
					//商品分账金额(保留2位小数其他舍去)
					$va['inventory_monry'] = sprintf("%.2f",substr(sprintf("%.3f", 0.08*$va['total_price_edit']*$va['score_ratio']), 0, -1));  
					
					
					//循环字段（将订单商品表的字段换成订单表的字段）
					foreach($va as $key => $val){
						$tmp = array();
						$tmp['shop_id'] = '商品名称：'.$va['goods_name'];
						$tmp['goods_price'] = $va['price'];
						$tmp['goods_num'] = $va['num'];
						$tmp['pay_price'] = $va['total_price_edit'];
						$tmp['score'] = $va['score'];
						$tmp['status'] = '积分比例：'.$va['score_ratio'];
						$tmp['cost_price'] = $va['cost_price'];
						$tmp['profit_price'] = $va['profit_price'];
						$tmp['refund_express_price'] = $va['refund_express_price'];
						$tmp['refund_totals_price'] = $va['refund_totals_price'];
						$tmp['purchase_time'] = strtotime($va['purchase_time']) !== false ? $va['purchase_time'] : '';
						$tmp['refund_time']   = $va['refund_time'];
						//$tmp['inventory_monry'] = $va['inventory_monry'];
					}
					//将换算好的数据覆盖原来
					$order_goods[$ke] = $tmp;
					
					//订单累计真正的利润
					$profit_price += $va['profit_price'];
					//订单累计分账金额
					$inventory_monry += $va['inventory_monry'];

					//获取商品中最早录入时间
					$purchase_time = strtotime($va['purchase_time']);
					if($first_purchase_time == 0 && $purchase_time !== false){
						$first_purchase_time = $purchase_time;
					}else if($purchase_time !== false && $first_purchase_time !== 0 && $purchase_time <= $first_purchase_time){
						$first_purchase_time = $purchase_time;
					}
				}
				$list[$k+$add_num]['profit_price'] = $profit_price;
				$list[$k+$add_num]['inventory_monry'] = $inventory_monry;
				$list[$k+$add_num]['purchase_time'] = $first_purchase_time > 0 ? date('Y-m-d H:i:s',$first_purchase_time) : '';
				
				//在订单列表中插入商品信息
				array_splice($list,$k+1+$add_num,0,$order_goods);
				//echo $k+1+$add_num;
				$add_num = $add_num+count($order_goods);
				//unset($t);
				//dump($list);
				//dump($t);
				//echo $k;
			}
			//array_splice($list,1,0,$t);
			//array_splice($list,2,0,$t);
			//dump($list);
			D('Admin/Excel')->outExcel($list,$out_excel_option,'订单信息');
		}else return false;

	}

	/**
     * 打印出货单
     */
	public function print_out_goods(){
        $do=D('OrdersShopViewRelation');

        $rs=$do->relation(true)->where(['id' => I('get.id')])->find();

        $area =	$this->cache_table('area');
        $rs['orders']['province']	=	$area[$rs['orders']['province']];
        $rs['orders']['city']		=	$area[$rs['orders']['city']];
        $rs['orders']['district']	=	$area[$rs['orders']['district']];
        $rs['orders']['town']		=	$area[$rs['orders']['town']];
        //dump($rs);


        $this->assign('rs',$rs);

        $html = $this->fetch('out_goods');
        //echo $html;


        vendor('mpdf60.mpdf');

        $mpdf=new \mPDF();

        $mpdf->autoScriptToLang = true;
        $mpdf->baseScript = 1;	// Use values in classes/ucdn.php  1 = LATIN
        $mpdf->autoVietnamese = true;
        $mpdf->autoArabic = true;

        $mpdf->autoLangToFont = true;

        $mpdf->SetDisplayMode('fullpage');

        $mpdf->WriteHTML($html);

        $mpdf->Output();

    }

    /**
     * 打印快递电子面单
     */
    public function print_express_bill(){
        $do=D('OrdersShopViewRelation');
        $rs=$do->relation(true)->where(['id' => I('get.id')])->find();
        $area =	$this->cache_table('area');
        $rs['orders']['province']	=	$area[$rs['orders']['province']];
        $rs['orders']['city']		=	$area[$rs['orders']['city']];
        $rs['orders']['district']	=	$area[$rs['orders']['district']];
        $rs['orders']['town']		=	$area[$rs['orders']['town']];

        if(!in_array($rs['status'],array(2,3))){
            echo '该订单状态下不可再次创建快递单！';
            exit();
        }

        $weight = 0;
        $num    = 0;
        foreach($rs['orders_goods'] as $key => $val){
            $weight += $val['total_weight'];
            $num    += $val['num'] - $val['refund_num'];
        }

        //dump($rs);
        //快递公司
        $express = M('express_company')->where(['id' => I('get.express_company_id')])->field('id,sub_name,kdniao_code,customer_name,customer_pwd,month_code,send_site')->find();
        //dump($express);
        if(empty($express['kdniao_code'])) {
            echo '该快递公司不支持电子面单！';
            exit();
        }

        //发货人资料
        $from = M('send_address')->where(['uid' => $rs['seller_id']])->field('id,atime,etime,ip,uid,is_default',true)->order('is_default desc')->find();
        if(!$from) {
            echo '未设置发货地址！';
            exit();
        }
        $from['province']	=	$area[$from['province']];
        $from['city']		=	$area[$from['city']];
        $from['district']	=	$area[$from['district']];
        $from['town']		=	$area[$from['town']];
        //dump($from);

        $eorder = [];
        $eorder["ShipperCode"]  = $express['kdniao_code'];
        $eorder["OrderCode"]    = $rs['s_no'];
        $eorder["PayType"]      = 1;    //1-现付，2-到付，3-月结，4-第三方支付
        $eorder["ExpType"]      = 1;    //快递类型：1-标准快件
        $eorder["Weight"]       = $weight;
        $eorder["Quantity"]     = $num;
        //$eorder["Remark"]       = '买家ID:'.$rs['user']['nick'];

        //快递公司账号密码密钥私钥
        $eorder['CustomerName']     = $express['customer_name'];
        $eorder['CustomerPwd']      = $express['customer_pwd'];
        $eorder['MonthCode']        = $express['month_code'];
        $eorder['SendSite']         = $express['send_site'];

        //发货人
        $sender = [];
        $sender["Name"]         = $from['linkname'];
        if($from['mobile']) $sender["Mobile"]       = $from['mobile'];
        if($from['tel']) $sender["Tel"]          = $from['tel'];
        $sender["ProvinceName"] = $from['province'];
        $sender["CityName"]     = $from['city'];
        $sender["ExpAreaName"]  = $from['district'].' '.$from['town'];
        $sender["Address"]      = $from['street'];

        //收货人
        $receiver = [];
        $receiver["Name"]           = $rs['orders']['linkname'];
        if($rs['orders']['mobile']) $receiver["Mobile"]         = $rs['orders']['mobile'];
        if($rs['orders']['tel']) $receiver["Tel"]            = $rs['orders']['tel'];
        $receiver["ProvinceName"]   = $rs['orders']['province'];
        $receiver["CityName"]       = $rs['orders']['city'];
        $receiver["ExpAreaName"]    = $rs['orders']['district'].' '.$rs['orders']['town'];
        $receiver["Address"]        = $rs['orders']['street'];

        $commodity[]    = [
            'GoodsName'     => '['.$rs['s_no'].']'.$rs['orders_goods'][0]['goods_name'] . '……（共件'.$num.'商品）',
            'Goodsquantity' => $num,
        ];
        //dump($eorder);
        //dump($sender);
        //dump($receiver);

        $res = $this->kdn->create_express_bill($eorder,$sender,$receiver,$commodity);

        //dump($res);
        /*
        if($res['ResultCode'] == 100) {
            echo $res['PrintTemplate'];
            exit;
        }else{
            dump($res);
            exit;
        }
        */

        //是否发货
        if(I('get.is_send') == 1 && !empty($res['Order']['LogisticCode']) && (I('get.express_company_id') != $rs['express_company_id'] || $res['Order']['LogisticCode'] != $rs['express_code'])){
            //dump($res['Order']['LogisticCode']);
            $orders=new \Common\Controller\SellerOrdersController(array('s_no'=>$rs['s_no'],'seller_id'=>$rs['seller_id']));

            $data = [
                'express_company_id'    => $express['id'],
                'express_code'          => $res['Order']['LogisticCode']
            ];

            if(empty($rs['express_code'])){ //发货
                $orders->send_express($data);
            }else{  //修改发货
                $orders->edit_express($data);
            }
        }

        if($res['PrintTemplate']){
            echo $res['PrintTemplate'];
            exit;
        }else{
            dump($res);
            exit;
        }

        vendor('mpdf60.mpdf');
        $mpdf=new \mPDF();

        $mpdf->autoScriptToLang = true;
        $mpdf->baseScript = 1;	// Use values in classes/ucdn.php  1 = LATIN
        $mpdf->autoVietnamese = true;
        $mpdf->autoArabic = true;

        $mpdf->autoLangToFont = true;

        $mpdf->SetDisplayMode('fullpage');

        $mpdf->WriteHTML($res['PrintTemplate']);

        $mpdf->Output();
    }

    public function select_express_company(){
        $company = D('Admin/ExpressCategoryRelation')->relation(true)->relationWhere('express_company','kdniao_code!=""')->where()->select();

        $this->assign('company',$company);
        $this->display();
    }
	
	//财务退款录入
	public function quit_money(){
		$do=D('OrdersShopViewRelation');
		$rs=$do->relation(true)->where(['id' => I('get.id')])->find();
        $rs['erp_status']   = $this->doApi('/Erp/check_orders_status',['s_no' => $rs['s_no']]);

		$this->assign('rs',$rs);
		$this->display();
    }
	
	//财务退款录入
	public function edit_orders_refund(){
		$do = M("orders_goods");
		$data['id']                   = I('post.id'); 
		$data['refund_totals_price']  = I('post.total'); 
		$data['refund_express_price'] = I('post.other'); 
		$data['refund_time']          = date('Y-m-d H:i:s',time());; 

		$res  = $do->save($data);
		if ($res !==false){
    	    $this->ajaxReturn(['status' => 'success','msg' => '请求成功!']);
        }else{
            $this->ajaxReturn(['status' => 'warning','msg' => "更新失败"]);
        }
    }
    
    /**
     * 收到退款
     */
    public function refund() {
        $do = D('Common/AdminRefundView');
        $data = $do->where(['id' => I('get.id')])->find();
        $expressCompany = M('express_company')->where(['status' => 1])->order('id asc')->field('id,company')->select();
        $this->assign('expressCompany', $expressCompany);
        $this->assign('rs', $data);
        $this->display();
    }
    
    /**
     * 收到退款保存
     */
    public function refundSave() {
        $data = I('post.');
        $do = D('Common/AdminRefundView');
        $rs = $do->where(['id' => $data['id']])->find();
        if (!$rs) $this->ajaxReturn(['status' => 'warning', 'msg' => '订单不存在']);
        $msg = '操作成功';
        $model = M();
        $times = date('Y-m-d H:i:s', NOW_TIME);
        $model->startTrans();
        $score = $data['price'] * $rs['score_ratio'] * 100; //积分
        $refundData = [
            'r_no'      =>  $this->create_orderno('RE',$rs['uid']),         //退款单号
            'ip'        =>  get_client_ip(),         //IP地址
            'uid'       =>  $rs['uid'],         //退款用户
            'seller_id' =>  $rs['seller_id'],         //退款卖家
            'shop_id'   =>  $rs['shop_id'],         //退款商家
            's_id'      =>  $rs['s_id'],         //商家订单ID
            's_no'      =>  $rs['s_no'],         //商家订单号
            'num'       =>  $data['num'] ? : 0,         //退货数量
            'money'     =>  $data['price'] ? : 0,         //退款金额
            'score'     =>  $score,         //退积分
            'status'    =>  100,         //退款状态
            'type'      =>  $data['type'],         //退款类型
            'reason'    =>  $data['remark'],         //退款理由
            'remark'    =>  $data['remark'],         //退款备注
            'images'    =>  $data['images1'] ? : '',    //退款证据
            'dotime'    =>  $times,         //操作时间
            'orders_status' =>  3,     //订单状态
            'accept_time'   =>  $times,     //同意退款时间
            'orders_goods_id'   =>  $data['id'], //商家订单商品ID
            'refund_express'    =>  $data['express_price'], //退运费金额
        ];
        
        $rId = M('refund')->add($refundData);
        if (!$rId) {
            $msg = '添加退款数据失败';
            goto error;
        }
        //申请日志
        $reason1 = '<p class="">雇员：<span class="strong text_red">'.$_SESSION['admin']['name'].'</span></p>';
        $reason1 .= '<p class="">为买家：<span class="strong text_red">'.$rs['buy_nick'].'</span></p>';
        $reason1 .= '<p class="">向商家：<span class="strong text_red">'.$rs['shop_name'].'</span>提交了退款申请。</p>';
        $reason1 .= '<p>';
        if($refundData['money'] > 0) $reason1 .= '申请退款金额：<span> '.$refundData['money'].' </span>元 ';
        if($refundData['refund_express'] > 0) $reason1 .= '申请退运费：<span> '.$refundData['refund_express'].' </span>元 ';
        if($refundData['num'] > 0) $reason1 .= '申请退货数量：<span> '.$refundData['num'].' </span>件';
        $reason1 .= '</p>';
        $refundLogs1 = [
            'ip'        =>  $refundData['ip'],
            'r_id'      =>  $rId,
            'r_no'      =>  $refundData['r_no'],
            'uid'       =>  $refundData['uid'],
            'a_uid'     =>  $_SESSION['admin']['id'],
            'status'    =>  1,
            'type'      =>  $refundData['type'],
            'images'    =>  $data['images1'] ? : '',
            'remark'    =>  $reason1,
        ];
        
        if (false == M('refund_logs')->add($refundLogs1)) {
            $msg = '添加申请日志失败';
            goto error;
        }
        //同意日志
        $reason2 = '<p class="">雇员：<span class="strong text_red">'.$_SESSION['admin']['name'].'</span></p>';
        $reason2 .= '<p class="">为商家：<span class="strong text_red">'.$rs['shop_name'].'</span></p>';
        $reason2 .= '<p class="">同意了：<span class="strong text_red">'.$rs['buy_nick'].'</span>的退款申请。</p>';
        if (!empty($data['express_company_id'])) {
            $reason2 .= '<p>快递公司：'.M('express_company')->where(['id' => $data['express_company_id']])->getField('company').'快递单号：'.$data['express_code'].'</p>';
        }
        $reason2 .= '<p>';
        if($refundData['money'] > 0) $reason2 .= '退款金额：<span> '.$refundData['money'].' </span>元 ';
        if($refundData['refund_express'] > 0) $reason2 .= '退运费：<span> '.$refundData['refund_express'].' </span>元 ';
        if($refundData['num'] > 0) $reason2 .= '退货数量：<span> '.$refundData['num'].' </span>件';
        $reason2 .= '</p>';
        
        $refundLogs2 = [
            'ip'        =>  $refundData['ip'],
            'r_id'      =>  $rId,
            'r_no'      =>  $refundData['r_no'],
            'uid'       =>  $refundData['seller_id'],
            'a_uid'     =>  $refundLogs1['a_uid'],
            'status'    =>  100,
            'type'      =>  $refundData['type'],
            'images'    =>  $data['images2'] ? : '',
            'remark'    =>  $reason2,
            'express_code'  =>  $data['express_code'] ? : '',
            'express_company_id'    =>  $data['express_company_id'] ? : '',
        ];
        if (false == M('refund_logs')->add($refundLogs2)) {
            $msg = '添加同意日志失败';
            goto error;
        }
        
        //退商品
//         if ($data['express_price'] > 0) {
//             if(!$model->execute('update '.C('DB_PREFIX').'orders_shop set refund_num=refund_num+'.$refundData['num'].',refund_price=refund_price+'.$refundData['money'].',refund_score=refund_score+'.$refundData['score'].',money=money-'.($refundData['money']+$refundData['refund_express']).',refund_express=refund_express+'.$refundData['refund_express'].' where s_no='.$refundData['s_no'])) {
//                 $msg = '更新订单退款金额(含运费)时失败';
//                 goto error;
//             }
//         } else {
//             if(!$model->execute('update '.C('DB_PREFIX').'orders_shop set refund_num=refund_num+'.$refundData['num'].',refund_price=refund_price+'.$refundData['money'].',refund_score=refund_score+'.$refundData['score'].',money=money-'.$refundData['money'].' where s_no='.$refundData['s_no'])) {
//                 $msg = '更新订单退款金额时失败';
//                 goto error;
//             }
//         }
        if(!$model->execute('update '.C('DB_PREFIX').'orders_shop set refund_num=refund_num+'.$refundData['num'].' where s_no='.$refundData['s_no'])) {
            $msg = '更新订单退货数量时失败';
            goto error;
        }
        if ($data['num'] > 0 || $data['price'] > 0) {
            if(!$model->execute('update '.C('DB_PREFIX').'orders_goods set refund_num=refund_num+'.$refundData['num'].',refund_price=refund_price+'.$refundData['money'].',refund_score=refund_score+'.$refundData['score'].' where id='.$refundData['orders_goods_id'])) {
                $msg = '更新订单商品退款金额及数量时失败';
                goto error;
            }
        }
        
        $model->commit();
        $this->ajaxReturn(['status' => 'success', 'msg' => $msg]);
        error :
            $model->rollback();
            $this->ajaxReturn(['status' => 'warning', 'msg' => $msg]);
    }


	/**
	 * 设置为刷单
	 */
	public function set_shuadan(){
		$do=M('orders_shop');
		if($do->where(['id' => ['in',I('post.id')]])->setField('is_shuadan',I('get.is_shuadan'))){
			$this->ajaxReturn(['status' =>'success','msg' =>'操作成功！']);
		}else{
			$this->ajaxReturn(['status' =>'warning','msg' =>'操作失败！']);
		}
	}
}