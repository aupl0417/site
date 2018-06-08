<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 店铺设置
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
class ShopSettingController extends CommonController {
	protected $action_logs = array('shop_info_save','set_domain');
	public function index(){
		redirect(C('sub_domain.www'));
	}

	/**
	* 店铺资料
	* @param int $_POST['openid']  用户openid
	*/
	public function shop_info(){
		//频繁请求限制,间隔2秒
		//$this->_request_check();

		//必传参数检查
		$this->need_param=array('openid','sign');
		$this->_need_param();
		$this->_check_sign();	

		$status_name=['暂停营业','营业中','强制关闭'];

		$do=D('Common/ShopRelation');
		$rs=$do->relation(true)->where(['uid' => $this->uid])->field('etime,ip',true)->find();

		if($rs) {
			$shop_type 	=	$this->cache_table('shop_type');
			$area 		=	$this->cache_table('area');
            $rs['province_name']    =$area[$rs['province']];
            $rs['city_name']        =$area[$rs['city']];
            $rs['district_name']    =$area[$rs['district']];
            $rs['town_name']        =$area[$rs['town']];  
	
			$rs['status_name']		=$status_name[$rs['status']];
			$rs['shop_url']			=shop_url($rs['id'],$rs['domain']);
			$rs['type_name']		=$shop_type[$rs['type_id']];
			$rs['logo']				=myurl($rs['shop_logo'],100);
			
			if($rs['category_id']){
				$goods_category 	=	$this->cache_table('goods_category');
				$category_id=explode(',',$rs['category_id']);
				foreach($category_id as $val){
					$rs['category_name'][]	=	$goods_category[$val];
				}
			}

			$this->apiReturn(1,['data' => $rs]);
		}else $this->apiReturn(3);
	}


	/**
	* 保存店铺资料设置
	* @param int $_POST['openid']  		用户openid
    * @param string $_POST['shop_logo'] 店铺logo
    * @param string $_POST['about']     店铺描述
    * @param string $_POST['province']  省份
    * @param string $_POST['city']      城市
    * @param string $_POST['district']  区域
    * @param string $_POST['town']      街道
    * @param string $_POST['street']    详细地址
    * @param string $_POST['qq']        QQ
    * @param string $_POST['mobile']    手机
    * @param string $_POST['tel']       电话
    * @param string $_POST['email']     邮箱
	*/
	public function shop_info_save(){
		//频繁请求限制,间隔2秒
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('openid','about','province','city','district','street','qq','mobile','sign');
		$this->_need_param();
		$this->_check_sign();


		$data=[
			'shop_logo'		=>I('post.shop_logo'),
			'about'			=>I('post.about'),
			'province'		=>I('post.province'),
			'city'			=>I('post.city'),
			'district'		=>I('post.district'),
			'town'			=>I('post.town'),
			'street'		=>I('post.street'),
			'qq'			=>I('post.qq'),
			'mobile'		=>I('post.mobile'),
			'tel'			=>I('post.tel'),
			'email'			=>I('post.email'),
			# 'inventory_type'=>I('post.inventory_type', 0, 'int')
		];

        $do=D('Common/ShopInfo');
        if(!$do->create($data)) $this->apiReturn(4,'',1,$do->getError());

        if($do->where(['uid' => $this->uid])->save($data) === false) $this->apiReturn(0);

        $this->apiReturn(1);
	}

	public function inventory_type_save(){
		$this->apiReturn(0);
		# 频繁请求限制,间隔2秒
		$this->_request_check();

		# 必传参数检查
		$this->need_param = array('inventory_type','openid', 'password_pay');
		$this->_need_param();
		$this->_check_sign();
		# 验证安全密码
		$this->check_password_pay(I('password_pay'));

		$inventory_type = I('inventory_type','','int');
		if(in_array($inventory_type, [0,1], true)){
			$result = M('shop')->where(['uid' => $this->uid])->data(['inventory_type' => $inventory_type])->save();
			if(is_int($result)){
				$this->apiReturn(1);
			}else{
				$this->apiReturn(0);
			}
		}else{
			$this->apiReturn(0);
		}
	}


    /**
    * 设置店铺域名
    * @param int $_POST['openid']	用户openid
    * @param int $_POST['domain']	域名前缀
    */
    public function set_domain(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','domain','sign');
        $this->_need_param();
        $this->_check_sign();      

        $check=A('OpenShop');
        $res=$check->_check_domain(I('post.domain'),$this->uid);

        if($res['code']!=1) $this->apiReturn($res['code']);

        $do=M('shop');
        if($do->where(['uid' => $this->uid])->save(['domain' => I('post.domain')])) $this->apiReturn(1);
        else $this->apiReturn(0);

    }


}