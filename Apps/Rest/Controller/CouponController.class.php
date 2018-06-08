<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 优惠券
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class CouponController extends CommonController {
    protected $_status=array(1 => '未使用', 2 => '已使用', 3 => '已过期');
    protected $_use_type = [1=>'全店通用',2=>'指定店铺',3=>'指定商品',4=>'指定类目'];
	protected $action_logs = array('get_coupon');
    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 可领取优惠券
    */
    public function coupon_batch(){
        //频繁请求限制
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
		
		//$this->uid;
		//M('shop_fav')->field('shop_id')->where('uid = '.$this->uid)->select();
/*
        $do=D('CouponBatchView');
        $list=$do->where(array('eday'=>array('egt',date('Y-m-d'),'_string'=>'use_num<num')))->field('sday,eday,min_price,price,id,get_num,shop_name,use_num,num,shop_id')->limit(20)->select();
*/
		
		$map['eday'] = array('egt',date('Y-m-d'));
		$map['status'] = 1;
		//只选出已生成订单和关注的商家、关注的商品、购物车的商品的优惠卷
		/*$map['_string'] = ' (num = 0 or use_num < num)  and ('
			.'shop_id in (select shop_id from '.C('DB_PREFIX').'shop_fav where uid = '.$this->uid.') or '
			.'shop_id in (select shop_id from '.C('DB_PREFIX').'orders_shop where uid = '.$this->uid.') or '
			.'shop_id in (select shop_id from '.C('DB_PREFIX').'cart where uid = '.$this->uid.') or '
			.'shop_id in (select shop_id from '.C('DB_PREFIX').'goods where id in(select goods_id from '.C('DB_PREFIX').'goods_fav where uid = '.$this->uid.')))';
		*/
		if (isset($_POST['shop_id']) && !empty(I('post.shop_id'))) {
		    $map['shop_id'] = I('post.shop_id');
		}
		$pagelist=pagelist(array(
                'table'     =>'CouponBatchView',
                'do'        =>'D',
                'map'       =>$map,
                'order'     =>'eday asc',
                'fields'    =>'sday,eday,min_price,price,id,get_num,shop_name,use_num,num,shop_id,max_num,shop_logo,atime',
                'pagesize'  =>20,
                //'relation'  =>true,
                'action'    =>I('post.action'),
                //'query'     =>I('query'),
                'p'         =>I('post.p'),
                //'cache_name'=>md5(implode(',',$_POST).__SELF__),
                //'cache_time'=>C('CACHE_LEVEL.L'),
            ));
		
        if($pagelist['list']){
            $this->apiReturn(1,array('data'=>$pagelist));
        }else{
            //没有可领取优惠券发行记录
            $this->apiReturn(3);
        }
    }

    /**
    * 领取优惠券
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['b_id']      优惠券批次ID
    */
    public function get_coupon(){
        //频繁请求限制
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','b_id','sign');
        $this->_need_param();
        $this->_check_sign();

        
        $do=M('coupon_batch');
        $brs=$do->where(array('id'=>I('post.b_id')))->field('atime,ip,etime',true)->find();
        if (!$brs) $this->apiReturn(4,'',1,'优惠信息不存在');
        if (strtotime($brs['eday']) <= NOW_TIME)  $this->apiReturn(4,'',1,'优惠信息已过期');
        if ($brs['num'] > 0 && $brs['get_num'] >= $brs['num']) $this->apiReturn(4,'',1,'你来晚了');
        
        //不能领取自己发行的优惠券
        if($brs['uid']==$this->uid) $this->apiReturn(181); 

        //同一个用户最多充许领取的数量
        $do=M('coupon');
        $count=$do->where(array('uid'=>$this->uid,'b_id'=>$brs['id']))->count();
        if($count>=$brs['max_num']) $this->apiReturn(4,'',1,'同一个用户最多充许领取'.$brs['max_num'].'张');

        $do->startTrans();
        //直接写进去
        $data   =   [
            'b_id'      =>  $brs['id'],
            'price'     =>  $brs['price'],
            'code'      =>  md5(NOW_TIME . $brs['id'] . $brs['shop_id'] . $this->uid),
            'shop_id'   =>  $brs['shop_id'],
            'sday'      =>  $brs['sday'],
            'eday'      =>  $brs['eday'],
            'min_price' =>  $brs['min_price'],
            'uid'       =>  $this->uid,
            'get_time'  =>  date('Y-m-d H:i:s', NOW_TIME),
            'ip'        =>  get_client_ip(),
            'type'      =>  $brs['type'],
            'use_type'  =>  $brs['use_type'],
            'goods_ids' =>  $brs['goods_ids'],
            'shop_ids' 	   =>  $brs['shop_ids'],
            'category_ids' =>  $brs['category_ids'],
			
        ];
        $data['short_code']   = shortUrl($data['code']);
        if (!$sw1 = $do->add($data)) goto error;
        if(!$sw2=M('coupon_batch')->where(array('id'=>I('post.b_id')))->setInc('get_num')) goto error;

        $do->commit();
        $this->apiReturn(1,array('data'=>$data));

        error:
            $do->rollback();
            $this->apiReturn(0);
    }

    /**
     * 获取已领取的IDS
     */
    public function getReceiveIds() {
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
        $map    =   [
            'is_use'    =>  0,
            'status'    =>  1,
            'eday'      =>  ['egt', date('Y-m-d', NOW_TIME)],
        ];
        $data    =   M('coupon')->cache(true)->where($map)->order('id desc')->getField('id,b_id');
        if($data) {
            $ids    =   '';
            foreach ($data as $v) {
                $ids    .=  $v . ',';
            }
            $this->apiReturn(1, ['data' => trim($ids, ',')]);
        } else {
            $this->apiReturn(3,'', '找不到记录');
        }
    }
    
    
    /**
    * 优惠券详情
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['code']      优惠券编码
    */
    public function view(){
        //频繁请求限制,间隔2秒
        //$this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','code','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=D('Common/CouponRelation');
        $rs=$do->relation(true)->cache(true,C('CACHE_LEVEL.XXS'))->where(array('code'=>I('post.code'),'uid'=>$this->uid))->field('id,b_id,code,short_code,price,sday,eday,min_price,uid,get_time,is_use,use_time,orders_id,use_type,shop_ids,goods_ids,category_ids')->find();

        if($rs){
            if($rs['is_use']==1) $rs['status']=2;
            elseif($rs['eday']<date('Y-m-d')) $rs['status']=0;
            else $rs['status']=1;
			$rs['use_type_name'] = $this->_use_type[$rs['use_type']];
            $rs['status_name']=$this->_status[$rs['status']];

            $this->apiReturn(1,array('data'=>$rs));
        }else{
            $this->apiReturn(0);
        }

    }

    /**
    * 我的优惠券
    * @param string $_POST['openid']    用户openid
    */
    public function my_coupon(){
        //频繁请求限制,间隔2秒
        //$this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $map['uid']=$this->uid;
        if(I('post.price')) $map['price']=I('post.price');
        $pagesize=I('post.pagesize')?I('post.pagesize'):20;
        if (I('post.status') && array_key_exists(I('post.status'), $this->_status)) {
            $map['status'] = I('post.status');
        } else {
            $map['status'] = ['gt', 0];
        }
        $pagelist=pagelist(array(
                'table'     =>'CouponRelation',
                'do'        =>'D',
                'map'       =>$map,
                'order'     =>'atime desc',
                'fields'    =>'atime,id,b_id,code,short_code,shop_id,sday,eday,price,min_price,uid,get_time,is_use,use_time,orders_id,status,type,use_type',
                'pagesize'  =>$pagesize,
                'relation'  =>true,
                'action'    =>I('post.action'),
                'query'     =>I('query'),
                'p'         =>I('post.p'),
                //'cache_name'=>md5(implode(',',$_POST).__SELF__),
                //'cache_time'=>C('CACHE_LEVEL.L'),
            ));
        $model = M('coupon');
        $pagelist['count']['all']   =   $model->where(['uid' => $this->uid, 'status' => ['gt', 0]])->count();
        $pagelist['count'][1]   =   $model->where(['uid' => $this->uid, 'status' => 1])->count();
        $pagelist['count'][2]   =   $model->where(['uid' => $this->uid, 'status' => 2])->count();
        $pagelist['count'][3]   =   $model->where(['uid' => $this->uid, 'status' => 3])->count();

//         foreach($pagelist['list'] as $key=>$val){
//             if($val['is_use']==1) $pagelist['list'][$key]['status']=2;
//             elseif($val['eday']<date('Y-m-d')) $pagelist['list'][$key]['status']=0;
//             else $pagelist['list'][$key]['status']=1;

//             $pagelist['list'][$key]['status_name']=$status[$pagelist['list'][$key]['status']];
//         }
        if ($pagelist['list']) {
            foreach ($pagelist['list'] as &$v) {
                $v['status_name'] = $this->_status[$v['status']];
                $v['use_type_name'] = $this->_use_type[$v['use_type']];
                $v['shop_url'] = shop_url($v['shop_id']);
            }
            unset($v);
        }
        //if($pagelist['list']){
            $this->apiReturn(1,array('data'=>$pagelist));
        //}else{
         //   $this->apiReturn(3);
        //}
    }
    
    /**
     * 统计
     */
    public function count() {
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
        $model  = M('coupon');
        $data   = [];
        $data['no_use'] = $model->where(['uid' => $this->uid, 'status' => 1])->count(); //未使用
        $data['use']    = $model->where(['uid' => $this->uid, 'status' => 2])->count();    //已使用
        $data['expire'] = $model->where(['uid' => $this->uid, 'status' => 3])->count(); //已过期
        $this->apiReturn(1, ['data' => $data]);
    }
    
    //获取商家优惠券
    public function getShopCoupon() {
        //必传参数检查
        $this->need_param=array('shop_id','sign');
        $num    =   isset($_POST['num']) && I('post.num') > 0 ? I('post.num') : 6;
        $this->_need_param();
        $this->_check_sign();
        $model  =   M('coupon_batch');
        $map    =   [
            'shop_id'   =>  I('post.shop_id'),
            'status'    =>  1,
            'eday'      =>  ['egt', date('Y-m-d', NOW_TIME)],
        ];
        
        //默认最多列出6条
        $data['coupon']   =   $model->where($map)->field('id,price,min_price,shop_id')->order('price asc')->limit($num)->select();
        
        if ($data['coupon']) {
            $data['count']  =   count($data['coupon']);
            $this->apiReturn(1,['data' => $data]);
        }
        $this->apiReturn(3);
    }
    
    /**
     * 推荐的优惠券
     */
    public function recom() {
        //频繁请求限制
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('sign', 'id');
        $this->_need_param();
        $this->_check_sign();
        
        if (isset($_POST['id']) && !empty(I('post.id'))) {
            $category_id = I('post.id');
        } else {
            $category_id = M('coupon_recom_category')->cache(true)->where(['status' => 1])->order('sort asc, id asc')->getField('id');   //默认为第一个
        }
        $map = [
            'category_id' => $category_id,
            'status' => 1,
            //'batch_status' => 1
        ];
        $pagelist = pagelist([
            'table'     => 'CouponRecom1View',
            'do'        => 'D',
            'pagesize'  => 10,
            'p'         => I('post.p'),
            'map'       => $map,
            'order'     => 'sort asc',
        ]);
        
        $pagelist['cates'] = M('coupon_recom_category')->cache(true)->where(['status' => 1])->order('sort asc')->field('id,name')->select();
        
        $this->apiReturn(1, ['data' => $pagelist]);
    }

    /**
     * 优惠券  -- PC
     */
    public function lists() {
        //频繁请求限制
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        $map['eday']   = array('egt',date('Y-m-d'));
        $map['status'] = 1;
        $map['face_type']   =   1;
        $order = '';
        if (isset($_POST['sort'])) {
            switch ($_POST['sort']) {
                case 'new':
                    $order = 'id asc';
                    break;
                case 'expire':
                    $order = 'eday asc';
                    break;
                case 'quota':
                    $order = 'price desc';
                    break;
            }
        }
        if (isset($_POST['self'])) $map['type_id'] = 1;
        if (isset($_POST['cat'])) $map['_string']  = 'FIND_IN_SET("'.$_POST['cat'].'", shop.category_id)';
        if (isset($_POST['shop'])) $map['shop_id'] = $_POST['shop'];
        $pagelist=pagelist(array(
            'table'     =>'CouponBatchView',
            'do'        =>'D',
            'map'       =>$map,
            'order'     =>$order,
            'fields'    =>'sday,eday,min_price,price,id,get_num,shop_name,use_num,num,shop_id,max_num,shop_logo,atime,domain,category_id,use_type,shop_ids,goods_ids,category_ids',
            'pagesize'  =>24,
            //'relation'  =>true,
            'action'    =>I('post.action'),
            //'query'     =>I('query'),
            'p'         =>I('post.p'),
            //'cache_name'=>md5(implode(',',$_POST).__SELF__),
            //'cache_time'=>C('CACHE_LEVEL.L'),
        ));
		foreach($pagelist['list'] as $k => $v){
			$pagelist['list'][$k]['use_type_name'] = $this->_use_type[$v['use_type']];
			
		}
		$pagelist['map'] = $map;
        if ($pagelist['list']) $this->apiReturn(1, ['data' => $pagelist]);
        $this->apiReturn(3);
    }

    /**
     * 删除优惠券
     */
    public function delete() {
        //频繁请求限制
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('sign', 'id', 'openid');
        $this->_need_param();
        $this->_check_sign();

        $map = [
            'uid'   =>  $this->uid,
            'id'    =>  I('post.id'),
            'status'=>  ['gt', 0],
        ];

        if (M('coupon')->where($map)->save(['status' => 0])) $this->apiReturn(1);

        $this->apiReturn(0);

    }
}