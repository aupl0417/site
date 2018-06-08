<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 快递相关
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-03-01
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class ExpressController extends ApiController {
    //protected $action_logs = array('ad');

    /**
     * subject: 快递公司列表
     * api: /Express/company
     * author: Lazycat
     * day: 2017-03-01
     *
     * [字段名,类型,是否必传,说明]
     */
    public function company(){
        $this->check('',false);

        $res = $this->_company($this->post);
        $this->apiReturn($res);
    }


    public function _company($param=null){
        $list = M('express_category')->cache(true)->where(['status' => 1])->field('id,category_name')->order('sort asc,id asc')->select();
        foreach($list as $key => $val){
            $list[$key]['dlist'] = M('express_company')->cache(true)->where(['status' => 1,'category_id' => $val['id']])->field('id,company,sub_name,code,logo')->order('sort asc,id asc')->select();
        }

        if($list){
            return ['code' => 1,'data' => $list];
        }

        return ['code' => 3,'msg' => '找不快递公司！'];
    }
	
    /**
     * subject: 快递查询
     * api: /Express/query_express
     * author: lizuheng
     * day: 2017-04-10
     *
     * [字段名,类型,是否必传,说明]
     * param: company_id,int,1,快递公司ID
     * param: express_code,string,1,快递单号
     */
    public function query_express(){
		$field = 'express_code,company_id';
        $this->check($field,false);

        $res = $this->_query_express($this->post);
        $this->apiReturn($res);
    }


    public function _query_express($param=null){
		$do  = M('express_company');
		$rs = $do->where(array('id'=>$param['company_id']))->field('id,company,sub_name,logo,website,tel,code')->find();
        if($rs){
			$rs['express_code']=$param['express_code'];
			$url='https://www.kuaidi100.com/query?type='.$rs['code'].'&postid='.$param['express_code'];
			$res=$this->curl_get($url);
			$res=json_decode($res);
			$rs['history']=objectToArray($res)['data'];
            return ['code' => 1,'data' => $rs];
        }
        return ['code' => 3,'msg' => '找不到快递记录！'];
    }
	
	/**
     * subject: 阿里云快递查询
     * api: /Express/query_express_aliyun
     * author: lizuheng
     * day: 2017-04-10
     *
     * [字段名,类型,是否必传,说明]
     * param: company_id,int,1,快递公司ID
     * param: express_code,string,1,快递单号
     */
    public function query_express_aliyun(){
 		$field = 'express_code,company_id';
        $this->check($field,false); 

        $res = $this->_query_express_aliyun($this->post);
        $this->apiReturn($res);
    }


    public function _query_express_aliyun($param=null){
		if(strlen($param['express_code'])<8 || strlen($param['express_code'])>30){
			return ['code' => 3,'msg' => '快递单号错误！'];
		}
		$data = getSiteConfig('logistics');

		$do  = M('express_company');
		$rs = $do->where(array('id'=>$param['company_id']))->field('id,company,sub_name,logo,website,tel,code')->find();
		if($rs){
			$method = "GET";
			$appcode = $data['appcode'];
			$headers = array();
			array_push($headers, "Authorization:APPCODE " . $appcode);
			$querys = "com=".$rs['code']."&nu=".$param['express_code'];
			$bodys = "";
			$url = $data['apiurl'] . "?" . $querys;

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_FAILONERROR, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, false);
			if (1 == strpos("$".$host, "https://"))
			{
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			}
			$result = json_decode(curl_exec($curl),true);
			$result['logo'] = $rs['logo'];
			if($result['showapi_res_code'] !=0){
				$result['showapi_res_body']['expTextName'] = $rs['sub_name'];
				$result['showapi_res_body']['updateStr'] = $param['express_time'];
				$result['showapi_res_body']['mailNo'] = $param['express_code'];
				$result['showapi_res_body']['msg'] = $result['showapi_res_error'];
			}
			if($result['showapi_res_body']['ret_code'] != 0){
				$result['showapi_res_body']['expTextName'] = $rs['sub_name'];
				$result['showapi_res_body']['updateStr'] = '0000-00-00 00:00:00';
				$result['showapi_res_body']['mailNo'] = $param['express_code'];
			}
			return ['code' => 1,'data' => $result];
		}
		return ['code' => 3,'msg' => '找不到快递记录！'];
    } 
}