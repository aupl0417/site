<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 买家常用购物地址管理
 * ----------------------------------------------------------
 * Author:lizuheng 
 * ----------------------------------------------------------
 * 2017-02-26
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller;
class AddressController extends ApiController {
	/**
     * subject: 获取买家默认地址
     * api: /Address/default_address
     * author: lizuheng
     * day: 2017-02-26
     *
     * [字段名,类型,是否必传,说明]
	 * param: openid,string,1,用户openid
     */
    public function default_address() {
		$field = 'sign,openid';
		$this->check($field,false);

        $res = $this->_default_address($this->post);
        $this->apiReturn($res);
    }
	public function _default_address($param) {
		$do=M('shopping_address');
		$rs=$do->cache(true,C('CACHE_LEVEL.XXS'))->where(array('uid'=>$this->user['id']))->field('atime,etime,ip',true)->order('is_default desc')->find();
		if($rs){
            //返回详情
            $area   =$this->cache_table('area');
            $rs['province_name']    =$area[$rs['province']];
            $rs['city_name']        =$area[$rs['city']];
            $rs['district_name']    =$area[$rs['district']];
            $rs['town_name']        =$area[$rs['town']];    
                        
            return ['code' => 1,'data' => $rs];
        }else{
            //找不到记录
            return ['code' => 3,'msg' => '找不到记录'];
        }
    }
	
	/**
     * subject: 获取买家默认地址
     * api: /Address/address_list
     * author: lizuheng
     * day: 2017-02-27
     *
     * [字段名,类型,是否必传,说明]
	 * param: openid,string,1,用户openid
     */
    public function address_list() {
		$field = 'sign,openid';
		$this->check($field,false);

        $res = $this->_address_list($this->post);
        $this->apiReturn($res);
    }
	public function _address_list($param) {
		$do=M('shopping_address');
		
        $list=$do->where(array('uid'=>$this->user['id']))->field('etime,ip',true)->order('is_default desc,id desc')->select();
		if($list){
            $area   =$this->cache_table('area');
            foreach($list as $key=>$val){
                $list[$key]['province_name']    =$area[$val['province']];
                $list[$key]['city_name']        =$area[$val['city']];
                $list[$key]['district_name']    =$area[$val['district']];
                $list[$key]['town_name']        =$area[$val['town']];
                
            }   
                        
            return ['code' => 1,'data' => $list];
        }else{
            //找不到记录
            return ['code' => 3,'msg' => '找不到记录'];
        }
    }
}