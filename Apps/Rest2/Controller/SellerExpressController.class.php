<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 快递
 * ----------------------------------------------------------
 * Author:lizuheng 
 * ----------------------------------------------------------
 * 2017-02-27
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller;
class SellerExpressController extends ApiController {
	/**
     * subject: 快递公司列表
     * api: /Address/default_address
     * author: lizuheng
     * day: 2017-02-26
     *
     * [字段名,类型,是否必传,说明]
	 * param: openid,string,1,用户openid
     */
    public function express_company() {
		$field = 'sign';
		$this->check($field,false);

        $res = $this->_express_company($this->post);
        $this->apiReturn($res);
    }
	public function _express_company($param) {
		$list=M('express_category')->cache(true,C('CACHE_LEVEL.OneDay'))->where(['status' => 1])->field('id,category_name')->order('sort asc')->select();
          //数据格式化输出
        foreach($list as $i=>$val){
            $list[$i]['dlist']=M('express_company')->cache(true,C('CACHE_LEVEL.OneDay'))->where(['category_id'=>$val['id'],'status'=>1])->field('id,company,sub_name,code,logo')->order('sort asc')->select();
            foreach($list[$i]['dlist'] as $k=>$v){
                $list[$i]['dlist'][$k]['logo']=myurl($list[$i]['dlist'][$k]['logo'],150,50);
            }
        }
		if($list){
            return ['code' => 1,'data' => $list];
        }else{
            //找不到记录
            return ['code' => 3,'msg' => '找不到记录'];
        }
    }
}