<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| Work管理端 - 会员管理
| 此文件为表单生成器创建
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/

namespace Admin\Controller;
use Common\Form\Form;
use Common\Notice\Email;
use Common\Notice\Message;
use Common\Notice\Pushs;
use Common\Notice\System;
use Think\Controller;
use Think\Exception;
use Common\Builder\R;
class UserController extends CommonModulesController {
	protected $name 			='会员管理';	//控制器名称
    protected $formtpl_id		=72;			//表单模板ID
    protected $fcfg				=array();		//表单配置
    protected $do 				='';			//实例化模型
    protected $map				=array();		//where查询条件

    protected $qa = [
    		['q' =>'谁骂玉皇大帝的儿子不识数？','a' => '玉鼎真人'],
    		['q' =>'有人说，女人象一本书，那么胖女人象什么书？','a'=>'合订本'],
    		['q' =>'哪种蛇的寿命最长？','a' => '三寸不烂之舌'],
    		['q' =>'三面墙一面空,小孩子在当中','a' =>'匹'],
    		['q' =>'什么东西越洗越脏？','a'=>'水'],
    		['q' =>'读完大学需要多长时间？','a'=>'1秒'],
    		['q' =>'别人问你什么问题你老是回答“没有”？','a'=>'你睡了没有'],
    		['q' =>'一只蚂蚁从几百万米高的山峰落下来会怎么死？','a'=>'饿死'],
    		['q' =>'飞行员从来不吃哪一种食物？','a'=>'醉鸡'],
    		['q' =>'癞蛤蟆怎样才能吃到天鹅肉？','a'=>'天鹅死了'],
    		['q' =>'来电了怎么办？','a'=>'看电视'],
    		['q' =>'什么样的房子不能住人？','a'=>'蜂房'],
    		['q' =>'什么地方只要进去一个人就客满？','a'=>'厕所'],
    		['q' =>'什么东西不能用放大镜放大？','a'=>'角度'],
    		['q' =>'谁能让全世界的妖魔鬼怪同时抱头鼠窜?','a'=>'太阳能'],
    		['q' =>'什么东西洗好了却不能吃？','a'=>'扑克'],
    		['q' =>'别人请你吃什么需要你自己花钱？','a'=>'吃官司'],
    		['q' =>'一双鞋卖16元，一只鞋卖多少钱？','a'=>'不卖'],
    		['q' =>'一个盒子有几个边？','a'=>'两个'],
    	];	//清除数据暗号

    /**
    * 初始化
    */
    public function _initialize() {
    	parent::_initialize();

    	//初始化表单模板
    	$this->_initform();

    	//dump($this->fcfg);


    	

    }

    /**
    * 列表
    */
    public function index($param=null){
    	$this->_index();
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a><a href="'.__CONTROLLER__.'/openShop/id/[id]" class="btn btn-sm btn-danger btn-rad btn-trans btn-block m0">开店</a>','td_attr'=>'width="100" class="text-center"','norder'=>1);
        /**
         * <a data-id=[id] data-status=[status] class="btn btn-sm btn-danger btn-rad btn-trans btn-block m0 btn-prohibit">冻结用户</a>
         * <a data-id="[id]" class="btn btn-sm btn-success btn-rad btn-trans btn-block m0 send-message">发送通知</a> '.(session('admin.sid')==100810427 || session('admin.sid')==1?'<a href="'.__CONTROLLER__.'/login/openid/[openid]" target="_blank" class="btn btn-sm btn-default btn-rad btn-trans btn-block m0 btn-view">登录</a>':'')
         */
    	$this->assign('fields',$this->plist(null,$btn));    

		$this->display();
    }

    /**
     * 清理Erp不存在的用户
     * Create by liangfeng
     * 2017-08-18
     */
    public function clean_user(){
        $pagesize = 20;
        $p = I('get.p') ? I('get.p') : 1;
        $limit = (($p - 1) * $pagesize).','.$pagesize;

        $user_list = M('user')->where($where)->field('id,erp_uid')->limit($limit)->order('atime asc')->select();
        if($user_list){
            foreach($user_list as $v){
                $res = R::getInstance(['url' => ['res' => '/Erp/user_info'], 'rest' => ['rest2'], 'data' => [['erp_uid' => $v['erp_uid']]]])->multiCurl();
                $res = $res['res'];
                if($res['code'] != 1 && $res['data']['id'] == 2104){
					$re = M('user')->delete($v['id']);
                }
				log_add('clean_user',['atime' => date('Y-m-d H:i:s'),'id' => $v['id'],'erp_res'=>$res,'re'=>$re]);
            }
            gourl(__CONTROLLER__.'/clean_user/p/' . ($p+1));
        }
    }

    /**
    * 添加记录
    */
    public function add($param=null){
    	$this->display();
    }
	
	/**
	* 保存新增记录
	*/
	public function add_save($param=null){
		$result=$this->_add_save();

		$this->ajaxReturn($result);
	}

	/**
	* 修改记录
	*/
	public function edit($param=null){
		$this->_edit();
		$this->display();
	}

	/**
	* 保存修改记录
	*/
	public function edit_save($param=null){
		$result=$this->_edit_save();

		$this->ajaxReturn($result);
	}

	/**
	* 删除选中记录
	*/
	public function delete_select($param=null){
		$result=$this->_delete_select();
		$this->ajaxReturn($result);
	}
	
	/**
	* 冻结选中记录
	*/
	public function prohibit($param=null){
		$ids = I("get.ids");
		$this->assign('rs',$ids);    
		$this->display();
	}
	/**
	* 冻结选中记录
	*/
	public function save_prohibit($param=null){
		$data['reason'] = I("post.reason");
		if(!$data['reason']){
			$this->ajaxReturn(['status' => 'warning', 'msg' => '请输入冻结原因']);
		}
		$ids = I("post.ids");
		if(!$ids){
			$this->ajaxReturn(['status' => 'warning', 'msg' => '请选择冻结用户']);
		}
		$data['atime']	= date('Y-m-d H:i:s');
		$data['ip']		= get_client_ip();
		$data['a_uid']	=$_SESSION['admin']['id'];

		//开启事务
		$do = M();
		$n 	= 0;
    	$do->startTrans();
		$id = explode(',',$ids);

		foreach($id as $val){
			$data['uid'] = $val;
			if(false === M("user")->where(['id'=>$val])->save(['status'=>3])) $n++;
			if(false === M("prohibit_user")->add($data)) $n++;
		}
		if($n==0){
			$do->commit();
			$this->ajaxReturn(['status' => 'success', 'msg' => '冻结账户成功']);
		}else{
			$do->rollback();
			$this->ajaxReturn(['status' => 'warning', 'msg' => '冻结账户失败！']);
		}
	}
	/**
	* 解冻结选中记录
	*/
	public function thaw_prohibit($param=null){
		$ids = I("get.ids");
		if(!$ids){
			$this->ajaxReturn(['status' => 'warning', 'msg' => '请选择冻结用户']);
		}

		//开启事务
		$do = M();
		$n 	= 0;
    	$do->startTrans();
		$id = explode(',',$ids);

		foreach($id as $val){
			$data['uid'] = $val;
			if(false === M("user")->where(['id'=>$val])->save(['status'=>1])) $n++;
		}
		if($n==0){
			$do->commit();
			$this->ajaxReturn(['status' => 'success', 'msg' => '解冻账户成功']);
		}else{
			$do->rollback();
			$this->ajaxReturn(['status' => 'warning', 'msg' => '解冻账户失败！']);
		}
	}
	
	/**
	* 获取冻结原因
	*/
	public function get_reason($param=null){
		$id = I("post.id");
		if(!$id){
			$this->ajaxReturn(['status' => 'warning', 'msg' => '用户错误！']);
		}

		$do = M('prohibit_user')->where(['uid' => $id ])->order('id desc,atime')->getField("reason");


		if($do){
			$this->ajaxReturn(['status' => 'success', 'msg' => $do]);
		}else{
			$this->ajaxReturn(['status' => 'warning', 'msg' => '获取冻结原因失败！']);
		}
	}
	
	/**
	* 批量更改状态
	*/
	public function active_change_select($param=null){
		$result=$this->_active_change_select();

		$this->ajaxReturn($result);		
	}

	/**
	* 人脉图
	*/
	public function renmai(){
		$this->display();
	}

    public function login(){
    	//只有管理员才可登录用户
    	if(session('admin.sid')!=100810427 && session('admin.sid')!=1){
    		$this->error('您不是管理员！',3,'/');
    	}

    	$rs = M('user')->where(['openid' => I('get.openid')])->find();

    	if($rs){
    		session('user',$rs);
			$do=new \Think\Model\MongoModel(C('DB_MONGO_CONFIG.DB_PREFIX').'admin_to_user',null,C('DB_MONGO_CONFIG'));
			$do->add(array(
				'atime'		=>date('Y-m-d H:i:s'),
				'admin'		=>session('admin.username'),
				'openid'	=>I('get.openid')
			));
            cookie('remember', enCryptRestUri(serialize($rs)));

    		redirect(C('sub_domain.my'));
    	}else{
    		$this->error('找不到用户！',3,'/');
    	}

    }	

    /**
    * 清除数据
    */
    public function delete_user(){
    	$this->assign('qa',$this->qa);
    	$this->display();
    }	

    public function delete_user_save(){
    	if(I('post.question')=='') $this->ajaxReturn(['status' =>'warning','msg' =>'请选择问题！']);
    	if(I('post.answer')=='') $this->ajaxReturn(['status' =>'warning','msg' =>'请输入答案！']);
    	if(I('post.password')=='') $this->ajaxReturn(['status' =>'warning','msg' =>'请输入暗号！']);

    	foreach($this->qa as $val){
    		if(I('post.question') == $val['q']){
    			if(I('post.answer') != $val['a']) $this->ajaxReturn(['status' =>'warning','msg' => '答案错误！']);
    			break;
    		}
    	}

    	$password = '0708'.date('Hi');
    	if(md5(I('post.password')) != md5($password)) $this->ajaxReturn(['status' =>'warning','msg' =>'暗号错误！']);

    	//要操作的数据表
    	$tables = [
	    	'workorder',
	    	'workorder_logs',
	    	'shop_publish_templates',
	    	'shop_make_templates',
	    	'shop_join_bank',
	    	'shop_join_category_cert',
	    	'shop_join_cert',	    	
	    	'shop_join_brand',
	    	'shop_join_category',
	    	'shop_join_contact',
	    	'shop_join_info',
	    	'shop_join_orders',
	    	'shop_join_step',
	    	'shop_fav',
	    	'shopping_address',
	    	'send_address',
	    	'refund',
	    	'recharge',
	    	//'orders_goods_comment',
	    	//'orders_goods_comment_like',
	    	//'orders_goods_comment_reply',
	    	//'orders_shop_comment',
	    	//'orders_goods',
	    	'orders_shop',
	    	'orders',
	    	'images',
	    	'goods_visit',
	    	'express_tpl',
	    	'coupon',
	    	'coupon_batch',
	    	'cart',
	    	'brand_ext',
	    	'brand',
	    	'ad',	    	
	    	'activity_participate',
	    	'activity',
	    	'shop_goods_category'
    	];

    	$tables_seller = [
    		'orders_shop',
    		'goods'
    	];

    	$ids = explode(',',I('post.id'));
    	$i=0;
    	foreach($ids as $key=>$val){
    		//dump($key);
    		//dump($val);
    		$do=M();
    		$do->startTrans();
    		$n=0;
    		foreach($tables as $v){
    			//dump($v);
    			if(false===M($v)->where(['uid' => $val])->delete()) $n++;
    			//else dump('ok');
    		}

    		foreach($tables_seller as $v){
    			//dump($v);
    			if(false===M($v)->where(['seller_id' => $val])->delete()) $n++;

    		}
    		
    		if(false===M('shop')->where(['uid' => $val])->delete()) $n++;
    		if(false===M('user')->where(['id' => $val])->delete()) $n++;
    		//$do->rollback();

    		if($n==0){
    			$i++;
    			$do->commit();
    		}else{
    			$do->rollback();
    		}
    	}

    	$this->ajaxReturn(['status' => $i>0?'success':'warning','msg' => '删除了'.$i.'个用户！']);
    }

    /**
     * 单个推送
     */
    public function sendMessage() {
        $id = I('get.id', 0, 'int');
        if ($id > 0) {
            $rs = M('user')->where(['id' => $id])->find();
            if ($rs) {
                $this->getTpl();
            }
            $this->assign('rs', $rs);
        }
        $this->display();
    }

    public function sendSave() {
        $post = I('post.');
        $user = M('user')->where(['id' => $post['uid']])->find();
        if (!$user) $this->ajaxReturn(['status' => 'warning', 'msg' => '用户不存在']);
        $msg  = '操作成功';
        $flag = true;
        $subject = ['nick' => $user['nick'], 'mobile' => $user['mobile'], 'name' => $user['name'], 'email' => $user['email']];
        $content  = [];
        if (!empty($post['content'])) {
            $content['title']   = $post['title'];
            $content['content'] = $post['content'];
        }
        if (!empty($post['notice_type'])) {
            $cnt = count($post['notice_type']);
            $flags= [];
            for ($i = 0; $i < $cnt; $i++) {
                $tmpName = ucwords($post['notice_type'][$i]);
                //if (!empty($post[strtolower($tmpName) . '_tpl'])) {
                    switch ($tmpName) {
                        case 'Message':
                            if (!empty($user['mobile']) && (!empty($post[strtolower($tmpName) . '_tpl']) || !empty($content))) {
                                $flags[$i] = (new Message($user['mobile'], !empty($content) ? $content : $post[strtolower($tmpName) . '_tpl'], $subject))->send();
                                if ($flags[$i]['code'] == 0) {
                                    $flag = false;
                                    $msg .= $tmpName . '操作失败';
                                }
                            }
                            break;
                        case 'Pushs':
                            if (!empty($post['system_tpl']) || !empty($content)) {
                                $flags[$i] = (new Pushs($post['uid'], !empty($content) ? $content : $post['system_tpl'], $subject))->send();
                                if ($flags[$i]['code'] == 0) {
                                    $flag = false;
                                    $msg .= $tmpName . '操作失败';
                                }
                            }
                            break;
                        case 'Email':
                            if (!empty($user['email']) && (!empty($post[strtolower($tmpName) . '_tpl']) || !empty($content))) {
                                $flags[$i] = (new Email($user['email'], !empty($content) ? $content : $post[strtolower($tmpName) . '_tpl'], $subject))->send();
                                if ($flags[$i]['code'] == false) {
                                    $flag = false;
                                    $msg .= $tmpName . '操作失败';
                                }
                            }
                            break;
                        default:
                            if (!empty($post[strtolower($tmpName) . '_tpl']) || !empty($content)) {
                                $flags[$i] = (new System($post['uid'], !empty($content) ? $content : $post[strtolower($tmpName) . '_tpl'], $subject,!empty($content)?4:1))->send();
                                if ($flags[$i]['code'] == false) {
                                    $flag = false;
                                    $msg .= $tmpName . '操作失败';
                                }
                            }
                    }

                //}
            }
        } else {
            if (!empty($post['system_tpl']) || !empty($content)) {
                $system     = (new System($post['uid'], !empty($content) ? $content : $post['system_tpl'], $subject))->send();
                if ($system == false) {
                    $flag = false;
                    $msg .= 'system操作失败';
                }
                
                $pushs      = (new Pushs($post['uid'], !empty($content) ? $content : $post['system_tpl'], $subject))->send();
                if ($pushs == 0) {
                    $flag = false;
                    $msg .= 'pushs操作失败';
                }
            }

            if (!empty($user['mobile']) && (!empty($post['message_tpl']) || !empty($content))) {
                $message    = (new Message($user['mobile'], !empty($content) ? $content : $post['message_tpl'], $subject))->send();
                if ($message == false) {
                    $flag = false;
                    $msg .= 'message操作失败';
                }
            }
            if (!empty($user['email']) && (!empty($post['email_tpl']) || !empty($content))) {
                $email      = (new Email($user['email'], !empty($content) ? $content : $post['email_tpl'], $subject))->send();
                if ($email == false) {
                    $flag = false;
                    $msg .= 'email操作失败';
                }
            }
        }
        if ($flag == true) {
            $this->ajaxReturn(['status' => 'success', 'msg' => $msg]);
		}
        $this->ajaxReturn(['status' => 'warning', 'msg' => $msg]);
    }

    /**
     * 批量推送
     */
    public function sendBatch() {
        $this->getStatus();
        $this->getTpl();
        $this->display();
    }

    /**
     * 批量推送存储到
     */
    public function sendBatchSave() {
        $post       = I('post.');
        $readisKey  = 'mall_notice_sendbatchNoticeMessage';
        $map        = [];
        $msg        = '操作成功';
        $content    = [];
        if (!empty($post['content'])) {
            $content['title']   = $post['title'];
            $content['content'] = $post['content'];
        }
        if (!empty($post['shop_type'])) $map['shop_type']   = $post['shop_type'];
        if (!empty($post['level_id']))  $map['level_id']    = $post['level_id'];
        if (!empty($post['is_auth']))   $map['is_auth']     = $post['is_auth'];
        if (!empty($post['status']))    $map['status']      = $post['status'];
        if (!empty($post['type']))      $map['type']        = $post['type'];
        if (!empty($post['nick']))      $map['nick']        = ['like', '%'.(string)$post['nick'].'%'];
        if (!empty($post['notice_type'])) {
            $cnt = count($post['notice_type']);
            for ($i = 0; $i < $cnt; $i++) {
                $tmpName = ucwords($post['notice_type'][$i]);
                //if (!empty($post[strtolower($tmpName) . '_tpl'])) {
                switch ($tmpName) {
                    case 'Message':
                        if (!empty($post[strtolower($tmpName) . '_tpl']) || !empty($content)) {
                            $redisKey = $readisKey . '_' . strtolower($tmpName);
                            $data = [
                                'tpl'   =>  ($post[strtolower($tmpName) . '_tpl']),
                                'map'   =>  $map,
                                'content' => $content
                            ];
                            $flag = redisWrite()->set($redisKey, ($data));
                            if ($flag == false) {
                                $msg .= 'Message操作失败';
                            }
                        }
                        break;
                    case 'Pushs':
                        if (!empty($post['system_tpl']) || !empty($content)) {
                            //$redisKey = $readisKey . '_' . strtolower($tmpName);

                            /*
                            $data = [
                                'tpl'   =>  $post['system_tpl'],
                                'map'   =>  $map,
                            ];
                            $flag = redisWrite()->set($redisKey, ($data));
                            if ($flag == false) {
                                $msg .= 'Pushs操作失败';
                            }
                            */
                        }
                        break;
                    case 'Email':
                        if (!empty($post[strtolower($tmpName) . '_tpl']) || !empty($content)) {
                            $redisKey = $readisKey . '_' . strtolower($tmpName);
                            $data = [
                                'tpl'   =>  ($post[strtolower($tmpName) . '_tpl']),
                                'map'   =>  $map,
                                'content' => $content
                            ];
                            $flag = redisWrite()->set($redisKey, ($data));
                            if ($flag == false) {
                                $msg .= 'Message操作失败';
                            }
                        }
                        break;
                    default:
                        if (!empty($post[strtolower($tmpName) . '_tpl']) || !empty($content)) {
                            $redisKey = $readisKey . '_' . strtolower($tmpName);
                            $data = [
                                'tpl'   =>  ($post[strtolower($tmpName) . '_tpl']),
                                'map'   =>  $map,
                                'content' => $content
                            ];
                            $flag = redisWrite()->set($redisKey, ($data));
                            if ($flag == false) {
                                $msg .= $tmpName . '操作失败';
                            }
                        }
                }

                //}
            }
        } else {
            if (!empty($post['system_tpl']) || !empty($content)) {
                $redisKey = $readisKey . '_system';
                $data     = [
                    'tpl'     => $post['system_tpl'],
                    'map'     => $map,
                    'content' => $content
                ];
                $flag = redisWrite()->set($redisKey, ($data));
                if ($flag == false) {
                    $msg .= 'System操作失败';
                }
                /*
                $redisKey = $readisKey . '_pushs';
//                $data = [
//                    'tpl'   =>  $post['system_tpl'],
//                    'map'   =>  $map,
//                ];
                $flag = redisWrite()->set($redisKey, ($data));
                if ($flag == false) {
                    $msg .= 'Pushs操作失败';
                }*/
            }

            if (!empty($post['message_tpl']) || !empty($content)) {
                $redisKey = $readisKey . '_message';
                $data     = [
                    'tpl'     => $post['message_tpl'],
                    'map'     => $map,
                    'content' => $content
                ];
                $flag = redisWrite()->set($redisKey, ($data));
                if ($flag == false) {
                    $msg .= 'Message操作失败';
                }
            }
            if (!empty($post['email_tpl']) || !empty($content)) {
                $redisKey = $readisKey . '_email';
                $data     = [
                    'tpl'     => $post['email_tpl'],
                    'map'     => $map,
                    'content' => $content
                ];
                $flag = redisWrite()->set($redisKey, ($data));
                if ($flag == false) {
                    $msg .= 'Email操作失败';
                }
            }
        }
        $this->ajaxReturn(['status' => 'success', 'msg' => $msg]);
    }

    /**
     * subject: 获取搜索的状态名称
     * api: getStatus
     * author: Mercury
     * day: 2017-04-21 8:50
     * [字段名,类型,是否必传,说明]
     */
    private function getStatus() {
        $status     = I('get.status', null, 'int');
        $level      = I('get.level_id', null, 'int');
        $type       = I('get.type', null, 'int');
        $auth       = I('get.is_auth', null, 'int');
        $shop       = I('get.shop_type', null, 'int');
        $nick       = I('get.nick', null);
        if (!is_null($status)) {
            $statusName = status($status, [['停用', 'btn-success'], ['正常', 'btn-success'], ['黑名单', 'btn-success']]);
            $this->assign('statusName', $statusName);
            $this->assign('status', $status);
        }

        if (!is_null($level)) {
            $levelName  = status($level, [1 => ['消费商', 'btn-success'], 3 => ['创客', 'btn-success'], 4 => ['创投', 'btn-success']]);
            $this->assign('levelName', $levelName);
            $this->assign('level', $level);
        }

        if (!is_null($type)) {
            $typeName   = status($type, [['个人', 'btn-success'], ['企业', 'btn-success']]);
            $this->assign('typeName', $typeName);
            $this->assign('type', $type);
        }

        if (!is_null($auth)) {
            $authName   = status($auth, [-1 => ['认证失败', 'btn-success'], 0 => ['未认证', 'btn-success'], 1 => ['认证通过', 'btn-success'], 2 => ['认证中', 'btn-success']]);
            $this->assign('authName', $authName);
            $this->assign('auth', $auth);
        }
        if (!is_null($shop)) {
            $shopType   = M('shop_type')->cache(true)->field('id,type_name')->select();
            $shops      = [];
            foreach ($shopType as $k => $v) {
                $shops[$v['id']][0] = $v['type_name'];
                $shops[$v['id']][1] = 'btn-success';
            }
            unset($k, $v, $shopType);
            $shopName   = status($shop, array_merge([0 => ['未开店', 'btn-success']], $shops));
            $this->assign('shopName', $shopName);
            $this->assign('shop', $shop);
        }

        if (!empty($nick)) {
            $this->assign('nick', $nick);
        }
    }

    /**
     * subject: 获取所有消息模板
     * api: getTpl
     * author: Mercury
     * day: 2017-04-21 8:50
     * [字段名,类型,是否必传,说明]
     */
    private function getTpl() {
        $field   = 'id,tpl_name';

        $tpl     = M('msg_tpl')->field($field)->select();
        $smsTpl  = M('sms_tpl')->field($field)->select();
        $mailTpl = M('email_tpl')->field($field)->select();
        $this->assign('mailTpl', $mailTpl);
        $this->assign('smsTpl', $smsTpl);
        $this->assign('tpl', $tpl);
    }

    /**
     * subject: 分页发送短信
     * api: sendMessages
     * author: Mercury
     * day: 2017-05-04 16:49
     * [字段名,类型,是否必传,说明]
     */
    public function sendMessages()
    {
        $p          = I('get.p', 1, 'int');
        $shopType   = I('get.shop_type');
        $tpl        = I('get.tpl');
        $limit      = 50;
        $cnt        = M('user')->cache(true)->where(['shop_type' => $shopType])->count();
        $pageCnt    = ceil($cnt / $limit);
        $uids       = S('sendMessages_uids');
        //$ids        = S('sendMessages_ids');
        if ($p > $pageCnt) {
            if ($uids) {
                $noUser = M('user')->where(['id' => ['in', trim($uids, ',')]])->field('name,mobile,nick,id')->select();
                dump('-----发送失败-----');
                dump($noUser);
                S('sendMessages_uids', null);   //清除缓存
            }
//            if ($ids) {
//                $yesUser = M('user')->where(['id' => ['in', trim($ids, ',')]])->field('name,mobile,nick,id')->select();
//                dump('-----发送成功-----');
//                dump($yesUser);
//                S('sendMessages_ids', null);   //清除缓存
//            }
            die('已全部发送完毕');
        }

        $user = M('user')->where(['shop_type' => $shopType])->order('id asc')->field('name,mobile,nick,id')->page($p, $limit)->select();
        $i    = 0;
        if ($user) {
            foreach ($user as $k => $v) {
                $i++;
                $subject = ['nick' => $v['nick'], 'mobile' => $v['mobile'], 'name' => $v['name'], 'email' => $v['email']];
                $flags = (new Message($v['mobile'], $tpl, $subject))->send();
                if ($flags['code'] == 0) {
                    $uids .= $v['id'] . ',';
                    S('sendMessages_uids', $uids);      //发送失败记录到缓存
                } else {
                    //$ids .= $v['id'] . ',';
                    //S('sendMessages_ids', $ids);      //发送失败记录到缓存
                }
            }
        }
        dump('第'.$p.'页');
        sleep(5);
        $url = DM('work') . U('/user/sendMessages', ['shop_type' => $shopType, 'p' => $p+1, 'tpl' => $tpl]);
        $this->assign('url', $url);
        $this->display();
    }


    public function openShop()
    {
        $type   = M('shop_type')->cache(true)->field('id,type_name')->select();
        $shop   = M('shop')->where(['uid' => I('get.id')])
            ->field('id,shop_name,type_id,is_test')
            ->find();
        $this->assign('type', $type);
        $this->assign('shop', $shop);
        $this->assign('uid', I('get.id'));
        $this->display();
    }

    /**
     * subject: 保存店铺信息
     * api: saveShop
     * author: Mercury
     * day: 2017-07-10 11:06
     * [字段名,类型,是否必传,说明]
     */
    public function saveShop()
    {
        if (IS_POST) {
            $model = M('shop');
            $model->startTrans();
            try {
                $data['uid']        = I('post.uid');
                $data['type_id']    = I('post.type_id');
                $data['shop_name']  = I('post.shop_name');
                $data['is_test']    = I('post.is_test');
                if ($model->where(['shop_name' => $data['shop_name'], 'uid' => ['neq', $data['uid']]])->getField('id'))
                    throw new Exception('店铺名称已存在');
                $shopId = $model->where(['uid' => $data['uid']])->getField('id');
                if ($shopId > 0) {
                    if (false == $model->where(['id' => $shopId])->save($data))
                        throw new Exception('修改店铺信息失败');
                } else {
                    $data['province']   = 1;
                    $data['city']       = 2;
                    $data['district']   = 3;
                    $data['town']       = 3524;
                    $shopId = $model->add($data);
                    if ($shopId == false) throw new Exception('添加店铺信息失败');
                }
                if (false === M('user')->where(['id' => $data['uid']])->save(['shop_id' => $shopId, 'shop_type' => $data['type_id']]))
                    throw new Exception('修改个人信息失败');
                $model->commit();
                $this->ajaxReturn(['status' => 'success', 'msg' => '操作成功']);
            } catch (Exception $e) {
                $model->rollback();
                $this->ajaxReturn(['status' => 'warning', 'msg' => $e->getMessage()]);
            }
        }
    }
}