<?php
/*
+--------------------------------
+ 用户认证处理 by enhong
+-------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class AuthController extends CommonController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 处理个人认证
    */
    public function person(){
        //频繁请求限制
        $this->_request_check();

        //var_dump(I('post.'));
        //必传参数检查
        $this->need_param=array('openid','name','card_type','card_no','card_pic','card_pic2','card_pic3','sign');
        $this->_need_param();
        $this->_check_sign();

        //检查是否提交过企业认证
        if(M('auth_company')->where(array('uid'=>$this->uid))->field('id')->find()){
            $this->apiReturn(69);
        }

        //验证身份证号码
        if(I('post.card_type')==0){
            if(checkform(I('post.card_no'),array('is_card'))==false) $this->apiReturn(4,'',1,'身份证格式错误！');
        }
        $do=M('auth_person');
        
        //判断是否已使用 
        if ($do->where(array('card_no' => I('post.card_no')))->find()) {
            $this->apiReturn(611);
        }
        
        $_POST['uid']=$this->uid;
        $_POST['status']=0;
        //检查是否已通过审核
        
        if($rs=$do->where(array('uid'=>$this->uid))->field('id,status')->find()){
            if($rs['status']==1) $this->apiReturn(61);  //已通过认证，不可再次提交！
            elseif($rs['status']==-1) $this->apiReturn(62); //被列入黑名单，无法再次提交认证资料！
            else{   //修改资料
                $do=D('Common/AuthPerson');
                $_POST['id']=$rs['id'];
                $_POST['atime']=date('Y-m-d H:i:s');
                if($do->create()){
                    if($do->save()){
                        //提交成功
                        $this->apiReturn(1);
                    }else{
                        //提交失败
                        $this->apiReturn(0);
                    }
                }else{
                    //数据验证失败！
                    $this->apiReturn(4,'',1,$do->getError());
                }                
            }
        }else{
            //添加记录
            $do=D('Common/AuthPerson');
            if($do->create()){
                if($do->add()){
                    //提交成功
                    $this->apiReturn(1);
                }else{
                    //提交失败
                    $this->apiReturn(0);
                }
            }else{
                //数据验证失败！
                $this->apiReturn(4,'',1,$do->getError());
            }

        }
    }


    /**
    * 企业认证
    */
    public function company(){
        //频繁请求限制
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','company','reg_address','legal','legal_card_type','legal_card_no','legal_pic','legal_pic2','legal_pic3','com_type','license_code','license_pic','sign');

        $this->_need_param();
        $this->_check_sign();

        //检查是否提交过个人认证
        if(M('auth_person')->where(array('uid'=>$this->uid))->field('id')->find()){
            $this->apiReturn(70);
        }        

        //当为企业是，必填数据验证
        if(I('post.com_type')!=1){
            //if(checkform(I('post.reg_capital'),'required')) $this->apiReturn(63);   //注册资本
            //if(checkform(I('post.paidin_capital'),'required')) $this->apiReturn(64);    //实收资本            

            //非三证合一企业
            if(I('post.is_three')!=1){
                if(checkform(I('post.org_pic'),'required')) $this->apiReturn(67);   //机构代码证图片
                if(checkform(I('post.tax_pic'),'required')) $this->apiReturn(68);   //税务登记证图片
				if(checkform(I('post.org_code'),'required')) $this->apiReturn(65);  //机构代码
				if(checkform(I('post.tax_code'),'required')) $this->apiReturn(66);  //税务登记证号				
            }
        }

        //验证身份证号码
        if(I('post.legal_card_type')==0){
            if(checkform(I('post.legal_card_no'),array('is_card'))==false) $this->apiReturn(4,'',1,'身份证格式错误！');
        }

        $_POST['uid']=$this->uid;
        $_POST['status']=0;
        //检查是否已通过审核
        $do=M('auth_company');
        if($rs=$do->where(array('uid'=>$this->uid))->field('id,status')->find()){
            if($rs['status']==1) $this->apiReturn(61);  //已通过认证，不可再次提交！
            elseif($rs['status']==-1) $this->apiReturn(62); //被列入黑名单，无法再次提交认证资料！
            else{   //修改资料
                $do=D('Common/AuthCompany');
                $_POST['id']=$rs['id'];
                $_POST['atime']=date('Y-m-d H:i:s');
                if($do->create()){
                    if($do->save()){
                        //提交成功
                        $this->apiReturn(1);
                    }else{
                        //提交失败
                        $this->apiReturn(0);
                    }
                }else{
                    //数据验证失败！
                    $this->apiReturn(4,'',1,$do->getError());
                }                
            }
        }else{
            //添加记录
            $do=D('Common/AuthCompany');
            if($do->create()){
                if($do->add()){
                    //提交成功
                    $this->apiReturn(1);
                }else{
                    //提交失败
                    $this->apiReturn(0);
                }
            }else{
                //数据验证失败！
                $this->apiReturn(4,'',1,$do->getError());
            }

        }
    }


    //获取认证状态
    public function auth_status(){
        //频繁请求限制
        //$this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');

        $this->_need_param();
        $this->_check_sign();

        $status=array(
                0   =>'等待审核',
                1   =>'审核通过',
                2   =>'审核失败',
                '-1'  =>'黑名单'
            );   
            
        //判断是否个人认证
        $do=D('AuthPersonRelation');
        $rs=$do->relation('logs')->where(array('uid'=>$this->uid))->field('etime,ip',true)->find();
        if($rs){
            $rs['status_name']=$status[$rs['status']];
            $rs['auth_type']=1;
            $rs['auth_type_name']='个人认证';
            $this->apiReturn(1,array('data'=>$rs));
        }

        //判断是否为企业认证
        $do=D('AuthCompanyRelation');
        $rs=$do->relation('logs')->where(array('uid'=>$this->uid))->field('etime,ip',true)->find();
        if($rs){
            $rs['status_name']=$status[$rs['status']];
            $rs['auth_type']=2;
            $rs['auth_type_name']='企业认证';
            $this->apiReturn(1,array('data'=>$rs));
        }        

        $this->apiReturn(0,'',1,'还未提交认证！');

    }
    
    /**
     * 手机号码认证
     */
    public function auth_mobile() {
        $this->need_param=array('openid','sign','smscode');
        $this->_need_param();
        $this->_check_sign();
        $do =   M('user');
        $userInfo   =   $do->where(array('id' => $this->uid))->field('is_mobile,mobile')->find();
        if ($userInfo['is_mobile'] == 1) {
            $this->apiReturn(1);//手机号码已认证
        }
        
        $cache_name='sms_vcode_'.$userInfo['mobile'].'_12';
        $code=S($cache_name);
        $c  =   join(',', $code);
        if ($code['code'] != I('post.smscode')) {
            $this->apiReturn(0,'',1,'短信验证码错误！');
        }
        
        if ($do->where(array('id' => $this->uid))->save(array('is_mobile' => 1))) {
            S($cache_name,null);
            $this->apiReturn(1);
        }
        $this->apiReturn(0);
    }
    
    /**
     * 邮箱认证
     */
    public function auth_email() {
        $this->need_param=array('openid','sign','code');
        $this->_need_param();
        $this->_check_sign();
    }
}