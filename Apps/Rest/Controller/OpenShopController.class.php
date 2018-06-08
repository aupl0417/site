<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 开店
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
+ 暂时没应用到此接口
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class OpenShopController extends CommonController {
    /**
    * 判断用户是否已开店
    * @param string $_POST['openid']    用户openid
    */
    public function is_open(){
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();      
        
        if($this->user['shop_type']==0) $this->apiReturn(652); //未开店
        $this->apiReturn(1);
    }

    /**
    * 店铺类型
    */
    public function shop_type(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();   

        //暂停个人店铺及自营店
        $list=M('shop_type')->cache(true,C('CACHE_LEVEL.OneDay'))->where(['id' => ['not in','1'], 'status' => 1])->field('atime,etime',true)->select();

        //找不到记录
        if(!$list) $this->apiReturn(3);
        $this->apiReturn(1,['data' => $list]);

    }

    /**
    * 获取某种店铺类型
    * @param int $_POST['id']    店铺类型ID
    */
    public function shop_type_view(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('id','sign');
        $this->_need_param();
        $this->_check_sign();   

        //暂停个人店铺及自营店
        $rs=M('shop_type')->cache(true,C('CACHE_LEVEL.OneDay'))->where(['id' => I('post.id')])->field('atime,etime',true)->find();

        //找不到记录
        if(!$rs) $this->apiReturn(3);
        $this->apiReturn(1,['data' => $rs]);

    }    

    /**
    * 检查店铺域名是否可用
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['domain']    店铺域名前缀
    */
    public function check_domain(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('domain','openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $res=$this->_check_domain(I('post.domain'),$this->uid);

        $this->apiReturn($res['code'],['data' => ['domain' => I('post.domain')]],1,$res['msg']);
    }    

    /**
    * 检查店铺域名是否可用
    * @param string $domain  店铺域名前缀
    * @param int  用户ID
    */
    public function _check_domain($domain,$uid){
        //域名格式不符合要求，必须是在5~20位之间的字母或字母和数字的组合！
        if(!checkform($domain,'domain')) return ['code' => 899,'msg' => C('error_code.899')];

        $notdomain=M('shop_notdomain')->cache(true,C('CACHE_LEVEL.OneDay'))->getField('domain',true);
        $notdomain=implode(',',$notdomain);
        $notdomain=explode(',',$notdomain);

        //含有禁用关键词
        if(in_array($domain,$notdomain)){
            $result['code']=906;
            return $result;
        }

        //判断是否被其它用户使用
        if(M('shop')->where(['uid' => ['neq',$uid] , 'domain' => $domain])->count()>0){
            $result['code']=900;
        }else {
            $result['code']=1;
            $result['msg']=C('error_code.901');
        }

        return $result;
    }

    /**
    * 检查店铺名称是否可使用
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['shop_name'] 店铺名称
    */
    public function check_shop_name(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('shop_name','openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $res=$this->_check_shop_name(I('post.shop_name'),$this->uid);

        $this->apiReturn($res['code'],['data' => ['shop_name' => I('post.shop_name')]],1,$res['msg']);
    }  

    /**
    * 检查店铺名称是否可用
    * @param string $shop_name  店铺名称
    * @param int  用户ID
    */
    public function _check_shop_name($shop_name,$uid){
        $notname=M('shop_notname')->cache(true,C('CACHE_LEVEL.OneDay'))->getField('name',true);
        $notname=implode(',',$notname);
        $notname=explode(',',$notname);
        
        foreach($notname as $val){
            if(strstr($shop_name,$val)){
                $result['code']=0;
                $result['msg']=C('error_code.905')."“{$val}”";
                return $result;
            }
        }     

        //判断是否被其它用户使用
        if(M('shop')->where(['uid' => ['neq',$uid] , 'shop_name' => $shop_name])->count()>0){
            $result['code']=0;
            $result['msg']=C('error_code.903');
        }else{
            $result['code']=1;
            $result['msg']=C('error_code.904');            
        }

        return $result;
    }
	
	
	/**
	* 开店入驻协议
	*/
	public function agreement(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();
		
		$id=6;
		$rs=M('help')->cache(true,C('CACHE_LEVEL.OneDay'))->where(['id' => $id])->field('name,content')->find();
		
		if(!$rs) $this->apiReturn(3);
		
        $rs['content']=html_entity_decode($rs['content']);
		$this->apiReturn(1,['data' => $rs]);
		
	}

    /**
    * 商家入驻须知
    */
    public function notice(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();
        
        $id=7;
        $rs=M('help')->cache(true,C('CACHE_LEVEL.OneDay'))->where(['id' => $id])->field('name,content')->find();
        
        if(!$rs) $this->apiReturn(3);
        
        $rs['content']=html_entity_decode($rs['content']);
        $this->apiReturn(1,['data' => $rs]);
        
    }

    /**
    * 商家入驻合同
    */
    public function contract(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();
        
        $id=8;
        $rs=M('help')->cache(true,C('CACHE_LEVEL.OneDay'))->where(['id' => $id])->field('name,content')->find();
        
        if(!$rs) $this->apiReturn(3);
        
        $rs['content']=html_entity_decode($rs['content']);
        $this->apiReturn(1,['data' => $rs]);
        
    }

    /**
    * 标记已签订合同
    * @param string $_POST['openid']    用户openid
    */
    public function accept_contract(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('shop_join_info');
        if($rs=$do->where(['uid' => $this->uid , 'status' => ['gt',0]])->find()){
            $this->apiReturn(911); //您已签订合同，请进入下一步操作！
        }

        if($do->where(['uid' => $this->uid ])->setField('status',1)){
            //更新步骤
            M('shop_join_contact')->where(['uid'=>$this->uid])->setField('step',4);

            $this->apiReturn(1);
        }else $this->apiReturn(0);
    }

    /**
    * 取开店联系人资料
    * @param string $_POST['openid']    用户openid
    */
    public function contact_info(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign(); 

        $do=M('shop_join_contact');
        $rs=$do->where(['uid' => $this->uid ])->field('atime,etime,ip',true)->find();

        if(!$rs) $this->apiReturn(3); //找不到记录
        $this->apiReturn(1,['data' => $rs]);
    }

    /**
    * 登记开店联系人信息
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['linkname']  负责人姓名
    * @param string $_POST['mobile']    负责人手机
    * @param string $_POST['tel']       负责人电话
    * @param string $_POST['email']     负责人邮箱
    * @param string $_POST['qq']        QQ    
    * @param string $_POST['rf_linkname']   退货联系人
    * @param string $_POST['rf_mobile']     退货手机
    * @param string $_POST['rf_tel']        退货电话
    * @param int $_POST['rf_province']      退货省份
    * @param int $_POST['rf_city']          退货城市
    * @param int $_POST['rf_district']      退货地区
    * @param string $_POST['rf_street']     退货详细地址
    * @param string $_POST['type_id']       店铺类型

    */
    public function contact_info_add(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','linkname','mobile','tel','email','qq','rf_linkname','rf_mobile','rf_tel','rf_province','rf_city','rf_district','rf_street','type_id','sign');
        $this->_need_param();
        $this->_check_sign(); 

        $do=M('shop_join_contact');

        $rs=$do->where(['uid' => $this->uid])->field('ip,atime,etime',true)->find();

        //已登记过联系人信息
        if($rs) $this->apiReturn(907,['data' => $rs]);

        $_POST['uid']=$this->uid;
        if(!$data=D('Common/ShopJoinContact')->create()){
            $this->apiReturn(4,'',1,D('Common/ShopJoinContact')->getError());
        }

        if(!D('Common/ShopJoinContact')->add()){
            $this->apiReturn(0);
        }

        $data['id']=D('Common/ShopJoinContact')->getLastInsID();
        $this->apiReturn(1,['data' => $data]);
    }

    public function contact_info_edit(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','linkname','mobile','tel','email','qq','rf_linkname','rf_mobile','rf_tel','rf_province','rf_city','rf_district','rf_street','type_id','sign');
        $this->_need_param();
        $this->_check_sign(); 

        $do=M('shop_join_contact');

        $rs=$do->where(['uid' => $this->uid])->field('id')->find();

        //联系人信息不存在，无法修改！
        if(!$rs) $this->apiReturn(912);

        $_POST['id']=$rs['id'];
        $_POST['uid']=$this->uid;
        if(!$data=D('Common/ShopJoinContact')->create()){
            $this->apiReturn(4,'',1,D('Common/ShopJoinContact')->getError());
        }

        if(!D('Common/ShopJoinContact')->save()){
            $this->apiReturn(0);
        }
        $this->apiReturn(1);
    }

    /**
    * 添加品牌
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['b_name']    品牌名称
    * @param string $_POST['b_logo']    品牌logo
    * @param string $_POST['b_images']  品牌注册证
    * @param string $_POST['b_master']  品牌所有者
    * @param string $_POST['b_code']    品牌商标注册号，当只有授理书时可不填
    * @param string $_POST['b_type']    品牌类型
    * @param string $_POST['b_scope']   经营类型
    * @param string $_POST['type_id']   店铺类型
    */
    public function brand_add(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','b_name','b_logo','b_master','b_type','b_scope','sign');
        if(I('post.b_code')!='') {
            $this->need_param[]='b_code';   //不有商标注册号时，b_code和b_images加入签名
            $this->need_param[]='b_images';
        }

        if(I('post.b_code')=='') {
            $this->need_param[]='b_images2';
        }

        $this->_need_param();
        $this->_check_sign();

        //充许品牌最大数量验证
        $type=D('ShopJoinShopTypeView')->where(['shop_join_contact.uid' => $this->uid])->field('max_brand')->find();
        $count=M('shop_join_brand')->where(['uid' => $this->uid])->count();
        if($type['max_brand'] < $count && $type['max_brand']>0) $this->apiReturn(4,'',1,str_replace('{max_brand}', $type['max_brand'], C('error_code.914')));


        $rs=M('shop_join_brand')->where(['uid' => $this->uid , 'b_name' => I('post.b_name')])->field('atime,etime,ip',true)->find();
        if($rs) $this->apiReturn(908,['data' => $rs]); //品牌已存在

        $_POST['uid']=$this->uid;
        if(!$data=D('Common/ShopJoinBrand')->create()){
            $this->apiReturn(4,'',1,D('Common/ShopJoinBrand')->getError());
        }

        if(!$data['id']=D('Common/ShopJoinBrand')->add()){
            $this->apiReturn(0);
        }

        //更新步骤
        M('shop_join_contact')->where(['uid'=>$this->uid])->setField('step',2);

        $this->apiReturn(1,['data' => $data]);
    }


    /**
    * 删除品牌
    * @param int $_POST['id']  品牌记录ID
    */
    public function brand_delete(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        if(M('shop_join_brand')->where(['uid' => $this->uid , 'id'=>I('post.id')])->delete()){
            $this->apiReturn(1);
        }else{
            $this->apiReturn(0);
        }
    }

    /**
    * 获取品牌列表
    * @param string $_POST['openid']    用户openid
    */
    public function brand(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $list=M('shop_join_brand')->where(['uid' => $this->uid ])->field('atime,etime,ip',true)->select();

        if(!$list) $this->apiReturn(3);
        else $this->apiReturn(1,['data' => $list]);
    }


    /**
    * 添加资质
    * @param string $_POST['openid']        用户openid
    * @param int    $_POST['category_id']   资质类目
    * @param string $_POST['cert_name']     资质名称
    * @param string $_POST['cert_images']   资质证书照片
    * @param date   $_POST['expire_day']    有效期限
    */
    public function cert_add(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','category_id','cert_name','cert_images','sign');
        $this->_need_param();
        $this->_check_sign();

        //充许资质最大数量验证
        $type=D('ShopJoinShopTypeView')->where(['shop_join_contact.uid' => $this->uid])->field('max_cert')->find();
        $count=M('shop_join_cert')->where(['uid' => $this->uid])->count();
        if($type['max_cert'] < $count && $type['max_cert']>0) $this->apiReturn(4,'',1,str_replace('{max_cert}', $type['max_cert'], C('error_code.915')));


        $rs=M('shop_join_cert')->where(['uid' => $this->uid , 'cert_name' => I('post.cert_name')])->field('atime,etime,ip',true)->find();

        if($rs) $this->apiReturn(909,['data' => $rs]); //资质已存在

        $_POST['uid']=$this->uid;
        if(!$data=D('Common/ShopJoinCert')->create()){
            $this->apiReturn(4,'',1,D('Common/ShopJoinCert')->getError());
        }

        if(!$data['id']=D('Common/ShopJoinCert')->add()){
            $this->apiReturn(0);
        }

        //更新步骤
        M('shop_join_contact')->where(['uid'=>$this->uid])->setField('step',2);        

        $this->apiReturn(1,['data' => $data]);

    }

    /**
    * 删除资质
    * @param strint $_POST['openid']    用户openid
    * @param int $_POST['id']  资质记录ID
    */
    public function cert_delete(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        if(M('shop_join_cert')->where(['uid' => $this->uid , 'id'=>I('post.id')])->delete()){
            $this->apiReturn(1);
        }else{
            $this->apiReturn(0);
        }
    }

    /**
    * 获取资质列表
    * @param strint $_POST['openid']    用户openid
    */
    public function cert(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $list=M('shop_join_cert')->where(['uid' => $this->uid ])->field('atime,etime,ip',true)->select();
		
        if(!$list) $this->apiReturn(3);
        else {
			$goods_category	=	$this->cache_table('goods_category');
			foreach($list as $i=>$val){
				$list[$i]['category_name']	=	$goods_category[$val['category_id']];
			}			
			$this->apiReturn(1,['data' => $list]);
		}
    }


    /**
    * 添加经营类目
    * @param string $_POST['openid']        用户openid
    * @param int    $_POST['category_id']   资质类目
    * @param string $_POST['category_second']     资质名称
    * @param string $_POST['cert_images']   资质证书照片
    * @param date   $_POST['expire_day']    有效期限
    */
    public function category_add(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','category_id','sign');
        $this->_need_param();
        $this->_check_sign();

        //充许类目最大数量验证
        $type=D('ShopJoinShopTypeView')->where(['shop_join_contact.uid' => $this->uid])->field('max_category')->find();
        $count=M('shop_join_category')->where(['uid' => $this->uid])->count();
        if($type['max_category'] < $count && $type['max_category']>0) $this->apiReturn(4,'',1,str_replace('{max_category}', $type['max_category'], C('error_code.916')));


        $rs=M('shop_join_category')->where(['uid' => $this->uid , 'category_id' => I('post.category_id')])->field('atime,etime,ip',true)->find();

        if($rs) $this->apiReturn(909,['data' => $rs]); //资质已存在

        $_POST['uid']=$this->uid;
        if(!$data=D('Common/ShopJoinCategory')->create()){
            $this->apiReturn(4,'',1,D('Common/ShopJoinCategory')->getError());
        }

        if(!$data['id']=D('Common/ShopJoinCategory')->add()){
            $this->apiReturn(0);
        }

        //更新步骤
        M('shop_join_contact')->where(['uid'=>$this->uid])->setField('step',2);

        $this->apiReturn(1,['data' => $data]);

    }

    /**
    * 删除经营类目
    * @param strint $_POST['openid']    用户openid
    * @param int $_POST['id']  类目记录ID
    */
    public function category_delete(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        if(M('shop_join_category')->where(['uid' => $this->uid , 'id'=>I('post.id')])->delete()){
            $this->apiReturn(1);
        }else{
            $this->apiReturn(0);
        }
    }

    /**
    * 获取经营类目列表
    * @param strint $_POST['openid']    用户openid
    */
    public function category(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $list=M('shop_join_category')->where(['uid' => $this->uid ])->field('atime,etime,ip',true)->select();

        if(!$list) $this->apiReturn(3);
        else {
			$goods_category 	=	$this->cache_table('goods_category');
			foreach($list as $i=>$val){
				$list[$i]['category_name']	=	$goods_category[$val['category_id']];

				/*
				$val['category_second']=explode(',',$val['category_second']);
				foreach($val['category_second'] as $v){
					$list[$i]['category_second_name'][]=get_key_by_list(array('table'=>'goods_category','field'=>'id,category_name','key_val'=>$v,'cache_name'=>'table_goods_category'));
				}
				*/

			}			
			$this->apiReturn(1,['data' => $list]);
		}
    }

    /**
    * 店铺信息
    * @param string $_POST['openid']    用户openid
    */
    public function shop_info(){
        //频繁请求限制,间隔2秒
        $this->_request_check();


        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('shop_join_info');
        $rs=$do->where(['uid' => $this->uid])->field('atime,etime,ip',true)->find();
        //if(!$rs) $this->apiReturn(913);    //找不到记录

        //店铺类型
        $type=D('ShopJoinShopTypeView')->where(['shop_join_contact.uid' => $this->uid])->field('id,type_name')->find();

        $this->apiReturn(1,['data' => $rs,'shop_type'=>$type]);
    }

    /**
    * 设置店铺名称
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['shop_name'] 店铺名称
    * @param string $_POST['type_id']   店铺类型
    * @param string $_POST['about']     店铺描述
    * @param int    $_POST['inventory_type']    库存积分结算方式，0=非即时结算,1=即时结算（须先购买库存积分）
    */
    public function shop_info_add(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //$_POST['shop_name']=trimall(I('post.shop_name'));   //过滤所有空格

        //必传参数检查
        $this->need_param=array('openid','shop_name','type_id','about','inventory_type','sign');
        $this->_need_param();
        $this->_check_sign();

        //检查店铺名称是否可用
        $res=$this->_check_shop_name(I('post.shop_name'),$this->uid);
        if($res['code']!=1) $this->apiReturn($res['code'],'',1,$res['msg']);

        $rs=M('shop_join_info')->where(['uid' => $this->uid ])->field('atime,etime,ip',true)->find();
        if($rs) $this->apiReturn(910,['data' => $rs]);  //已添加过店铺信息

        $type=M('shop_type')->cache('shop_type_'.I('post.type_id'),C('CACHE_LEVEL.XXL'))->where(['id' => I('post.type_id')])->field('bond_price,max_category,max_cert,max_brand')->find();
        //检查充许品牌数量
        $count=M('shop_join_brand')->where(['uid' => $this->uid])->count();
        if($type['max_brand'] < $count && $type['max_brand']>0) $this->apiReturn(4,'',1,str_replace('{max_brand}', $type['max_brand'], C('error_code.914')));
        
        //检查充许资质数量
        $count=M('shop_join_cert')->where(['uid' => $this->uid])->count();
        if($type['max_cert'] < $count && $type['max_cert']>0) $this->apiReturn(4,'',1,str_replace('{max_cert}', $type['max_cert'], C('error_code.915')));

        //检查充许类目数量
        $count=M('shop_join_category')->where(['uid' => $this->uid])->count();
        if($type['max_category'] < $count && $type['max_category']>0) $this->apiReturn(4,'',1,str_replace('{max_category}', $type['max_category'], C('error_code.916')));


        $_POST['uid']       =$this->uid;
        $_POST['bond_price']=$type['bond_price'];
        if(!$data=D('Common/ShopJoinInfo')->create()){
            $this->apiReturn(4,'',1,D('Common/ShopJoinInfo')->getError());
        }

        if(!$data['id']=D('Common/ShopJoinInfo')->add()){
            $this->apiReturn(0);
        }

        //更新步骤
        M('shop_join_contact')->where(['uid'=>$this->uid])->setField('step',3);

        $this->apiReturn(1,['data' => $data]);
    }

    /**
    * 修改店铺资料
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['shop_name'] 店铺名称
    * @param string $_POST['type_id']   店铺类型
    * @param string $_POST['about']     店铺描述
    * @param int    $_POST['inventory_type']    库存积分结算方式，0=非即时结算,1=即时结算（须先购买库存积分）    
    */
    public function shop_info_edit(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //$_POST['shop_name']=trimall(I('post.shop_name'));   //过滤所有空格

        //必传参数检查
        $this->need_param=array('openid','shop_name','type_id','about','inventory_type','sign');
        $this->_need_param();
        $this->_check_sign();

        //检查店铺名称是否可用
        $res=$this->_check_shop_name(I('post.shop_name'),$this->uid);
        if($res['code']!=1) $this->apiReturn($res['code'],'',1,$res['msg']);

        $rs=M('shop_join_info')->where(['uid' => $this->uid ])->field('id')->find();
        if(!$rs) $this->apiReturn(913); //店铺信息不存在！

        $type=M('shop_type')->cache(true,C('CACHE_LEVEL.XXL'))->where(['id' => I('post.type_id')])->field('bond_price,max_category,max_cert,max_brand')->find();
        //检查充许品牌数量
        $count=M('shop_join_brand')->where(['uid' => $this->uid])->count();
        if($type['max_brand'] < ($count+1) && $type['max_brand']>0) $this->apiReturn(4,'',1,str_replace('{max_brand}', $type['max_brand'], C('error_code.914')));
        
        //检查充许资质数量
        $count=M('shop_join_cert')->where(['uid' => $this->uid])->count();
        if($type['max_cert'] < ($count+1) && $type['max_cert']>0) $this->apiReturn(4,'',1,str_replace('{max_cert}', $type['max_cert'], C('error_code.915')));

        //检查充许类目数量
        $count=M('shop_join_category')->where(['uid' => $this->uid])->count();
        if($type['max_category'] < ($count+1) && $type['max_category']>0) $this->apiReturn(4,'',1,str_replace('{max_category}', $type['max_category'], C('error_code.916')));

        $_POST['uid']       =$this->uid;
        $_POST['id']        =$rs['id'];
        $_POST['bond_price']=$type['bond_price'];
        if(!$data=D('Common/ShopJoinInfo')->create()){
            $this->apiReturn(4,'',1,D('Common/ShopJoinInfo')->getError());
        }

        if(!$data['id']=D('Common/ShopJoinInfo')->save()){
            $this->apiReturn(0);
        }

        $this->apiReturn(1);
    }

    /**
    * 店铺类目
    * @param int $_POST['sid']  上级类目ID，选填
    */
    public function category_list(){
        //频繁请求限制,间隔2秒
        $this->_request_check();
        
        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        $_POST['sid']=$_POST['sid']?$_POST['sid']:0;
        $list=M('goods_category')->cache(true,C('CACHE_LEVEL.OneDay'))->where(['sid' => I('post.sid'), 'status' => 1])->field('id,category_name')->order('sort asc')->select();
        
        if(!$list) $this->apiReturn(3);
        $this->apiReturn(1,['data' => $list]);
    }


    /**
    * 取申请步骤
    * @param string $_POST['openid']    用户openid
    */
    public function step(){
        //频繁请求限制,间隔2秒
        $this->_request_check();
        
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $step_name=['未开始','已登录联系人资料','已填写品牌/类目/资质','已填写店铺资料','已签订合同','不符合要求被拒绝','已保证金','开店成功'];
        $step=M('shop_join_contact')->cache(true,C('CACHE_LEVEL.S'))->where(['uid' => $this->uid])->getField('step');
        $step=$step?$step:0;

        $data=[
            'step'  =>$step,
            'step_name' =>$step_name[$step]
        ];

        $this->apiReturn(1,['data' => $data]);
    }

}