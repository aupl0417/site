<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 卖家-运费模板管理
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
* 未用到该接口
*/

namespace Rest\Controller;
use Think\Controller\RestController;
class SellerExpressController extends CommonController {
	protected $action_logs = array('express_add','express_edit','express_delete','express_area_add','express_area_edit','express_area_delete');
    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 运费模板
    * @param string $_POST['openid']    用户openid
    */
    public function express_list(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=D('Common/ExpressRelation');

        $list=$do->relation(true)->where(['uid' => $this->uid])->field('etime,ip',true)->order('id desc')->select();

        if($list){
			$area 	=	$this->cache_table('area');
            //数据格式化输出
            foreach($list as $i=>$val){
                foreach($val['express_area'] as $k=>$v){
                    if($v['city_ids']){
                        $city=array();
                        $v['city_ids']=explode(',', $v['city_ids']);
                        foreach($v['city_ids'] as $c){
                            $city[]=$area[$c];
                        }
                        $list[$i]['express_area'][$k]['city_name']=implode(',',$city);
                    }
                }
                $list[$i]['express_company']['logo']=myurl($list[$i]['express_company']['logo'],150,50);
            }

            $this->apiReturn(1,['data' => $list]);
        }else $this->apiReturn(3);
      
    }

    /**
    * 快递公司列表
    */
    public function express_company(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        $list=M('express_category')->cache(true,C('CACHE_LEVEL.OneDay'))->where(['status' => 1])->field('id,category_name')->order('sort asc')->select();
        
        //数据格式化输出
        foreach($list as $i=>$val){
            $list[$i]['dlist']=M('express_company')->cache(true,C('CACHE_LEVEL.OneDay'))->where(['category_id'=>$val['id'],'status'=>1])->field('id,company,sub_name,code,logo')->order('sort asc')->select();
            foreach($list[$i]['dlist'] as $k=>$v){
                $list[$i]['dlist'][$k]['logo']=myurl($list[$i]['dlist'][$k]['logo'],150,50);
            }
        }

        if($list) $this->apiReturn(1,['data' => $list]);
        else $this->apiReturn(3);
    }

    /**
    * 运费模板详情
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['id']        运费模板ID
    */

    public function express_view(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=D('ExpressRelation');
        $rs=$do->relation(true)->where(['uid' => $this->uid ,'id' => I('post.id')])->field('atime,etime,ip',true)->find();

        if($rs){
			$area 	= 	$this->cache_table('area');
            foreach($rs['express_area'] as $k=>$v){
                if($v['city_ids']){
                    $city=array();
                    $v['city_ids']=explode(',', $v['city_ids']);
                    foreach($v['city_ids'] as $c){
                        $city[]=$area[$c];
                    }
                    $rs['express_area'][$k]['city_name']=implode(',',$city);
                }
            }
            $rs['express_company']['logo']=myurl($rs['express_company']['logo'],150,50);

            $this->apiReturn(1,['data' => $rs]);
        }else $this->apiReturn(3);
    }

    /**
    * 新增运费模板
    * @param string $_POST['openid']                用户openid
    * @param string $_POST['express_name']          模板名称
    * @param int    $_POST['express_company_id']    快递公司ID
    * @param string $_POST['unit']                  计量单位
    * @param float  $_POST['first_unit']            起步数量
    * @param float  $_POST['first_price']           起步价
    * @param float  $_POST['next_unit']             续重单位
    * @param float  $_POST['next_price']            续重金额
    * @param string $_POST['remark']                备注
    */
    public function express_add(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','express_name','express_company_id','unit','first_unit','first_price','next_unit','next_price','sign');
        $this->_need_param();
        $this->_check_sign();

        $_POST['uid']=$this->uid;
        if(!$data=D('Common/Express')->create()) $this->apiReturn(4,'',1,D('Common/Express')->getError());

        if(!$data['id']=D('Common/Express')->add()) $this->apiReturn(0);
        $this->apiReturn(1,['data' => $data]);
    }

    /**
    * 修改运费模板
    * @param int  $_POST['id']  运费模板ID
    * 其它参数同上
    */
    public function express_edit(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','express_name','express_company_id','unit','first_unit','first_price','next_unit','next_price','sign');
        $this->_need_param();
        $this->_check_sign();

        $_POST['uid']=$this->uid;
        if(!D('Common/Express')->create()) $this->apiReturn(4,'',1,D('Common/Express')->getError());

        if(D('Common/Express')->save() === false) $this->apiReturn(0);
        $this->apiReturn(1);
    }

    /**
    * 删除运费模板
    * @param int  $_POST['id']  运费模板ID
    * @param string $_POST['openid']    用户openid
    */
    public function express_delete(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        if(M('express')->where(['uid' => $this->uid,'id' => I('post.id')])->delete()) $this->apiReturn(1);
        else $this->apiReturn(0);
    }


    /**
    * 添加指定地区运费
    * @param string $_POST['openid']                用户openid
    * @param string $_POST['express_id']            运费模板ID
    * @param string $_POST['city_ids']              城市ID,多个用逗号隔开
    * @param float  $_POST['first_unit']            起步数量
    * @param float  $_POST['first_price']           起步价
    * @param float  $_POST['next_unit']             续重单位
    * @param float  $_POST['next_price']            续重金额
    */
    public function express_area_add(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','express_id','city_ids','first_unit','first_price','next_unit','next_price','sign');
        $this->_need_param();
        $this->_check_sign();

        if(M('express')->where(['uid' => $this->uid,'id'=>I('post.express_id')])->count()<1) $this->apiReturn(950); //快递模板不存在

        //检查是否有城市重复添加
        $city_ids=explode(',',I('post.city_ids'));
        $list=M('express_area')->where(['express_id'=>I('post.express_id')])->getField('city_ids',true);
        if($list){
            $list=implode(',',$list);
            $list=explode(',',$list);
            
            foreach($city_ids as $val){
                if(in_array($val,$list)) $this->apiReturn(951);     //本次添加的城市中已有部分已设置了运费，请不要复添加
            }
        }

        if(!$data=D('Common/ExpressArea')->create()) $this->apiReturn(4,'',1,D('Common/ExpressArea')->getError());

        if(!$data['id']=D('Common/ExpressArea')->add()) $this->apiReturn(0);

        //数据格式化输出
		$area 	=	$this->cache_table('area');
        foreach($city_ids as $val){
            $data['city_name'][]=$area[$val];
        }
        $data['city_name']=implode(',',$data['city_name']);        
        $this->apiReturn(1,['data' => $data]);        
    }

    /**
    * 添加指定地区运费
    * @param int $_POST['id']   地区模板ID
    * 其它参数同上
    */
    public function express_area_edit(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','express_id','city_ids','first_unit','first_price','next_unit','next_price','sign');
        $this->_need_param();
        $this->_check_sign();

        if(M('express')->where(['uid' => $this->uid,'id'=>I('post.express_id')])->count()<1) $this->apiReturn(950); //快递模板不存在

        //检查是否有城市重复添加
        $city_ids=explode(',',I('post.city_ids'));
        $list=M('express_area')->where(['express_id'=>I('post.express_id'),'id'=>['neq',I('post.id')]])->getField('city_ids',true);
        if($list){
            $list=implode(',',$list);
            $list=explode(',',$list);

            
            foreach($city_ids as $val){
                if(in_array($val,$list)) $this->apiReturn(951);     //本次添加的城市中已有部分已设置了运费，请不要复添加
            }
        }

        if(!$data=D('Common/ExpressArea')->create()) $this->apiReturn(4,'',1,D('Common/ExpressArea')->getError());

        if(false===D('Common/ExpressArea')->save()) $this->apiReturn(0);

        //数据格式化输出
		$area 	=	$this->cache_table('area');
        foreach($city_ids as $val){
            $data['city_name'][]=$area[$val];
        }
        $data['city_name']=implode(',',$data['city_name']);

        $this->apiReturn(1,['data' => $data]);        
    }


    /**
    * 列出自定义地区运费
    * @param string $_POST['openid']   用户openid
    * @param int    $_POST['express_id']    运费模板ID
    */
    public function express_area(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','express_id','sign');
        $this->_need_param();
        $this->_check_sign();

        if(!$rs=M('express')->where(['uid' => $this->uid,'id'=>I('post.express_id')])->field('unit')->find()) $this->apiReturn(950); //快递模板不存在

        $list=M('express_area')->where(['express_id' => I('post.express_id')])->field('atime,etime,ip',true)->order('id desc')->select();
        
        //数据格式化输出
		$area 	=	$this->cache_table('area');
        foreach($list as $i=>$val){
            $city_ids=explode(',',$val['city_ids']);
            foreach($city_ids as $v){
                $list[$i]['city_name'][]=$area[$v];
            }
            $list[$i]['unit']=$rs['unit'];
            $list[$i]['city_name']=implode(',',$list[$i]['city_name']);            
        }

        if($list) $this->apiReturn(1,['data' => $list]);
        else $this->apiReturn(3);
    }

    /**
    * 删除自定义地区运费
    * @param string $_POST['openid']   用户openid
    * @param int    $_POST['express_id']    运费模板ID
    * @param int    $_POST['id']    自定义地区运费ID
    */
    public function express_area_delete(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','express_id','sign');
        $this->_need_param();
        $this->_check_sign();

        if(!$rs=M('express')->where(['uid' => $this->uid,'id'=>I('post.express_id')])->field('id')->find()) $this->apiReturn(950); //快递模板不存在    

        if(M('express_area')->where(['id'=>I('post.id'),'express_id'=>I('post.express_id')])->delete()){
            $this->apiReturn(1);
        }else $this->apiReturn(0);
    }

}