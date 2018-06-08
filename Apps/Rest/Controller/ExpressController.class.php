<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 快递查询
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class ExpressController extends CommonController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 根据商家订单号查询快递
    * @param string $_POST['s_no']  订单号
    */
    public function query_express(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('s_no','sign');
        $this->_need_param();
        $this->_check_sign();
		
		$orders=new \Common\Controller\OrdersController(array('s_no'=>I('post.s_no')));
		$res=$orders->query_express();
		
		$this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']); 
    }	
	
    /**
    * 查询快递
    * @param int    $_POST['company_id']    快递公司ID
    * @param string $_POST['express_code']  快递单号
    */
    public function query_express2(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('company_id','express_code','sign');
        $this->_need_param();
        $this->_check_sign();
		
		$do=M('express_company');
		$rs=$do->cache(true,C('CACHE_LEVEL.S'))->where(array('id'=>I('post.company_id')))->field('id,company,sub_name,logo,website,tel,code')->find();
		
		if($rs){
			$rs['express_code']=I('post.express_code');
			$url='https://www.kuaidi100.com/query?type='.$rs['code'].'&postid='.I('post.express_code');
			$res=$this->curl_get($url);
			$res=json_decode($res);
			$rs['express']=objectToArray($res)['data'];
			$this->apiReturn(1,array('data'=>$rs));
		}else{
			//找不到快递公司
			$this->apiReturn(3);
		}
    }
 
    /**
    * 阿里云查询快递
    * @param int    $_POST['company_id']    快递公司ID
    * @param string $_POST['express_code']  快递单号
    */
    public function query_express_aliyun(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('company_id','express_code','sign');
        $this->_need_param();
        $this->_check_sign();
		
		if(strlen(I('post.express_code'))<8 || strlen(I('post.express_code'))>30){
			$this->apiReturn(4,'',1,"快递单号错误");
		}
		$data = getSiteConfig('logistics');

		$do  = M('express_company');
		$rs = $do->where(array('id'=>I('post.company_id')))->field('id,company,sub_name,logo,website,tel,code')->find();
		if($rs){
			$method = "GET";
			$appcode = $data['appcode'];
			$headers = array();
			array_push($headers, "Authorization:APPCODE " . $appcode);
			$querys = "com=".$rs['code']."&nu=".I('post.express_code');
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
				$result['showapi_res_body']['mailNo'] = I('post.express_code');
			}
			$this->apiReturn(1,array('data'=>$result));
		}
		$this->apiReturn(3);
    }
	
    /**
     * 搜索快递公司
     */
    public function search() {
        //必传参数检查
        $this->need_param=array('q','sign');
        $this->_need_param();
        $this->_check_sign();
        $q = I('post.q');
        if ($q != 'all') {
            $map['company'] = ['like', '%' . (string)$q . '%'];
        }
        //$map['status'] = 1;
        //$rs=$do->cache(true)->where($map)->field('id,company,sub_name,logo,website,tel,code')->order('id asc')->select();

        $list = pagelist([
            'do'        =>  'M',
            'table'     =>  'express_company',
            'map'       =>  $map,
            'order'     =>  'category_id asc, id asc',
            'fields'    =>  'id,company,sub_name,logo,website,tel,code,category_id',
            'action'    =>  I('post.action'),
            'p'			=>  I('post.p'),
            'cache'     =>  true,
        ]);

        if($list) {
            $this->apiReturn(1,array('data'=>$list));
        }
        $this->apiReturn(3);
    }

}