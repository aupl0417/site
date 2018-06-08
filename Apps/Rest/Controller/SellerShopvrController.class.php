<?php
/**
+----------------------------------------------------------------------
| RestFull API
+----------------------------------------------------------------------
| 卖家 - 违规管理
+----------------------------------------------------------------------
| Author: Alinki
| Date:	  2017-04-25
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class SellerShopvrController extends CommonController {
	private $type_name = ['','一般','严重','非常严重'];
	private $rules_type_name = ['','商品违规','交易违规','其他违规'];
	private $status_name = ['待申诉','待审核','已判定','处罚取消','申诉补充'];
	
	public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
     * 获取违规列表
     * @param	integer	$status			审核状态
     * @param	integer	$rules_type		违规类型
     * @param	integer	$wrongdoing		违规行为
     * @param	string	$atime			开始时间
     * @param	string	$etime			结束时间
     */
    public function vrlist(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
		
        $status=I('post.status');
       	if ($status>=0) $map['status']=$status;
        $rules_type=I('post.rules_type');
        if ($rules_type) $map['rules_type']=$rules_type;
        $wrongdoing=I('post.wrongdoing');
        if ($wrongdoing) $map['wrongdoing']=$wrongdoing;
        $stime=I('post.stime');
        if ($stime) $map['atime']=['egt',$stime];
        $etime=I('post.etime');
        if ($etime) $map['atime']=['elt',$etime];
        $map['uid']=$this->uid;
        
        $pagesize=I('post.pagesize')?I('post.pagesize'):5;
        $pagelist=pagelist(array(
                'table'         =>'Common/ShopvrRelation',
        		'do'			=>'D',
                'map'           =>$map,
                'order'         =>'atime desc',
                'fields'        =>'etime,ip,uid',
                'fields_type'   =>true,
        		'relation'		=>true,
                'pagesize'      =>$pagesize,
                'action'        =>I('post.action'),
                'query'         =>I('post.query'),
                'p'             =>I('post.p'),
            ));
        if($pagelist['list']){
            foreach($pagelist['list'] as $i => $val){
                $pagelist['list'][$i]['status_name']=$this->status_name[$val['status']];
                $pagelist['list'][$i]['rules_type_name']=$this->rules_type_name[$val['rules_type']];
                $pagelist['list'][$i]['type_name']=$this->type_name[$val['type']];
            }
            $this->apiReturn(1,array('data'=>$pagelist));
        }else{
            $this->apiReturn(3);
        }
    }
    
    /**
     * 获取一个违规记录
     */
    public function get_shopvr(){
    	//频繁请求限制,间隔2秒
    	$this->_request_check();
    	
    	//必传参数检查
    	$this->need_param=array('openid','id','sign');
    	$this->_need_param();
    	$this->_check_sign();
    	
    	$id=I('post.id',0);
    	$res=D('Common/ShopvrRelation')->relation(true)->where(['id'=>$id,'uid'=>$this->uid])->find();
    	if ($res){
    		$res['status_name']=$this->status_name[$res['status']];
    		$res['rules_type_name']=$this->rules_type_name[$res['rules_type']];
    		$res['type_name']=$this->type_name[$res['type']];
    		$this->apiReturn(1,['data'=>$res]);
    	}else {
    		$this->apiReturn(3);
    	}
    }
    
    /**
     * 保存申诉内容
     */
    public function save_appeal(){
    	//频繁请求限制,间隔2秒
    	$this->_request_check();
    	
    	//必传参数检查
    	$this->need_param=array('openid','id','appeal','sign');
    	$this->_need_param();
    	
    	$id=I('post.id',0);
    	$appeal=htmlspecialchars_decode(I('post.appeal'));
    	$res=M('shop_vr')->where(['uid'=>$this->uid,'id'=>$id])->find();
    	if ($res && in_array($res['status'], [0,4])){
    		$res=M('shop_vr')->where(['uid'=>$this->uid,'id'=>$id,'status'=>['in','0,4']])->save(['status'=>1,'appeal'=>$appeal,'appealtime'=>date('Y-m-d H:i:s')]);
    		if ($res){
    			$this->apiReturn(1);
    		}else {
    			$this->apiReturn(0);
    		}
    	}else {
    		$this->apiReturn(0,['msg'=>'该记录已提交申诉']);
    	}
    	
    }
    
    /**
     * 统计申诉状态
     */
    public function total_sv(){
    	//频繁请求限制,间隔2秒
    	$this->_request_check();
    	
    	//必传参数检查
    	$this->need_param=array('openid','sign');
    	$this->_need_param();
    	
    	$res=M('shop_vr')->field('count(id) as num,status')->where(['uid'=>$this->uid])->group('status')->select();
    	$data=[];
    	if ($res){
    		foreach ($res as $v){
    			$data[$v['status']]=$v['num'];
    		}
    	}
    	$this->apiReturn(1,['data'=>$data]);
    }
    
    /**
     * 统计扣分
     */
    public function total_vr(){
    	//频繁请求限制,间隔2秒
    	$this->_request_check();
    	
    	//必传参数检查
    	$this->need_param=array('openid','sign');
    	$this->_need_param();
    	
    	$res=M('shop_vr')->field('sum(point) as point')->where(['uid'=>$this->uid,'status'=>2])->find();
    	$this->apiReturn(1,['data'=>$res]);
    }

}