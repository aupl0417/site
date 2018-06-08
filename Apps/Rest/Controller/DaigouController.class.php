<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 申请代购
+----------------------------------------------------------------------
| Author: 李祖衡
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
use Common\Builder\Daigou;
class DaigouController extends CommonController {
// 	protected  $action_logs = array('add','edit','delete');

    protected $statusName = [1 => '审核中', 2 => '审核已通过', 3 => '审核被拒绝'];
    
    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 申请、修改代购
    * @param string     $_POST['openid']         用户openid
    * @param string     $_POST['goods_name']     商品名称
    * @param string     $_POST['url']            商品链接
    * @param int        $_POST['price']          代购价格
    * @param string     $_POST['attr_name']      代购颜色规格
    * @param string     $_POST['remark']         留言备注
	* @param string     $_POST['id']             申请代购ID
    */
    public function add(){
        $this->apiReturn(4,'',1,'代购功能已暂停！');
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','goods_name','url','num','price','sign');
        $this->_need_param();
        $this->_check_sign();
        
        $data['goods_name'] = I('post.goods_name');
        $data['url']        = I('post.url');
		$data['num']        = I('post.num');
        $data['price']      = I('post.price');
        $data['attr_name']  = I('post.attr_name');
        $data['remark']     = I('post.remark');
        $data['status']     = 1;
        $data['uid']        = $this->uid;
        $data['images']     = trim(I('post.images'), ',');
        $data['d_no']       = $this->create_orderno('DG',$this->uid);
        $do=D('Common/Daigou');
		
        if(!$do->create($data)) $this->apiReturn(4,'',1,$do->getError());
		if(I('post.id')){
			$data['id'] = I('post.id');
			if($do->save($data)){
				$this->apiReturn(1);
			}else{
				//更新失败
				$this->apiReturn(0,$data);
			}
		}else{
			if($do->add()){
				$this->apiReturn(1);
			}else{
				//添加失败
				$this->apiReturn(0,$data);
			}
		}
    }

    /**
    * 代购列表
    * @param string     $_POST['openid']    用户openid
    * @param int        $_POST['status']    审核状态
	* @param int        $_POST['id']        审核订单号
    */
    public function lists(){
        //频繁请求限制
        $this->_request_check();
        
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
        
        
        $map    =   [];
        $map['uid'] =   $this->uid;
        if (isset($_POST['status']) && array_key_exists(I('post.status'), $this->statusName)) {
            $map['status'] = I('post.status');
        }else{
			$map['status'] = array('neq','0');
		}
		if (isset($_POST['goods_name'])) {
            $map['goods_name'] = array('like','%'.I('post.goods_name').'%');
        }
		
        $do = M('daigou');
        $list = pagelist([
            'table'     =>  'daigou',
            'do'        =>  'M',
            'map'       =>  $map,
            'order'     =>  'id desc',
            'pagesize'  =>  10,
			'p'         =>  I('post.goods_name')?"":I('post.p'),
        ]);
		
        $daigou = new Daigou();
        if (!empty($list)) {
            foreach ($list['list'] as &$v) {
                $v['status_name'] = $this->statusName[$v['status']];
                $v['cost_price'] = $daigou->getCostPrice($v['price']);
                if (!empty($v['images'])) {
                    $pic = explode(',', trim($v['images'], ','));
                    $v['thumbnail'] = myurl($pic[0], 100);
                } else {
                    $v['thumbnail'] = myurl(null, 100);
                }
            }
            unset($v);
            $list['count']['all'] = $do->where(['uid' => $this->uid])->count();
            $list['count'][1] = $do->where(['uid' => $this->uid, 'status' => 1])->count();
            $list['count'][2] = $do->where(['uid' => $this->uid, 'status' => 2])->count();
            $list['count'][3] = $do->where(['uid' => $this->uid, 'status' => 3])->count();
            $this->apiReturn(1,array('data'=>$list));
        }
        $this->apiReturn(3);     
    }
    
    /**
     * 列表
     */
    public function wapLists() {
        //频繁请求限制
        $this->_request_check();
        
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
        
        
        $map    =   [];
        $map['uid'] =   $this->uid;
        if (isset($_POST['status']) && array_key_exists(I('post.status'), $this->statusName)) {
            $map['status'] = I('post.status');
        }else{
			$map['status'] = array('neq','0');
		}
        $do = M('daigou');
        $list = pagelist([
            'table'     =>  'daigou',
            'do'        =>  'M',
            'map'       =>  $map,
            'order'     =>  'id desc',
            'pagesize'  =>  10,
            'p'         =>  I('post.p'),
        ]);
		
        $daigou = new Daigou();
        if (!empty($list)) {
            foreach ($list['list'] as &$v) {
                $v['status_name'] = $this->statusName[$v['status']];
                $v['cost_price'] = $daigou->getCostPrice($v['price']);
                if (!empty($v['images'])) {
                    $pic = explode(',', trim($v['images'], ','));
                    $v['thumbnail'] = myurl($pic[0], 100);
                } else {
                    $v['thumbnail'] = myurl(null, 100);
                }
            }
            unset($v);
            $list['count']['all'] = $do->where(['uid' => $this->uid,'status' => array("neq",0)])->count();
            $list['count'][1] = $do->where(['uid' => $this->uid, 'status' => 1])->count();
            $list['count'][2] = $do->where(['uid' => $this->uid, 'status' => 2])->count();
            $list['count'][3] = $do->where(['uid' => $this->uid, 'status' => 3])->count();
            $this->apiReturn(1,array('data'=>$list));
        }
        $this->apiReturn(3);
    }
    
    /**
    * 代购详情
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['id']        ID
    */
    public function view(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();      

        //必传参数检查
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('daigou');
        $rs=$do->where(array('uid'=>$this->uid,'id'=>I('post.id')))->find();
        if($rs){
			if($rs['images']){
				$rs['images'] = explode(',',$rs['images']);
			}
            //返回详情
            $this->apiReturn(1,array('data'=>$rs));
        }else{
            //找不到记录
            $this->apiReturn(0);
        }
    }
    	
	/**
    * 删除代购
    * @param string     $_POST['openid']    用户openid
    * @param int        $_POST['id']        代购订单ID
    */
    public function delete(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('openid','sign','id');
		$this->_need_param();
		$this->_check_sign();
        
        $do = M('daigou');
		$data['status'] = 0;
		$res  = $do->where(array('id'=>I('post.id')))->getField('status');
		if($res == 2){
			$this->apiReturn(0,array('msg'=>'代购商品审核通过，删除失败！'));
		}
		
		$list = $do->where(array('id'=>I('post.id')))->save($data);     
        if($list){
            $this->apiReturn(1);
        }else{
            $this->apiReturn(3);
        }      
    }
    /**
     * WAP详情
     */
    public function wapView() {
    //频繁请求限制,间隔300毫秒
        $this->_request_check(); 
        //必传参数检查
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();
        $do=M('daigou');
        $rs=$do->where(array('uid'=>$this->uid,'d_no'=>I('post.id')))->find();
        if($rs){
            //返回详情
            $daigou = new Daigou();
            $rs['status_name'] = $this->statusName[$rs['status']];
            $rs['cost_price'] = $daigou->getCostPrice($rs['price']);
            $rs['thumbnail'] = [];
            if (!empty($rs['images'])) {
                $pic = explode(',', trim($rs['images'], ','));
                $cnt = count($pic);
                for ($i=0; $i<$cnt;$i++) {
                    $rs['thumbnail'][$i] = myurl($pic[$i], 100);
                }
            }
            $this->apiReturn(1,array('data'=>$rs));
        }else{
            //找不到记录
            $this->apiReturn(0);
        }
    }
}