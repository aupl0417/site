<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/23
 * Time: 11:22
 */

namespace Seller\Controller;
use Common\Builder\R;
use Common\Builder\F;
use Common\Form\Form;
use Think\Controller;
use Think\Exception;


/**
 * 用户组
 *
 * Class UserGroupController
 * @package Seller\Controller
 */

class SupplierController extends Controller
{
	private $seller_id;
    protected function _initialize(){
        C('cfg',getSiteConfig());
        //parent::_initialize();
		$this->seller_id = C('cfg.supplier')['seller_id'];
    }
	
	/**
     * 检查是否已经登录
     * Create by liangfeng
     * 2017-09-07
     */
	private function check_login(){
		if (!isset($_SESSION['user'])) {
            redirect(DM('user', '/login'));
            exit;
        }
	}
	/**
     * 检查是否已经登录(ajax)
     * Create by liangfeng
     * 2017-09-07
     */
	private function check_ajax_login(){
		if (!isset($_SESSION['user'])) {
            $this->ajaxReturn(['code'=>0,'msg'=>'请登录！']);
        }
	}
		
	
    /**
     * 检查是否已经申请入驻
     * Create by liangfeng
     * 2017-09-07
     */
    private function check_is_supplier(){
		if(!session('supplier_info')){
			$supplier_info = M('supplier_user')->where(['status'=>3,'uid'=>session('user.id')])->find();
			if(!$supplier_info){
				redirect(U('/Supplier/examine'));
			}else{
				$supplier_info['package'] = M('supplier_package')->where(['uid'=>$supplier_info['uid'],'status'=>3])->select();
				
				
				$supplier_info['gold_num'] = 0;
				$supplier_info['silver_num'] = 0;
				$supplier_info['cash_num'] = 0;
				
				foreach($supplier_info['package'] as $v){
					$tmp = explode(',',$v['package']);
					$supplier_info['gold_num'] += $v['gold_num'];
					$supplier_info['silver_num'] += $v['silver_num'];
					$supplier_info['cash_num'] += $v['cash_num'];
					foreach($tmp as $va){
						$supplier_info['packages'][] = $va;
					}
				}
				
				
				session('supplier_info',$supplier_info);
				
				
				
				
			}
		}
		$this->assign('info',session('supplier_info'));
       
    }

    /**
     * 供货商首页
     * Create by liangfeng
     * 2017-09-07
     */
    public function index(){
        $this->check_login();
        $this->check_is_supplier();
        

		
		$res = R::getInstance(['url' => ['total' => '/Seller/supplier_total','supplier_info'=>'/Supplier/get_info'], 'rest' => ['rest','rest2'], 'data' => [['openid'=>session('user.openid')],['openid'=>session('user.openid')]]])->multiCurl();
		
		//dump($res['total']['data']);
        $res['supplier_info']['data']['money'] = $res['supplier_info']['data']['sale_money'] - $res['supplier_info']['data']['withdrawals_money'];
		$this->assign('total',$res['total']['data']);
		$this->assign('supplier_info',$res['supplier_info']['data']);

		
		$this->display();
    }
	
	/**
     * 供货商信息页
     * Create by liangfeng
     * 2017-09-07
     */
    public function info(){
        $this->check_login();
        $this->check_is_supplier();
        
		$area = $this->cache_table('area');
		$this->assign('area',$area);
		$banks = F::getBankName();
        $this->assign('banks',$banks);
		
		$this->display();
    }

    /**
     * 供货商设置页
     * Create by liangfeng
     * 2017-09-19
     */
    public function setting(){
        $this->check_login();
        $this->check_is_supplier();
        $data['openid'] = session('user.openid');
        $res = R::getInstance(['url' => ['get_info' => '/Supplier/get_info'], 'rest' => ['rest2'], 'data' => [$data]])->multiCurl();
        $res = $res['get_info'];
        //dump($res);
        $config['action'] = U('/Supplier/ajax_setting_save');
        //dump($this->shop_info);
        $form = Form::getInstance($config)
            ->textarea(['name' => 'about', 'title' => '店铺描述', 'value' => $res['data']['about'], 'validate' => [ 'rangelength' => '[5,300]']])
            ->singleImages(['name' => 'logo', 'title' => '店铺logo',  'value' => $res['data']['logo']])
            ->text(['name' => 'tel', 'title' => '电话号码', 'value' => $res['data']['tel'], 'validate' => ['isTel']])
            ->text(['name' => 'qq', 'title' => '腾讯QQ', 'value' => $res['data']['qq'], 'validate' => ['number', 'min' => 10001, 'max' => 100000000000]])
            ->text(['name' => 'wang', 'title' => '阿里旺旺', 'value' => $res['data']['wang']])
            ->text(['name' => 'email', 'title' => '电子邮箱', 'value' => $res['data']['email'], 'validate' => ['email']])
            ->submit(['title' => '保存店铺信息'])
            ->create();
        $this->assign('form', $form);

        $this->display();
    }

	
	
    /**
     * 供货商入驻-上传资料
     * Create by liangfeng
     * 2017-09-07
     */
    public function upload_data(){
		$this->check_login();
        $banks = F::getBankName();
        $this->assign('banks',$banks);

		//读取省 和 入驻信息
        $res = R::getInstance(['url' => ['citys' => '/City/city_level','supplier_info'=>'/Supplier/get_supplier_info'], 'rest' => ['rest2','rest2'], 'data' => [['level'=>1],['openid'=>session('user.openid')]]])->multiCurl();
        $this->assign('citys',$res['citys']['data']);
		
		//unset($res['supplier_info']);
		
		if($res['supplier_info']['code'] == 1){
			$info = $res['supplier_info']['data'];
			$this->assign('info',$info);
			
			//读取市、区、街道
			$res = R::getInstance(['url' => ['city' => '/City/city_item','district' => '/City/city_item','town' => '/City/city_item','bank_city' => '/City/city_item'], 'rest' => ['rest2','rest2','rest2','rest2'], 'data' => [['sid'=>$info['province']],['sid'=>$info['city']],['sid'=>$info['district']],['sid'=>$info['bank_province']]]])->multiCurl();
			$this->assign('city',$res['city']['data']);
			$this->assign('district',$res['district']['data']);
			$this->assign('town',$res['town']['data']);
			$this->assign('bank_city',$res['bank_city']['data']);
			
			
			
		}else{
			$info['type'] = 1;
			$this->assign('info',$info);
		}
        
        //dump($res);
		$this->assign('level',1);
		
		
        $this->display();
    }

    /**
     * 供货商入驻-选择套餐
     * Create by liangfeng
     * 2017-09-07
     */
    public function change_package(){
		$this->check_login();
        //获取套餐价格 商品数量
        $this->assign('supplier',C('cfg.supplier'));
		
		//读取入驻信息
        $res = R::getInstance(['url' => ['supplier_info'=>'/Supplier/get_supplier_info'], 'rest' => ['rest2'], 'data' => [['openid'=>session('user.openid')]]])->multiCurl();
		if($res['supplier_info']['code'] == 1){
			$info = $res['supplier_info']['data'];
			foreach($info['package']['package_select'] as $v){
				$this->assign('select_'.$v,$v);
			}			
			$this->assign('info',$info);
		}else{
			redirect(U('/Supplier/upload_data'));
		}
		$this->assign('level',2);
		
        $this->display();
    }

    /**
     * 供货商入驻-付款凭证
     * Create by liangfeng
     * 2017-09-11
     */
    public function upload_voucher(){
		$this->check_login();
		
		//读取入驻信息
        $res = R::getInstance(['url' => ['supplier_info'=>'/Supplier/get_supplier_info'], 'rest' => ['rest2'], 'data' => [['openid'=>session('user.openid')]]])->multiCurl();
		if($res['supplier_info']['code'] == 1){
			$info = $res['supplier_info']['data'];	
			
			$this->assign('info',$info);
		}else{
			redirect(U('/Supplier/upload_data'));
		}
		
		
		$banks = F::getBankName();
        $this->assign('banks',$banks);
		
		$this->assign('supplier',C('cfg.supplier'));
		$this->assign('level',3);
		$this->display();
    }
	
	/**
     * 供货商入驻-雇员审核
     * Create by liangfeng
     * 2017-09-11
     */
    public function examine(){
		$this->check_login();
		
		$data['openid'] = session('user.openid');
		$res = R::getInstance(['url' => ['info' => '/Supplier/get_supplier_info'], 'rest' => ['rest2'], 'data' => [$data]])->multiCurl();

		if($res['info']['code'] == 0){
			redirect(U('/Supplier/upload_data'));
		}else if(empty($res['info']['data']['package'])){
			redirect(U('/Supplier/change_package'));
		}else if(empty($res['info']['data']['package']['pay_type'])){
			redirect(U('/Supplier/upload_voucher'));
		}else if($res['info']['data']['status'] == 3){
			redirect(U('/Supplier/success'));
		}
		//dump($res['info']);
		$this->assign('info',$res['info']['data']);
		
		
		
		
		$area = $this->cache_table('area');
		$this->assign('area',$area);
		
		$banks = F::getBankName();
        $this->assign('banks',$banks);
		
		
		$this->assign('level',4);
		
		$this->display();
    }
	
	/**
     * 供货商入驻-入驻成功
     * Create by liangfeng
     * 2017-09-12
     */
	public function success(){
		$data['openid'] = session('user.openid');
		$res = R::getInstance(['url' => ['info' => '/Supplier/get_supplier_info'], 'rest' => ['rest2'], 'data' => [$data]])->multiCurl();
		
		if($res['info']['data']['status'] != 3){
			redirect(U('/Supplier/examine'));
		}
		
		$this->assign('info',$res['info']['data']);
		
		$this->assign('level',5);
		$this->display();
	}


















    /**
     * 供货商保存店铺设置
     * Create by liangfeng
     * 2017-09-19
     */
    public function ajax_setting_save(){
        $this->check_ajax_login();

        $data = I('post.');
        unset($data['__hash__']);
        $data['openid'] = session('user.openid');

        $res = R::getInstance(['url' => ['save_edit_info' => '/Supplier/save_edit_info'], 'rest' => ['rest2'], 'data' => [$data]])->multiCurl();
        $this->ajaxReturn($res['save_edit_info']);
    }

	/**
     * 供货商入驻-申请审核
     * Create by liangfeng
     * 2017-09-12
     */
    public function ajax_examine(){
		$this->check_ajax_login();
		
		$data['openid'] = session('user.openid');
		
		$res = R::getInstance(['url' => ['examine' => '/Supplier/examine'], 'rest' => ['rest2'], 'data' => [$data]])->multiCurl();
		$res = $res['examine'];
		$this->ajaxReturn($res);
    }

	/**
     * 供货商入驻-上传付款凭证
     * Create by liangfeng
     * 2017-09-12
     */
    public function ajax_upload_voucher(){
		$this->check_ajax_login();
		unset($_POST['__hash__']);
		$data = I('post.');
		$data['openid'] = session('user.openid');
		//dump($data);exit();
		$res = R::getInstance(['url' => ['upload_voucher' => '/Supplier/upload_voucher'], 'rest' => ['rest2'], 'data' => [$data]])->multiCurl();
		//dump($res);
		$res = $res['upload_voucher'];
		$this->ajaxReturn($res);
    }

    /**
     * 供货商入驻-保存套餐选择
     * Create by liangfeng
     * 2017-09-11
     */
    public function ajax_change_package(){
		$this->check_ajax_login();
		
		unset($_POST['__hash__']);
		$data = I('post.');
        $data['openid'] = session('user.openid');
        
        //dump($data);exit();
        $res = R::getInstance(['url' => ['package_save' => '/Supplier/package_save'], 'rest' => ['rest2'], 'data' => [$data]])->multiCurl();
        $res = $res['package_save'];
        $this->ajaxReturn($res);
    }

    /**
     * 供货商入驻-保存上传资料
     * Create by liangfeng
     * 2017-09-08
     */
    public function ajax_upload_data_save(){
		$this->check_ajax_login();
        unset($_POST['__hash__']);
        $data = I('post.');
		$data['openid'] = session('user.openid');

        if($data['type'] == 1){
            $res = R::getInstance(['url' => ['upload_data_save' => '/Supplier/settled_personal'], 'rest' => ['rest2'], 'data' => [$data]])->multiCurl();
        }else{
            $res = R::getInstance(['url' => ['upload_data_save' => '/Supplier/settled_enterprise'], 'rest' => ['rest2'], 'data' => [$data]])->multiCurl();
        }
        $res = $res['upload_data_save'];
        //dump($res);
        $this->ajaxReturn($res);
    }

    /**
     * 查找下一级城市
     * Create by liangfeng
     * 2017-09-07
     */
    public function ajax_citys(){
        $res = R::getInstance(['url' => ['citys' => '/City/city_item'], 'rest' => ['rest2'], 'data' => [['sid'=>I('post.sid')]]])->multiCurl();
        $res = $res['citys'];

        if($res['code'] == 1){
            $html = '<option value="0">请选择</option>';
            foreach($res['data'] as $v){
                $html .= '<option value="'.$v['id'].'">'.$v['a_name'].'</option>';
            }
            $res['html'] = $html;
        }
        //$res['code'] = 3;

        $this->ajaxReturn($res);
    }

    /**
     * 上传图片
     * Create by liangfeng
     * 2017-09-07
     */
    public function ajax_upload(){
        try{
            if (empty($_FILES)) throw new Exception('没有上传图片');

            $data['openid'] = session('user.openid');
            $data['filebody'] = base64_encode(file_get_contents($_FILES['file']['tmp_name']));

            $res = R::getInstance(['url' => ['upload' => '/Upload/upload_base64'], 'rest' => ['rest2'], 'data' => [$data]])->multiCurl();
            $res = $res['upload'];
            if($res['code'] != 1) throw new Exception($res['msg']);

            $res = ['status'=>'success','url'=>$res['data']['url'],'msg'=>'成功'];
        }catch (Exception  $e){
            $res =  ['status' => 'warning','res'=>$res,'msg' => $e->getMessage()];
        }
        $this->ajaxReturn($res);


    }
}