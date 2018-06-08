<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 成本录入
+----------------------------------------------------------------------
| Author: 李祖衡
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
use Common\Builder\Daigou;
class ScmController extends CommonController {
    protected $action_logs = array('save','edit_save');
    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 成本录入
	* @param string     $_POST['id']              订单ID
    * @param string     $_POST['openid']          用户openid
    * @param string     $_POST['profit_price']    金额
    * @param string     $_POST['purchase_price']  成本价格
	* @param string     $_POST['other_price']     运费
	* @param string     $_POST['num']             数量
    */
    public function save(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','profit_price','purchase_price','num','id','sign');
        $this->_need_param();
        $this->_check_sign();
        
        $data['id'] 		    = I('post.id');
		$data['cost_price']     = I('post.other_price')+I('post.purchase_price');
        $data['profit_price']   = I('post.profit_price')-$data['cost_price'];
		$data['other_price']    = I('post.other_price');
        $data['purchase_price'] = I('post.purchase_price');
		$do=M('orders_goods');

		if($do->save($data) !== false ){
			$this->apiReturn(1);
		}else{
			//更新失败
			$this->apiReturn(0,$data);
		}
    }
	
	/**
    * 成本修改录入
	* @param string     $_POST['id']              订单ID
    * @param string     $_POST['openid']          用户openid
    * @param string     $_POST['profit_price']    金额
    * @param string     $_POST['purchase_price']  成本价格
	* @param string     $_POST['other_price']     运费
	* @param string     $_POST['refund_totals_price']   退款总额
	* @param string     $_POST['refund_express_price']  退款运费
    */
    public function edit_save(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','profit_price','purchase_price','id','sign');
        $this->_need_param();
        $this->_check_sign();
        
        $data['id'] 		          = I('post.id');
		$data['cost_price']           = I('post.other_price')+I('post.purchase_price');
        $data['profit_price']         = I('post.profit_price')-$data['cost_price'];
		$data['other_price']          = I('post.other_price');
        $data['purchase_price']       = I('post.purchase_price');
		$data['refund_totals_price']  = I('post.refund_totals_price');
		$data['refund_express_price'] = I('post.refund_express_price');
		$data['purchase_time']        = date('Y-m-d H:i:s',time());
	
		$do=M('orders_goods');
		
		if($do->save($data) !== false ){
			$this->apiReturn(1);
		}else{
			//更新失败
			$this->apiReturn(0,$data);
		}
    }
	
	/**
    * 报表明细
    * @param string     $_POST['openid']    用户openid
    * @param int        $_POST['status']    审核状态
	* @param int        $_POST['id']        id
    */
    public function lists(){
        //频繁请求限制
        $this->_request_check();
        
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
        
        $map['uid'] =   $this->uid;
		
		if (isset($_POST['id']) && I('post.id')!="") {
            $map['id'] = I('post.id');
        } 
		
		if(isset($_POST['sday']) && I('post.sday') !="" && isset($_POST['eday']) && I('post.eday') !=""){
			$map['atime']   = array(array('egt',I('post.sday')),array('elt',I('post.eday')));
		}else if(isset($_POST['sday']) && I('post.sday') !="" && I('post.eday') ==""){
			$map['atime']   = array('egt',I('post.sday'));
		}else if(isset($_POST['eday']) && I('post.eday') !="" && I('post.sday') ==""){
			$map['atime']   = array('elt',I('post.eday'));
		}
		
        $do = M('scm_totals');
        $list = pagelist([
            'table'     =>  'scm_totals',
            'do'        =>  'M',
            'map'       =>  $map,
            'order'     =>  'id desc',
            'pagesize'  =>  15,
			'p'         =>  I('post.id')?"":I('post.p'),
        ]);
		
        if (!empty($list)) {

            $this->apiReturn(1,array('data'=>$list));
        }
        $this->apiReturn(3);     
    }
	
	/**
    * 根据店铺ID，统计利润
    * @param string     $_POST['openid']    用户openid
	* @param int        $_POST['id']        店铺id
	* @param int        $_POST['day']       统计的日期，默认前一天
    */
	
	public function totals_profit_shop(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('id','sign');
        $this->_need_param();
        $this->_check_sign();

		$do     = M('orders_goods');
		$scm    = M('scm_totals');
		//取出商店的日期

        $day = I('post.day') ? I('post.day') : date('Y-m-d',time()-86400);
		//统计该商铺的数据
		$res = $do->field(C('DB_PREFIX').'orders_goods.seller_id as uid,sum(num) as goods_num,sum(purchase_price) as purchase_price,sum(other_price) as other_price,sum(cost_price) as cost_price,sum(profit_price) as profit_price,sum(total_price_edit) as goods_price')->join('LEFT JOIN '.C('DB_PREFIX').'orders_shop on '.C('DB_PREFIX').'orders_goods.s_no = '.C('DB_PREFIX').'orders_shop.s_no')->where('date_format(pay_time,"%Y-%m-%d")="'.$day.'" and '.C('DB_PREFIX').'orders_goods.shop_id="'.I('post.id').'"')->find();
		//dump($do->getlastsql());
		$res['day']     = $day;
		$res['atime']   = date('Y-m-d H:i:s',time());
		$res['ip']      = get_client_ip();
		if($scm->add($res)){
			$this->apiReturn(1);
		}else{
			//更新失败
			$this->apiReturn(0,$res);
		}
	}	
	
	/**
    * 根据日期，重新统计
    * @param string     $_POST['openid']    用户openid
    * @param int        $_POST['day']    	更新的日期
	* @param int        $_POST['id']        scm_id
    */
	
	public function totals_profit(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','day','id','sign');
        $this->_need_param();
        $this->_check_sign();
        
     //   $data['day'] = I('post.day');
		
		$do=M('orders_goods');
		$res = $do->field(C('DB_PREFIX').'orders_goods.seller_id as uid,sum(num) as goods_num,sum(purchase_price) as purchase_price,sum(other_price) as other_price,sum(cost_price) as cost_price,sum(profit_price) as profit_price,sum(total_price_edit) as goods_price')->join('LEFT JOIN '.C('DB_PREFIX').'orders_shop on '.C('DB_PREFIX').'orders_goods.s_no = '.C('DB_PREFIX').'orders_shop.s_no')->where('date_format(pay_time,"%Y-%m-%d")="'.I('post.day').'" and '.C('DB_PREFIX').'orders_goods.seller_id="'.$this->uid.'"')->find();
		if(!$res['uid']){
			$this->apiReturn(0,$res);
		}
		//dump($do->getlastsql());
		$res['day']   = I('post.day');
		$res['id']    = I('post.id');
		$res['etime'] = date('Y-m-d H:i:s',time());
		$res['ip']    = get_client_ip();
		$scm=M('scm_totals');
		if($scm->save($res)){
			$this->apiReturn(1);
		}else{
			//更新失败
			$this->apiReturn(0,$res);
		}
	}	
	
	/**
    * 根据日期.批量重新统计
    * @param string     $_POST['openid']    用户openid
    * @param int        $_POST['day']    	更新的日期
	* @param int        $_POST['id']        scm_id
    */
	public function all_totals_profit(){
		//频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','day','id','sign');
        $this->_need_param();
        $this->_check_sign();
		     
		//$data['day'] = I('post.day');
		$data = explode('|',I('post.day')); 
		
		$result = true;
		$do=M('orders_goods');
		foreach($data as $key=>$val){
			$res = $do->field('uid,sum(num) as goods_num,sum(purchase_price) as purchase_price,sum(other_price) as other_price,sum(cost_price) as cost_price,sum(profit_price) as profit_price,sum(total_price_edit) as goods_price')->where('date_format(atime,"%Y-%m-%d")="'.$val.'" and uid="'.$this->uid.'"')->find();
			//dump($do->getlastsql());
			$res['day']   = I('post.day');
			$res['id']    = I('post.id');
			$res['etime'] = date('Y-m-d H:i:s',time());
			$res['ip']    = get_client_ip();
			if(false === $scm->save($res)){
				$result = false;
				break;
			}
		}

		if($result){
			$this->apiReturn(1);
		}else{
			//更新失败
			$this->apiReturn(0,$res);
		}
	}
}