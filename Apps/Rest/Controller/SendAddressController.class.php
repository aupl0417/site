<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 卖家常用发货地址管理
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class SendAddressController extends CommonController {
	protected $action_logs = array('add','edit','delete');
    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 添加地址
    * @param string $_POST['openid']        用户openid
    * @param string     $_POST['linkname']  姓名
    * @param string     $_POST['mobile']    手机
    * @param int        $_POST['province']  省份
    * @param int        $_POST['city']      城市
    * @param int        $_POST['districe']  区县
    * @param int        $_POST['town']      街道,选填
    * @param string     $_POST['street']    详细地址
    */
    public function add(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','linkname','mobile','province','city','district','street','sign');
        $this->_need_param();
        $this->_check_sign();

        $_POST['postcode']?$_POST['postcode']:'510000';
        $_POST['uid']=$this->uid;

        $do=D('Common/SendAddress');

        if(!$data=$do->create()) $this->apiReturn(4,'',1,$do->getError());

        if($do->where($data)->find()) $this->apiReturn(151);    //地址已存在

        if($do->add()){
            $insid=$do->getLastInsID();
            //默认地址
            if(I('post.is_default')==1){
                $do->where(array('uid'=>$this->uid))->setField('is_default',0);
                $do->where(array('id'=>$insid))->setField('is_default',1);
            } else {
                $this->_default();
            }
            //添加成功！
            $this->apiReturn(1);
        }else{
            //添加失败
            $this->apiReturn(0);
        }

    }

    /**
    * 修改地址
    * @param string     $_POST['openid']    用户openid
    * @param int        $_POST['id']        地址ID
    * @param string     $_POST['linkname']  姓名
    * @param string     $_POST['mobile']    手机
    * @param int        $_POST['province']  省份
    * @param int        $_POST['city']      城市
    * @param int        $_POST['districe']  区县
    * @param int        $_POST['town']      街道,选填
    * @param string     $_POST['street']    详细地址
    */
    public function edit(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('id','openid','linkname','mobile','province','city','district','street','sign');
        $this->_need_param();
        $this->_check_sign();

        $_POST['postcode']?$_POST['postcode']:'510000';
        $_POST['uid']=$this->uid;

        $do=D('Common/SendAddress');

        if(!$data=$do->create()) $this->apiReturn(4,'',1,$do->getError());
        $map=$data;
        $map['id']=array('neq',I('post.id'));
        if($do->where($map)->find()) $this->apiReturn(151);    //地址已存在

        if($do->save()){
            $insid=I('post.id');
            //默认地址
            if(I('post.is_default')==1){
                $do->where(array('uid'=>$this->uid))->setField('is_default',0);
                $do->where(array('id'=>$insid))->setField('is_default',1);
            }else $this->_default();
            //修改成功！
            $this->apiReturn(1);
        }else{
            //修改失败
            $this->apiReturn(0);
        }        
    }

    /**
    * 删除地址
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['id']        要删除的地址ID
    */
    public function delete(){
        //频繁请求限制,间隔2秒
        $this->_request_check();
        //必传参数检查
        $this->need_param=array('id','openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('send_address');

        if($do->where(array('id'=>I('post.id'),'uid'=>$this->uid))->delete()){
            $this->_default();
            $this->apiReturn(1);
        }else{
            $this->apiReturn(0);
        }
    }

    /**
    * 默认发货地址
    */
    public function _default(){
        $do=M('send_address');
        if($rs=$do->where(array('uid'=>$this->uid))->order('is_default desc,id asc')->field('id,is_default')->find()){
            if($rs['is_default']==0) $do->where(array('id'=>$rs['id']))->setField('is_default',1);
        }

    }

    /**
    * 发货地址列表
    * @param string $_POST['openid']    用户openid
    */
    public function address_list(){
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('send_address');
        //->cache(true,C('CACHE_LEVEL.XXS'))
        $list=$do->where(array('uid'=>$this->uid))->field('etime,ip',true)->order('is_default desc,id desc')->limit(15)->select();
        if($list){
            $area   =$this->cache_table('area');
            foreach($list as $key=>$val){
                
                $list[$key]['province_name']    =$area[$val['province']];
                $list[$key]['city_name']        =$area[$val['city']];
                $list[$key]['district_name']    =$area[$val['district']];
                $list[$key]['town_name']        =$area[$val['town']];
                
            }
            $this->apiReturn(1,array('data'=>$list));
        }else{
            $this->apiReturn(3);
        }
    }

    /**
    * 地址详情
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['id']        地址ID
    */
    public function view(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();      

        //必传参数检查
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('send_address');
        $rs=$do->where(array('uid'=>$this->uid,'id'=>I('post.id')))->field('atime,etime,ip',true)->find();
        if($rs){
            //返回详情
            $area   =$this->cache_table('area');
            $rs['province_name']    =$area[$rs['province']];
            $rs['city_name']        =$area[$rs['city']];
            $rs['district_name']    =$area[$rs['district']];
            $rs['town_name']        =$area[$rs['town']];                
            $this->apiReturn(1,array('data'=>$rs));
        }else{
            //找不到记录
            $this->apiReturn(0);
        }
    }
	
    /**
    * 获取默认地址
    * @param string $_POST['openid']    用户openid
    */
	public function default_address(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();      

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

		$do=M('send_address');
		$rs=$do->cache(true,C('CACHE_LEVEL.XXS'))->where(array('uid'=>$this->uid))->field('atime,etime,ip',true)->order('is_default desc')->find();
        if($rs){
            //返回详情
            $area   =$this->cache_table('area');
            $rs['province_name']    =$area[$rs['province']];
            $rs['city_name']        =$area[$rs['city']];
            $rs['district_name']    =$area[$rs['district']];
            $rs['town_name']        =$area[$rs['town']];                
            $this->apiReturn(1,array('data'=>$rs));
        }else{
            //找不到记录
            $this->apiReturn(0);
        }
	}	

}