<?php
namespace Wap\Controller;
use Think\Controller;
use Common\Builder\Activity;
class GoodsController extends CommonController {
    public function index(){		
		$this->display();
    }
	
	/**
	* 商品搜索历史记录
	*/
	public function goods_q(){
		$goods_q=cookie('goods_q');

		if($goods_q){
			foreach($goods_q as $i => $val){
				$goods_q[$i]	=	urldecode($val);
			}
			$this->ajaxReturn(['code'=>1,'data'=>$goods_q]);
		}else{
			$this->ajaxReturn(['code'=>0]);
		}
	}

	/**
	* 店铺搜索历史记录
	*/
	public function shop_q(){
		$shop_q=cookie('shop_q');

		if($shop_q){
			foreach($shop_q as $i => $val){
				$shop_q[$i]	=	urldecode($val);
			}			
			$this->ajaxReturn(['code'=>1,'data'=>$shop_q]);
		}else{
			$this->ajaxReturn(['code'=>0]);
		}
	}
	
	public function goods_activity(){
	    if (IS_POST) {
	        $activitys  =   Activity::getSpikeAndRestriction(I('post.shop_id'), I('post.goods_id'));
	        if (!$activitys) {
	            //如果没有则查看是否参与常规活动
	            $activitys   =   getActivityGoods(I('post.shop_id'));
	        } else {
	            $activitys['spm']  =   enCryptRestUri($activitys['id'] . '_' . I('post.shop_id'));
	        }
	        if(!empty($activitys)){
	        	$activitys 	= imgsize_list($activitys,'images',50);
	        	
	            $this->ajaxReturn(['code'=>1,'data'=>$activitys]);
	        }else{
	            $this->ajaxReturn(['code'=>0,'data'=>$activitys]);
	        }
	    }
		/*$activitys   =   getActivityGoods(I('post.activity_id'), I('post.shop_id'));
		if(!empty($activitys)){
			$this->ajaxReturn(['code'=>1,'data'=>$activitys]);
		}else{
			$this->ajaxReturn(['code'=>0,'data'=>$activitys]);
		}*/
	}

    public function city(){
        $list=get_category(['table'=>'area','field'=>'id,sid,a_name,sub_name','level'=>2,'cache_name'=>'citylist_level2']);
        if($list) $this->ajaxReturn(['code' =>1,'data' => $list]);
        else $this->ajaxReturn(['code' =>0,'msg'=>'获取不到数据！']);
    }

    /**
    * 根据模板获取运费
    */
    public function get_express_price(){
        $rs=D('ExpressTplRelation')->relation(true)->where(['id' => I('get.tid')])->find();

        if($rs['is_free']) $this->ajaxReturn(['code' => 1,'data' => '快递：包邮<span class="pl10"></span>EMS：包邮']);

        if($rs['is_express']==1){
            $express    ='快递：'.$rs['express_default_first_price'].'元';
        }

        if($rs['is_ems']==1){
            $ems        ='EMS：'.$rs['ems_default_first_price'].'元';
        }
        foreach($rs['express_area'] as $val){
            $val['city_ids']    =explode(',', $val['city_ids']);

            if($val['type']==1){
                if(in_array(I('get.city'),$val['city_ids'])) $express ='快递：'.$val['first_price'].'元 ';
            }
            elseif($val['type']==2){
                if(in_array(I('get.city'),$val['city_ids'])) $ems ='EMS：'.$val['first_price'].'元';
            }
                
        }

        $this->ajaxReturn(['code' => 1,'data' => $express.'<span class="pl10"></span>'.$ems]);

    }	
}