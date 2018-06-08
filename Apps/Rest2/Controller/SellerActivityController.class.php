<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 商家活动管理
 * ----------------------------------------------------------
 * Author:liangfeng 
 * ----------------------------------------------------------
 * 2017-03-28
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller;
class SellerActivityController extends ApiController {
	protected $action_logs = array('');

	/**
     * subject: 卖家活动列表
     * api: /SellerActivity/activity_list
     * author: liangfeng
     * day: 2017-03-28
	 * content: 活动状态：0=>'未开始' 1=>'进行中' 2=>'已完成' 3=>'已取消'
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
	 * param: sid,int,0,活动状态 
     * param: pagesize,int,0,每页显示数量
     * param: p,int,0,第p页
     */
	public function activity_list(){
		$this->check($this->_field('p,pagesize,sid','openid'),false);
        $res = $this->_activity_list($this->post);
        $this->apiReturn($res);
    }
	public function _activity_list($param){
		$sArr   =   [0,1,2,3];
        $map    =   [
            'shop_id'   =>  $this->user['shop_id'],
            'uid'       =>  $this->user['id'],
        ];
		if(in_array($param['sid'],$sArr) && isset($param['sid'])){
			$map['status']  =  $param['sid'];
		}
		$pagesize = $param['pagesize'] ? $param['pagesize'] : 15;
		$pagelist   =   pagelist([
            'table'     =>  'ActivityView',
            'do'        =>  'D',
            'pagesize'  =>  $pagesize,
			'p'         =>  $param['p'],
            'order'     =>  'id desc',
            'fields'    =>  'activity_name,icon,id,start_time,end_time,status,tyep_id,full_money,atime,type_id,full_value',
            'map'       =>  $map,
        ]);
		if($pagelist['list']){
			return ['code' => 1,'data'=>$pagelist];
		}
		return ['code' => 3];		
	}
	
	/**
     * subject: 卖家活动详情
     * api: /SellerActivity/activity_view
     * author: liangfeng
     * day: 2017-03-28
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
	 * param: id,int,1,活动id 
     */
	public function activity_view(){
		$this->check('openid,id',false);
        $res = $this->_activity_view($this->post);
        $this->apiReturn($res);
    }
	public function _activity_view($param){
		$id =   intval($param['id']);
        if ($id > 0) {
            $data   =   D('ActivityView')->where(['shop_id' => $this->user['shop_id'], 'id' => $id])->find();
            if ($data) {
			
                $data['participate']    =   pagelist([
                    'table'     =>  'ActivityParticipateView',
                    'do'        =>  'D',
                    'pagesize'  =>  10,
                    'order'     =>  'id desc',
                    'map'       =>  ['activity_id' => $data['id'], 'status' => 1],
                ]);
				
                $inArr  =   [2,5,6];    //赠送的商品、秒杀、0元购
                if (in_array($data['type_id'], $inArr)) {    //赠送的商品、秒杀、0元购
                    foreach ($data['participate']['list'] as $k => $val) {
                        $data['participate']['list'][$k]['goods']   =   getActivityFullvalueGoods($val['full_value'], $this->user['shop_id']);
                    }
                    unset($val,$k);
                    $data['goods']  =   getActivityFullvalueGoods($data['full_value'], $this->user['shop_id']);
                }
				return ['code' => 1,'data'=>$data];
            } else {
                return ['code' => 3];
            }
        } else {
            return ['code' => 3];
        }
	}
}