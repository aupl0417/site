<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 卖家违规接口
 * ----------------------------------------------------------
 * Author:liangfeng
 * ----------------------------------------------------------
 * 2017-06-07
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class ShopVrController extends ApiController {
    protected $action_logs = array();
	
	private $punish_names = ['','屏蔽商品展示三天','','暂停店铺营业三个月','关停店铺一年'];
	
	
	
	

    /**
     * subject: 违规生效
     * api: /ShopVr/auto_illegl
     * author: liangfeng
     * day: 2017-06-07
     *
     * [字段名,类型,是否必传,说明]
     * param: shop_vr_id,int,1,违规记录id
     */
	 
    public function illegl(){
        $this->check('shop_vr_id',false);

        $res = $this->_illegl($this->post);
        $this->apiReturn($res);
    }

    public function _illegl($param){
		//writelog(['timd'=>date('Y-m-d H:i:s',time()),'function'=>__FUNCTION__,'param'=>$param]);
		//查找违规记录
		$res = M('shop_vr')->field('uid,status,point')->find($param['shop_vr_id']);
		if(!$res){
			return ['code' => 0,'msg'=>'没有找到此违规记录！'];
		}
		if($res['status'] == 2 || $res['status'] == 3){
			return ['code' => 0,'msg'=>'该违规记录已经生效或取消！'];
		}
		
		$do=M();
		$do->startTrans();
		
		//违规生效
		if(false===M('shop_vr')->where(['id'=>$param['shop_vr_id']])->save(['status'=>2])) goto error;
		
		//统计店铺一年所有的扣分
		$year = date('Y',time());
		$points = M('shop_vr')->field('sum(point) as total_point,sum(plus_point) as total_plus_point')->where(['_string'=>'date_format(atime,"%Y")="'.$year.'"','status'=>2,'uid'=>$res['uid']])->find();

		$total_point = $points['total_point'] ? $points['total_point'] : 0 ;
		$total_plus_point = $points['total_plus_point'] ? $points['total_plus_point'] : 0 ;		
		$dec_point = $total_point-$total_plus_point;
	
		//更新店铺扣分
		if(false===M('shop')->where(['uid'=>$res['uid']])->save(['illegl_point'=>$dec_point])) goto error;
		$do->commit();
		
		if($res['point'] > 0){
			//发送扣分消息
			$msg_data = ['tpl_tag'=>'dec_point','uid'=>$res['uid'],'score'=>$res['point']];
			tag('send_msg',$msg_data);
		}
		
		//查询分数是否已经到达处罚值
		if($dec_point >= 12){
			$this->_punish(['uid'=>$res['uid']]);
		}
		
		return ['code'=>1];
		
		error:
			$do->rollback();
			return ['code' => 0,'msg'=>'操作失败！'];
    }
	
	 /**
     * subject: 处罚生效
     * api: /ShopVr/punish
     * author: liangfeng
     * day: 2017-06-07
     *
     * [字段名,类型,是否必传,说明]
     * param: uid,int,1,商家用户id
     */
	 
    public function punish(){
        $this->check('uid',false);
		
        $res = $this->_punish($this->post);
        $this->apiReturn($res);
    }

    public function _punish($param){
		//writelog(['timd'=>date('Y-m-d H:i:s',time()),'function'=>__FUNCTION__,'param'=>$param]);
		$shop_info = M('shop')->field('id,uid,illegl_point')->where(['uid'=>$param['uid']])->find();
		if(!$shop_info){ return ['code' => 0,'msg'=>'查无此店铺！'];}
		
		//获取店铺的总扣分
		$illegl_point = $shop_info['illegl_point'];
		if($illegl_point < 12){
			return ['code' => 0,'msg'=>'该商家未达到处罚标准！'];
		}
		
		
		if($illegl_point >= 12 && $illegl_point < 24){
			$punish_type = 1;
		}else if($illegl_point >= 24 && $illegl_point < 36){
			$punish_type = 2;
		}else if($illegl_point >= 36 && $illegl_point < 48){
			$punish_type = 3;
		}else if($illegl_point >= 48){
			$punish_type = 4;
		}
		
		//查询是否已经接受此档处罚
		$year = date('Y',time());
		$res = M('shop_punish')->field('punish_type')->where(['uid'=>$param['uid'],'punish_year'=>$year,'punish_type'=>$punish_type])->find();
		if($res){return ['code' => 0,'msg'=>'处罚已经存在'];}
		
		$do=M();
		$do->startTrans();
		
		//屏蔽商品展示三天
		if($punish_type == 1){
			$loop = true;
			while($loop){
				$num = M('goods')->where(['seller_id'=>$shop_info['uid'],'shop_id'=>$shop_info['id'],'is_display'=>1])->data(['is_display'=>2])->limit(1000)->save();
				usleep(1000);
				if($num == 0){
					$loop == false;
					break;
				}
			}	
			$data['end_time'] = date('Y-m-d H:i:s',time()+86400*3);
		}
		//暂无
		if($punish_type == 2){
			$data['end_time'] = date('Y-m-d H:i:s',time()+86400*30);
		}
		//暂停营业三个月
		if($punish_type == 3){
			M('shop')->where(['id'=>$shop_info['id']])->data(['status'=>4])->save();
			$data['end_time'] = date('Y-m-d H:i:s',time()+86400*90);
		}
		//关停店铺(暂停营业一年)
		if($punish_type == 4){
			M('shop')->where(['id'=>$shop_info['id']])->data(['status'=>4])->save();
			$data['end_time'] = date('Y-m-d H:i:s',time()+86400*365);
		}
		
		//记录处罚信息
		$data['uid'] 			= $shop_info['uid'];
		$data['shop_id'] 		= $shop_info['id'];
		$data['punish_type'] 	= $punish_type;
		$data['punish_name'] 	= $this->punish_names[$punish_type];
		$data['punish_year'] 	= $year;
		M('shop_punish')->add($data);
		
		$do->commit();
		
		error:
			$do->rollback();
			return ['code' => 0,'msg'=>'操作失败！'];
	}
	
	/**
     * subject: 处罚解除
     * api: /ShopVr/punish_relieve
     * author: liangfeng
     * day: 2017-06-07
     *
     * [字段名,类型,是否必传,说明]
     * param: shop_punish_id,int,1,处罚id
     */
	 
    public function punish_relieve(){
        $this->check('shop_punish_id',false);
		
        $res = $this->_punish_relieve($this->post);
        $this->apiReturn($res);
    }

    public function _punish_relieve($param){
		//writelog(['timd'=>date('Y-m-d H:i:s',time()),'function'=>__FUNCTION__,'param'=>$param]);
		//writelog($param);
		$end_time = date('Y-m-d H:i:s',time());
		//查询处罚记录
		$map['shop_punish_id'] = $param['shop_punish_id'];
		$map['end_time'] = ['elt',$end_time];
		$map['status'] = 1;
		
		$res = M('shop_punish')->field('uid,shop_id,punish_type')->where($map)->find();
		//$sql = M()->getlastsql();
		if(!$res){return ['code' => 0,'data'=>$sql,'msg'=>'查无此处罚记录！'];}

		if($res['punish_type'] == 1){
			$loop = true;
			while($loop){
				$num = M('goods')->where(['seller_id'=>$res['uid'],'shop_id'=>$res['shop_id'],'is_display'=>2])->data(['is_display'=>1])->limit(1000)->save();
				usleep(1000);
				if($num == 0){
					$loop == false;
					break;
				}
			}
		}
		
		if($res['punish_type'] == 2){
			
		}
		if($res['punish_type'] == 3){
			M('shop')->where(['id'=>$res['id']])->data(['status'=>1])->save();
		}
		if($res['punish_type'] == 4){
			M('shop')->where(['id'=>$res['id']])->data(['status'=>1])->save();
		}
		
	
		
		if(false===M('shop_punish')->where(['id'=>$param['shop_punish_id']])->data(['status'=>2])->save()){
			return ['code' => 0];
		}
		return ['code' => 1];
		
	}   
	
	/**
     * subject: 获取店铺扣分值
     * api: /ShopVr/get_shop_point
     * author: liangfeng
     * day: 2017-06-09
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,商家用户
     */
	 
    public function get_shop_point(){
        $this->check('uid',false);
		$param = $this->post;
		//$param['uid'] = $this->user['id'];
        $res = $this->_get_shop_point($param);
        $this->apiReturn($res);
    }

    public function _get_shop_point($param){
		//writelog(['timd'=>date('Y-m-d H:i:s',time()),'function'=>__FUNCTION__,'param'=>$param]);
		//统计店铺一年所有的扣分
		$year = date('Y',time());
		$points = M('shop_vr')->field('sum(point) as total_point,sum(plus_point) as total_plus_point')->where(['_string'=>'date_format(atime,"%Y")="'.$year.'"','status'=>2,'uid'=>$param['uid']])->find();

		$total_point = $points['total_point'] ? $points['total_point'] : 0 ;
		$total_plus_point = $points['total_plus_point'] ? $points['total_plus_point'] : 0 ;		
		$dec_point = $total_point-$total_plus_point;
		
		$illegl_point = M('shop')->where(['uid'=>$param['uid']])->getField('illegl_point');
		
		
		if((int)$illegl_point != $dec_point){
			M('shop')->where(['uid'=>$param['uid']])->data(['illegl_point'=>$dec_point])->save();
			
		}
		
		return ['code' => 1,'data'=>$dec_point];
	}
	
	
	
}