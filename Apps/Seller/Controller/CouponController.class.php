<?php
namespace Seller\Controller;
use Common\Form\Form;

class CouponController extends AuthController {
    public function _initialize() {
        parent::_initialize();
        if (session('user.shop_type') == 6) {   //如果为个人店的话则执行跳转
            redirect(DM('zhaoshang', '/shopup'));
        }
    }
    
    public function index() {
        $map    =   [
            'uid'       =>  getUid(),
            'shop_id'   =>  getShopId(),
        ];
        $statusArr = [1,2];
        $status = I('get.sid', 0, 'int');
        if (in_array($status, $statusArr)) $map['status'] = $status;
        $data   =   pagelist([
            'table'         =>  'coupon_batch',
            'do'            =>  'M',
            //'cache_name'    =>  md5('coupon_index' . getUid() . getShopId() . I('get.p')),
            'pagesize'      =>  15,
            'fields'        =>  'id,sday,eday,max_num,num,price,atime,use_num,get_num,min_price,status,channel',
            'map'           =>  $map,
            'order'         =>  'id desc',
        ]);
        $this->assign('data', $data);
        C('seo', ['title' => '优惠券管理']);
		$this->seo(['title' => '优惠券管理']);
        $this->display();
    }
    
    public function create() {
        if (IS_POST) {
            $data   =   I('post.');
            $id     =   !empty($data['id']) ? intval($data['id']) : 0;
            $model  =   D('CouponBatch');
            if (!$model->token(false)->create($data)) {
                $msg = $model->getError();
                if (function_exists($model->getError())) {
                    $msg = call_user_func_array($model->getError(), []);
                }
                $this->ajaxReturn(['code' => 0, 'msg' => $msg]);
            }
			
            $model->startTrans();
            if ($id > 0) {
                if ($model->where([['id' => $id, 'uid' => getUid(), 'shop_id' => getShopId()]])->getField('channel') == 2) $this->ajaxReturn(['code' => 0, 'msg' => '抽奖优惠券不可修改']);
                $flag   =   $model->where(['id' => $id, 'uid' => getUid(), 'shop_id' => getShopId()])->save();
            } else {
                $flag   =   $model->add();
            }
            if ($flag == false) goto error;

            $model->commit();
            $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']);
            error:
                $model->rollback();
                $this->ajaxReturn(['code' => 0, 'msg' => '数据未更新']);
			
        } else {
            $data   =   [];
            $title  =   '添加优惠券';
            if (isset($_GET['id']) && intval(I('get.id')) > 0) {
                $data   =   M('coupon_batch')->where(['id' => I('get.id'), 'shop_id' => $this->_map['id'], 'uid' => getUid()])->field('atime,etime,ip',true)->find();
                if ($data['num'] == 0) unset($data['num']);
                $title  =   '修改优惠券';
                if ($data['channel'] == 2) {
                    $this->display(T('Home@Empty:404'));
                    exit();
                }
            }
			//面值
			$prices=['1'=>'
			1元','2'=>'2元','3'=>'3元','5'=>'5元','10'=>'10元','20'=>'20元','50'=>'50元','100'=>'100元','200'=>'200元','300'=>'300元','500'=>'500元'];
			
			//面值
			$receive_num=['1'=>'
			1张','2'=>'2张','3'=>'3张','4'=>'4张','5'=>'5张','6'=>'6张','7'=>'7张','8'=>'8张','9'=>'9张','10'=>'10张','15'=>'15张','20'=>'20张',];
			
			
			
            if (C('DEFAULT_THEME') == 'default') {
                $this->builderForm()
                    ->keyId()
                    ->keyText('price', '面值', 1)
                    ->keyText('min_price', '需消费金额', 1)
                    ->keyText('num', '发行数量', '', '不填则不限')
                    ->keyText('max_num', '领取数量', 1, '单个用户最多可领取数量')
                    ->keyDate('sday', '生效时间', 1)
                    ->keyDate('eday', '失效时间', 1, '失效时间不能大于生效时间30天')
                    ->data($data)
                    ->view();
            } else {
                $couponMax = C('cfg.activity')['coupon_max'];
                $config['action'] = U('/coupon/save');
                $config['gourl']  = '"' . U('/coupon') . '"';
                $form = Form::getInstance($config)
                    ->hidden(['name' => 'id', 'value' => $data['id']])
                    ->select(['name' => 'price', 'options' => $prices,'value'=>$data['price'],'title' => '面值','require' => 1, 'validate' => ['required', 'number']])
                    ->number(['name' => 'min_price', 'value' => $data['min_price'], 'title' => '最低使用限额', 'require' => 1, 'validate' => ['required', 'number', 'min' => 1]])
                    ->number(['name' => 'num', 'value' => $data['num'], 'title' => '发行数量','require' => 1, 'tips' => '最高10万张','validate' => ['required', 'number', 'min' => 1.0, 'max' => 100000.0]])
                    ->select(['name' => 'max_num', 'options' => $receive_num,'value' => $data['max_num'], 'title' => '每人最多领取数量', 'tips' => '单个用户最多可领取数量', 'require' => 1, 'validate' => ['required', 'number']])
                    ->dates(['name' => 'sday', 'value' => $data['sday'], 'title' => '生效时间', 'require' => 1, 'validate' => ['required']])
                    ->dates(['name' => 'eday', 'value' => $data['eday'], 'title' => '失效时间', 'require' => 1, 'validate' => ['required']])
					->radio(['name'=>'use_type','value'=>($data['use_type']?$data['use_type']:1),'title'=>'使用场景','require'=>1,'options'=>[1=>'全店通用',3=>'指定商品'],'validate' => ['required']])
					->goods(['name'=>'goods_ids','title'=>'选择商品','value'=>$data['goods_ids'],'url'=>'/Coupon/goods_checkbox'])
                    ->submit(['title' => $title])
                    ->create();

                $this->assign('form', $form);
            }

            C('seo', ['title' => $title]);
            $this->assign('title', $title);
            $this->display();

        }
    }

    /**
     *
     * 保存
     *
     */
    public function save() {
        if (IS_POST) {
            $data   =   I('post.');
            $id     =   !empty($data['id']) ? intval($data['id']) : 0;

			//是否勾选商品
			if(I('post.use_type') == 3 && I('post.goods_ids') == ''){
				$this->ajaxReturn(['code' => 0, 'msg' => '请选择需要参加的商品']);
			}
			//是否存在相同优惠券
			if(I('post.use_type') == 1){
				$check_map2['sday']		= ['between',[I('post.sday'),I('post.eday')]];
				$check_map2['eday']		= ['between',[I('post.sday'),I('post.eday')]];
				$check_map2['_logic']	= 'or';
				$check_map['shop_id'] 	= $_SESSION['user']['shop_id'];
				$check_map['price'] 	= I('post.price');
				$check_map['_logic']	= 'and';
				$check_map['use_type'] 	= 1;
				$check_map['id'] 		= ['neq',$id];
				$check_map['_complex'] 	= $check_map2;
				
				$res = D('CouponBatch')->where($check_map)->find();
				//dump(D()->getlastsql());
				//dump($check_map);
				//dump(I('post.'));
				//dump($res);
				if($res){
					$this->ajaxReturn(['code' => 0, 'msg' => '选择的时间段内存在相同的优惠券']);
				}
			}
			if($id == 0){
				$data['b_no'] = $this->create_orderno('YT',$this->uid);
			}
			
            $model  =   D('CouponBatch');
            if (!$model->token(false)->create($data)) {
                $msg = $model->getError();
                if (function_exists($model->getError())) {
                    $msg = call_user_func_array($model->getError(), []);
                }
                $this->ajaxReturn(['code' => 0, 'msg' => $msg]);
            }
			
			//dump($model->token(false)->create($data));exit();
			
			
			
            $model->startTrans();
            if ($id > 0) {
                if ($model->where([['id' => $id, 'uid' => getUid(), 'shop_id' => getShopId()]])->getField('channel') == 2) $this->ajaxReturn(['code' => 0, 'msg' => '抽奖优惠券不可修改']);
                $flag   =   $model->where(['id' => $id, 'uid' => getUid(), 'shop_id' => getShopId()])->save();
            } else {
                $flag   =   $model->add();
            }
            if ($flag == false) goto error;

            $model->commit();
            $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']);
            error:
            $model->rollback();
            $this->ajaxReturn(['code' => 0, 'msg' => '数据未更新']);
			
        }
    }
    
    public function detail() {
        $id =   I('get.id');
        if ($id > 0) {
            $data   =   M('coupon_batch')->cache(true)->where(['id' => $id, 'uid' => getUid(), 'shop_id' => getShopId()])->find();
            if ($data) {
                $map    =   [
                    'b_id'  =>  $data['id'],    
                ];
                $data['use']    =   pagelist([
                    'table'     =>  'CouponReceiveView',
                    'do'        =>  'D',
                    'pagesize'  =>  10,
                    //'cache_name'    =>  md5('coupon_detail' . getUid() . getShopId() . I('get.p') . $data['id']),
                    'fields'    =>  'id,atime,is_use,use_time,orders_id,nick',
                    'map'       =>  $map,
                    'order'     =>  'id desc',
                ]);
                $this->assign('data', $data);
                C('seo', ['title' => '优惠券详情']);
				$this->seo(['title' => '优惠券详情']);
                $this->display();
            }
        }
    }
	/**
     * 显示优惠券选中的商品
     * Create by liangfeng
     * 2017-04-25
     */
    public function show_goods(){
		$goods_ids = I('post.goods_ids');
		//if($goods_ids != ''){
			$map['id'] = ['in',$goods_ids];
			$map['shop_id'] = $_SESSION['user']['shop_id'];
			
			$list = D('GoodsRelation')->relation(true)->field('id,goods_name,images,price,num')->relationField('goods_attr_list','id')->relationLimit('goods_attr_list',1)->where($map)->limit(20)->select();
			if($list){
				$this->ajaxReturn(['code'=>1,'data'=>$list,'msg'=>'成功']);
			}else{
				$this->ajaxReturn(['code'=>3,'msg'=>'没有商品']);
			}
			
		//}else{
			
	//}
		
		//dump($goods_ids);
		
	}
	/**
     * 优惠券指定商品页面
     * Create by liangfeng
     * 2017-04-24
     */
	public function goods_checkbox(){
		$pagesize = 12;
        $map['status']  = 1;
		//dump($_SESSION);
		$map['shop_id'] = $_SESSION['user']['shop_id'];
        if(I('get.status')!='') $map['status']  = I('get.status');
        if(I('get.is_self')) $map['is_self']   = 1;
        if(I('get.is_love')) $map['is_love']   = 1;
        if(I('get.goods_name')) $map['goods_name'] = ['like','%'.I('get.goods_name').'%'];
        
       

        $list = pagelist(array(
            'table'     => 'GoodsRelation',
            'do'        => 'D',
            'pagesize'  => $pagesize,
            'map'       => $map,
            'relation'  => true,
            'fields'    => '*',
        ));
		//dump($list);
		//unset($list['list']);
	

        $this->assign('pagelist',$list);
		$this->display();
		
	}
	
    public function delete() {
        if (IS_POST) {
            
        }
    }
}