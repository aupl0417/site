<?php
namespace Rest2\Controller;


/**
 * 拼团购
 * @author Lzy
 * @date 2017-04-06
 */

class TuanController extends CommonController
{

	/**
	 * subject:活动商品列表
	 * api: /Tuan/activity_goods_list
	 * param: category_id,int,0,分类
	 * param: goods_name,string,0,商品名
	 * param: begin,string,0,起始时间
	 * param: end,string,0,结束时间
	 * param: pagesize,int,0,每页数目，默认10
	 * param: p,int,0,当前页，默认1
	 */
	public function activity_goods_list(){
		$this->check('');

		$res = $this->_activity_goods_list($this->post);

        $this->apiReturn($res);
	}

	public function _activity_goods_list($param){
		$map = array(
			'begin' 	=> $param['begin'] 	? ['elt', date('Y-m-d H:i:s', strtotime($param['begin']))] 	: ['elt', date('Y-m-d H:i:s')],
			'end'	 	=> $param['end'] 	? ['egt', date('Y-m-d H:i:s', strtotime($param['end']))] 	: ['egt', date('Y-m-d H:i:s')],
			'status'	=> 1,
		);
		# 分类搜索
		if(isset($param['category_id']) && $param['category_id']){
			$cid 				= $param['category_id'];
			$category 			= M('tuan_category')->cache(TRUE, 900)->field('id,sid,category_name')->select();
			$tree 				= childArray($category, ['now' => $cid,'pid' => 'sid']);
			$map['category_id'] = ['in', array_merge([$cid], $tree)];
		}
		# 商品名搜索
		if(isset($param['goods_name']) && $param['goods_name']){
			$map['goods_name'] = ['like','%' . (string) $param['goods_name'] . '%'];
		}

		$pagelist = pagelist(array(
            'table'     => 'Common/TuanApplyRelation',
            'map'       => $map,
            'do'		=> 'D',
            'relation'	=> true,
            'fields'    => '*',
            'pagesize'  => $param['pagesize'] ? $param['pagesize'] : 10,
            'p'         => $param['p'] ? $param['p'] : 1,
        ));
        if($pagelist['list']){
        	foreach ($pagelist['list'] as $key => $value) {
        		$pagelist['list'][$key]['sale_num'] 	= $this->sale_goods_num($value['goods_id']);
        		$pagelist['list'][$key]['count_num'] 	= $this->count_goods_num($value['goods_id']);
        	}
        	unset($pagelist['sql']);
        	return ['code' => 1, 'data' => $pagelist];
        }else{
        	return ['code' => 3];
        }
	}

	/**
	 * subject: 随机推荐3条活动商品
	 * api: /Tuan/love
	 * param: pagesize,int,0,推荐数目，默认3
	 */
	public function love(){
		$this->check('');

		$res = $this->_love($this->post);

		$this->apiReturn($res);
		
	}

	public function _love($post){
		$map = array(
			'begin' 	=> ['elt', date('Y-m-d H:i:s')],
			'end'	 	=> ['egt', date('Y-m-d H:i:s')],
			'status'	=> 1,
		);
		$list = D('Common/TuanApplyRelation')->relation(true)->order('rand()')->limit($post['pagesize'] ? $post['pagesize'] : 3)->where($map)->select();
		if($list){
			return ['code' => 1, 'data' => $list];
		}else{
			return ['code' => 0];
		}
	}


	/**
	 * subject: 拼团购 - 商品详情
	 * api: /Tuan/tuan_goods_info
	 * author: Lzy
	 * date: 2017-04-06
	 * param: id,int,1,申请id
	 */
	public function tuan_goods_info(){
		$this->check('id');

		$res = $this->_tuan_goods_info($this->post);

		$this->apiReturn($res);
	}

	public function _tuan_goods_info($param){

		$where = array(
			'id'		=> $param['id'],
			'status'	=> 1,
			'begin'		=> ['elt', date('Y-m-d H:i:s')],
			'end'		=> ['egt', date('Y-m-d H:i:s')],
		);
		$one = D('Common/TuanApplyRelation')->relation(TRUE)->where($where)->find();
		if($one){
			$map = array(
				'tuan_apply_id' => $one['id'],
			);
			$one['sale_num'] = 0;
			$list = D('Common/TuanApplyListRelation')->relation(TRUE)->where($map)->select();
			foreach ($list as $key => $value) {
				$saleNum = $this->sale_goods_attr_num($value['goods_attr_list_id']);
				$list[$key]['sale_num'] = $saleNum;
				$one['sale_num'] += $saleNum;
			}
			$one['tuan_apply_list'] = $list;
			
			return ['code' => 1, 'data' => $one];
		}else{
			return ['code' => 3];
		}
	}

	/**
	 * subject: 拼团购 - 等待成团列表
	 * api: /Tuan/_wait_tuan
	 * author: Lzy
	 * date: 2017-04-06
	 * param: id,int,1,申请id
	 * param: pagesize,int,0,每页记录数
	 * param: p,int,0,当前页数
	 */
	public function wait_tuan(){
		$this->check('id');

		$res = $this->_wait_tuan($this->post);

		$this->apiReturn($res);
	}

	public function _wait_tuan($param){
		$where = array(
			'id'		=> $param['id'],
			'status'	=> 1,
			'begin'		=> ['elt', date('Y-m-d H:i:s')],
			'end'		=> ['egt', date('Y-m-d H:i:s')],
		);
		$one = D('Common/TuanApplyRelation')->relation(true)->where($where)->find();
		if(empty($one['id'])){
			return ['code' => 3];
		}
		
		$map['tuan_apply_id'] = $one['id'];
		$map['status'] = 0;
		$map['end'] = ['egt', date('Y-m-d H:i:s')];
		$pagelist = pagelist(array(
			'table'     	=> 'Common/TuanStartRelation',
            'map'       	=> $map,
            'do'			=> 'D',
            'relation'		=> true,
            'fields'    	=> '*',
            'relationWhere' => ['tuan_join','status' => 0],
            'pagesize'  	=> $param['pagesize'] ? $param['pagesize'] : 10,
            'p'         	=> $param['p'] ? $param['p'] : 1,
		));

		if($pagelist['list']){
			foreach ($pagelist['list'] as $key => $value) {
				$pagelist['list'][$key]['wait_number'] = $value['number'] - count($value['tuan_join']);
			}
			unset($pagelist['sql']);
			return ['code' => 1, 'data' => $pagelist['list']];
		}else{
			return ['code' => 3];
		}
	}


	/**
	 * subject: 我的拼团购列表
	 * api: /Tuan/my_tuan
	 * author: Lzy
	 * date: 2017-04-06
	 * param: openid,string,1,用户openid
	 * param: pagesize,int,0,每页记录数
	 * param: p,int,0,当前页数
	 * param: status,int,0,状态开团状态 0拼团中 1拼团成功 2拼团失败 3取消拼团 
	 */
	public function my_tuan(){
		$this->check('openid');
		$res = $this->_my_tuan($this->post);
		$this->apiReturn($res);
	}

	public function _my_tuan($param){
		
		$map['uid'] = $this->user['id'];
		if(isset($param['status'])){
			$map['status'] = $param['status'];
		}
		$pagelist = pagelist(array(
			'table'     => 'Common/TuanJoinRelation',
            'map'       => $map,
            'do'		=> 'D',
            'relation'	=> true,
            'fields'    => '*',
            'pagesize'  => $param['pagesize'] ? $param['pagesize'] : 10,
            'p'         => $param['p'] ? $param['p'] : 1,
		));

		if($pagelist['list']){
			foreach ($pagelist['list'] as $key => $value) {
				$pagelist['list'][$key]['goods'] = M('goods')->field('id,goods_name,images,score_ratio,shop_id')->find($value['goods_attr_list']['goods_id']);
				$pagelist['list'][$key]['shop'] = M('shop')->field('id,shop_name,shop_logo')->find($pagelist['list'][$key]['goods']['shop_id']);
				# 是否是团长
				if($value['tuan_start']['uid'] == $this->user['id']){
					$pagelist['list'][$key]['is_colonel'] = 1;
				}
				if($value['status'] == 0){
					$pagelist['list'][$key]['wait_number'] = $value['tuan_start']['number'] - (int) M('tuan_join')->where(['tuan_start_id' => $value['tuan_start']['id'],'status' => 0])->count();	
				}
				$pagelist['list'][$key]['score'] = $value['num'] * 100 * $value['price'] * $pagelist['list'][$key]['goods']['score_ratio'];
				$pagelist['list'][$key]['status_name'] = ['拼团中','拼团成功','拼团失败','取消拼团'][$value['status']];
			}
			unset($pagelist['sql']);
			return ['code' => 1, 'data' => $pagelist];
		}else{
			return ['code' => 3];
		}

	}

	/**
	 * subject: 拼团购详情
	 * api: /Tuan/tuan_info
	 * author: Lzy
	 * date: 2017-04-16
	 * param: tuan_start_id,int,1,开团id
	 */
	public function tuan_info(){
		$this->check('tuan_start_id');
		$res = $this->_tuan_info($this->post);
		$this->apiReturn($res);
	}

	public function _tuan_info($param){
		$startId = $param['tuan_start_id'];
		$start = M('tuan_start')->where(['id' => $startId])->find();
		if($start){
			$list = D('Common/TuanJoinRelation')->relation(TRUE)->where(['tuan_start_id' => $startId])->select();
			foreach ($list as $key => $value) {
				if($value['uid'] == $start['uid']){
					$list[$key]['is_colonel'] = 1;
				}
				$start['join_user'][] = $value['uid'];
				$list[$key]['sale_num'] = $this->sale_goods_attr_num($value['goods_attr_list_id']);
			}
			
			if($start['status'] == 0){
				$start['wait_number'] = $start['number'] - count($list);
			}
			$start['goods'] = M('goods')->field('id,goods_name,images,score_ratio')->find($start['goods_id']);
			$start['tuan_join'] = $list;
			$start['status_name'] = ['等待成团','拼团成功','拼团失败'][$start['status']];
			$start['sale_num'] = $this->sale_goods_num($start['goods_id']);
			return ['code' => 1, 'data' => $start];
		}else{
			return ['code' => 3];
		}
	}

	/**
	 * subject: 拼团购类目列表
	 * api: /Tuan/category
	 * author: Lzy
	 * date: 2017-04-06
	 * param: sid,int,0,上级id
	 */
	public function category(){
		$this->check('');
		$res = $this->_category($this->post);
		$this->apiReturn($res);
	}

	public function _category($param){
		$map['status'] = 1;
		$sid = 0;
		if(isset($param['sid'])){
			$map['sid'] = $param['sid'];
			$sid = $param['sid'];
		}
		$list = M('tuan_category')->field('atime,etime',true)->cache(TRUE, 900)->where($map)->select();
		if($list){
			return ['code' => 1, 'data' => arrayTrees($list, $sid,'id','sid')];
		}else{
			return ['code' => 3];
		}
	}

	/**
	 * subject: 参团支付
	 * api: /Tuan/join_pay
	 * author: Lzy
	 * date: 2017-04-15
	 * param: openid,string,1,用户openid
	 * param: pay_type,int,1,支付类型
	 * param: pay_password,string,1,支付密码
	 * param: tuan_join_id,int,1,参团id
	 */
	public function join_pay(){
		$this->check('openid,pay_type,pay_password,tuan_join_id');
		$res = $this->_join_pay($this->post);
		$this->apiReturn($res);
	}

	public function _join_pay($post){
		$model = M();
		$model->startTrans();

		$join = M('tuan_join')->find($post['tuan_join_id']);
		$start = M('tuan_start')->find($join['tuan_start_id']);
		if(empty($join) || $join['uid'] != $this->user['id']){
			return ['code' => 0, 'msg' => '拼团订单不存在'];
		}
		if($join['is_pay'] == 1){
			return ['code' => 0, 'msg' => '拼团订单已支付'];
		}
		if($join['status'] != 1 && $join['status'] != 0){
			return ['code' => 0, 'msg' => '当前状态不能付款'];
		}
		if($start['end'] <= date('Y-m-d H:i:s')){
			return ['code' => 0, 'msg' => '拼团已过期，不能付款'];	
		}
		$pay['uid']				= $this->user['id'];
		$pay['pay_type'] 		= $post['pay_type'];
		$pay['pay_price'] 		= $join['num'] * $join['price'];
		$pay['tuan_join']		= $join['id'];
		$pay['tuan_join_id']	= $post['tuan_join_id'];
		# erp支付生成流水号
		$pay['pay_no'] = 123456789;
		$pay['encrypt'] = md5(http_build_query($pay));
		
		if(FALSE == M('tuan_join_pay')->data($pay)->add()){
			$model->rollback();
			return ['code' => 0];
		}
		$save1 =  M('tuan_join')->where(['id' => $join['id']])->data(['is_pay' => 1])->save();
		if(FALSE == $save1){
			$model->rollback();
			return ['code' => 0];
		}
		$model->commit();
		return ['code' => 1];
	}

	/**
	 * 检测是否全部支付,并更改状态
	 * api: /Tuan/check_all_pay
	 * author: Lzy
	 * date: 2017-04-19
	 */
	public function check_all_pay(){
		$this->check('');
		$res = $this->_check_all_pay($this->post, $this->user); 
		$this->apiReturn($res);
	}

	public function _check_all_pay($post){
		$model = M();
		$model->startTrans();
		$sIds = [];
		$where = array(
			'all_pay' 	=> 0,
			'end' 		=> ['egt', date('Y-m-d H:i:s')],
			'status' 	=> 1,
		);
		$relationWhere = array(
			'is_pay' => 1,
			'status' => 1,
		);
		$start = D('Common/TuanStartRelation')->field('id')->relationWhere('tuan_join', $relationWhere)->relation(true)->where($where)->select();
		foreach($start as $value){
			if($start['number'] == count($value['tuan_join'])){
				$sIds[] = $value['id'];
			}
		}
		if($sIds){
			if(M('tuan_start')->where(['id' => ['in',$sIds]])->data(['all_pay' => 1])->save() == false){
				$model->rollback();
				return ['code' => 0];
			}
		}
		$model->commit();
		return ['code' => 1];
	}

	/**
	 * subject: 发起拼团购
	 * api: /Tuan/tuan_start
	 * author: Lzy
	 * date: 2017-04-07
	 * param: tuan_apply_list_id,int,1,申请list id
	 * param: openid,string,1,买家openid
	 * param: num,int,1,购买数量
	 * param: address_id,int,1,收货地址
	 */
	public function tuan_start(){
		$this->check('tuan_apply_list_id,openid,num,address_id');
		$res = $this->_tuan_start($this->post, $this->user);
		$this->apiReturn($res);
	}

	public function _tuan_start($post, $user){
		$model = M();
		$model->startTrans();

		$applyList = M('tuan_apply_list')->find($post['tuan_apply_list_id']);
		if(empty($applyList)) return ['code' => 0];

		$apply = M('tuan_apply')->find($applyList['tuan_apply_id']);
		if(empty($apply)) return ['code' => 0];
		if($apply['status'] != 1 || $apply['begin'] > date('Y-m-d H:i:s') || $apply['end'] < date('Y-m-d H:i:s')){
			return ['code' => 0,'msg' => '商品不在拼团购活动中'];
		}
		if($this->check_user_have_tuan($applyList['goods_attr_list_id'], $user['id'])){
			return ['code' => 0,'msg' => '你已参加过此商品拼团，请勿重复申请'];
		}
		if($post['num'] >= $this->check_goods_attr_num($applyList['id'])){
			return ['code' => 0, 'msg' => '商品库存不足，无法开团'];
		}
		$end = date('Y-m-d H:i:s',time() + 24 * 3600);
		$end <= $apply['end'] or $end = $apply['end'];
		$ts_no = $this->create_orderno('TG', $user['id']);
		$start = array(
			'uid' 			=> $user['id'],
			'goods_id' 		=> $apply['goods_id'],
			'ip'			=> get_client_ip(),
			'status'		=> 0,
			'number'		=> $apply['number'],
			'end'			=> $end,
			'tuan_apply_id'	=> $apply['id'],
			'ts_no'			=> $ts_no,
			'is_order'		=> 0,
			'all_pay'		=> 0,
		);
		$startId = M('tuan_start')->data($start)->add();
		$join = array(
			'uid' 					=> $user['id'],
			'tuan_start_id'			=> $startId,
			'goods_attr_list_id'	=> $applyList['goods_attr_list_id'],
			'ip'					=> get_client_ip(),
			'status'				=> 0,
			'is_pay'				=> 0,
			'price'					=> $applyList['price'],
			's_no'					=> '',
			'num'					=> $post['num'],
			'ts_no'					=> $ts_no,
			'is_order'				=> 0,
			'end'					=> $end,
			'address_id'			=> $post['address_id'],
		);
		
		$joinId = M('tuan_join')->data($join)->add();

		if(FALSE == $startId || FALSE == $joinId){
			$model->rollback();
			return ['code' => 0];
		}
		$model->commit();
		return ['code' => 1,'data' => ['tuan_join_id' => $joinId]];
	}

	/**
	 * subject: 加入拼团购
	 * api: /Tuan/tuan_join
	 * author: Lzy
	 * date: 2017-04-07
	 * param: tuan_apply_list_id,int,1,商品属性id
	 * param: openid,string,1,用户openid
	 * param: tuan_start_id,int,1,开团id
	 * param: num,int,1,购买数量
	 * param: address_id,int,1,收货地址
	 */
	public function tuan_join(){
		$this->check('tuan_apply_list_id,openid,tuan_start_id,num,address_id');
		$res = $this->_tuan_join($this->post, $this->user);
		$this->apiReturn($res);
	}

	public function _tuan_join($post, $user){
		$model = M();
		$model->startTrans();

		$applyList 	= M('tuan_apply_list')->find($post['tuan_apply_list_id']);
		if(empty($applyList)) return ['code' => 0];
		
		$start = M('tuan_start')->find($post['tuan_start_id']);
		if(empty($start) || $start['status'] != 0 || $start['end'] <= date('Y-m-d H:i:s')) return ['code' => 0, 'msg' => '该团购不存在或者已过期'];

		if($this->check_user_have_tuan($applyList['goods_attr_list_id'], $user['id'])){
			return ['code' => 0,'msg' => '你已参加过此商品拼团，请勿重复申请'];
		}
		if(M('tuan_join')->where(['uid' => $user['id'],'status' => 0,'tuan_start_id' => $start['id']])->getField('id')){
			return ['code' => 0,'msg' => '你正在参与此拼团，请勿重复申请'];
		}
		if($post['num'] > $this->check_goods_attr_num($applyList['id'])){
			return ['code' => 0, 'msg' => '商品库存不足，无法开团'];
		}
		$join = array(
			'uid' 					=> $user['id'],
			'tuan_start_id'			=> $start['id'],
			'goods_attr_list_id'	=> $applyList['goods_attr_list_id'],
			'ip'					=> get_client_ip(),
			'status'				=> 0,
			'is_pay'				=> 0,
			'price'					=> $applyList['price'],
			's_no'					=> '',
			'num'					=> $post['num'],
			'ts_no'					=> $start['ts_no'],
			'tj_no'					=> $this->create_orderno('TG', $user['id']),
			'is_order'				=> 0,
			'end'					=> $start['end'],
			'address_id'			=> $post['address_id'],
		);
		
		$joinId = M('tuan_join')->data($join)->add();
		if(FALSE == $joinId){
			$model->rollback();
			return ['code' => 0];
		}
		# 是否满人
		if($start['number'] == M('tuan_join')->where(['start_id' => $start['id'],'status' => 0])->count()){
			$save1 = M('tuan_start')->data(['status' => 1])->where(['id' => $start['id']])->save();
			$save2 = M('tuan_join')->data(['status' => 1])->where(['start_id' => $start['id'],'status' => 0])->save();
			if(FALSE == $save1 || $save2 != $start['number']){
				$model->rollback();
				return ['code' => 0,'msg' => '参团失败，请稍后重试'];
			}
		}
		$model->commit();
		return ['code' => 1,'data' => ['tuan_join_id' => $joinId]];

	}

	/**
	 * subject: 取消拼团购
	 * api: /Tuan/tuan_cancel
	 * author: Lzy
	 * date: 2017-04-07
	 * param: openid,string,1,用户openid
	 * param: tuan_start_id,int,1,开团id
	 */
	public function tuan_cancel(){
		$this->check('openid,tuan_start_id');
		$res = $this->_tuan_cancel($this->post, $this->user);
		$this->apiReturn($res);
	}

	public function _tuan_cancel($post, $user){
		$model 	= M();
		$model->startTrans();

		$start 	= M('tuan_start')->find($post['tuan_start_id']);
		$join 	= M('tuan_join')->where(['start_id' => $start['id'],'uid' => $user['id']])->find();
		if(empty($start) || empty($join)){
			$model->rollback();
			return ['code' => 0, 'msg' => '拼团不存在'];
		}
		if($start['uid'] == $user['id']){
			$model->rollback();
			return ['code' => 0, 'msg' => '团长不能取消拼团'];
		}
		if($start['status'] != 0 || $join['status'] != 0 || $join['is_pay'] == 1 || $start['end'] <= date('Y-m-d H:i:s')){
			$model->rollback();
			return ['code' => 0, 'msg' => '当前状态不允许取消拼团'];
		}
		$save = M('tuan_join')->where(['start_id' => $start['id'],'uid' => $user['id']])->data(['status' => 3])->save();
		if($save == FALSE){
			$model->rollback();
			return ['code' => 0];
		}
		$model->commit();
		return ['code' => 1];
	}

	/**
	 * 检测 用户是否已团过 库存商品
	 */
	public function check_user_have_tuan($goodsAttrListId,$userId){
		$joinMap = array(
			'goods_attr_list_id'	=> $goodsAttrListId,
			'status' 				=> ['in', [0,1]],
			'uid'					=> $userId,
		);
		$join = M('tuan_join')->where($joinMap)->field('id')->find();
		return isset($join['id']);
	}

	/**
	 * 检测 活动 库存商品 库存
	 */
	public function check_goods_attr_num($tuanApplyListId){
		$applyList 	= M('tuan_apply_list')->find($tuanApplyListId);
		
		# 只检测包含tuan_apply_id的开团表
		$start 		= M('tuan_start')->where(['tuan_apply_id' => $applyList['tuan_apply_id']])->field('id')->select();
		$sIds 		= array_column($start,'id');
		
		$joinMap = array(
			'status' 				=> ['in', [0,1]],
			'goods_attr_list_id' 	=> $applyList['goods_attr_list_id'],
		);
		if($sIds){
			$joinMap['tuan_start_id'] = ['in', $sIds];
		}
		$joinNum = (int) M('tuan_join')->where($joinMap)->sum('num');
		return $applyList['num'] - $join;
	}

	/**
	 * 统计 库存商品 已团多少件
	 */
	public function sale_goods_attr_num($goodsAttrListId){
		$joinMap = array(
			'status'				=> ['in', [0,1]],
			'goods_attr_list_id'	=> $goodsAttrListId,
		);
		return (int) M('tuan_join')->where($joinMap)->sum('num');
	}

	/**
	 * 统计 活动商品 一共有多少库存
	 */
	public function count_goods_num($goodsId){
		$where =  array(
			'goods_id' 	=> $goodsId,
			'status'	=> 1,
			'begin'		=> ['elt', date('Y-m-d H:i:s')],
			'end'		=> ['egt', date('Y-m-d H:i:s')],
		);
		$applyId = M('tuan_apply')->where($where)->getField('id');
		return (int) M('tuan_apply_list')->where(['id' => $applyId])->sum('num');
	}

	/**
	 * 统计 商品 已团多少件
	 */
	public function sale_goods_num($goodsId){
		$attr 	= M('goods_attr_list')->field('id')->where(['goods_id' => $goodsId])->select();
		$ids 	= array_column($attr,'id');
		$joinMap = array(
			'status'				=> ['in', [0,1]],
			'goods_attr_list_id'	=> ['in', $ids],
		);
		return (int) M('tuan_join')->where($joinMap)->sum('num');
	}

	/**
	 * 已团成功列表，并且还没生成订单的，用于下面的创建订单
	 * api: /Tuan/success_list
	 * author: Lzy
	 * date: 2017-04-14
	 */
	public function success_list(){
		$this->check('');
		$res = M('tuan_start')->where(['status' => 1, 'is_order' => 0, 'all_pay' => 1])->select();
		$res = $res ? ['code' => 1, 'data' => $res] : ['code' => 3];
		$this->apiReturn($res);
	}

	/**
	 * subject: 已团成功 生成订单
	 * api: /Tuan/create_orders
	 * author: Lzy
	 * date: 2017-04-14
	 * param: tuan_start_id,int,1,开团id
	 */
	public function create_orders(){
		$this->check('tuan_start_id');
		$res = $this->_create_orders($this->post);
		$this->apiReturn($res);
	}

	public function _create_orders($post){
		$model = M();
		$model->startTrans();
		$start = M('tuan_start')->find($post['tuan_start_id']);
		if(empty($start)){
			return ['code' => 0, 'msg' => '拼团不存在'];
		}
		if($start['status'] != 1){
			return ['code' => 0, 'msg' => '当前状态不允许操作'];
		}
		if($start['all_pay'] != 1 || $start['number'] != M('tuan_join')->where(['tuan_start_id' => $start['id'],'status' => 1,'is_pay' => 1,'is_order' => 0])->count()){
			return ['code' => 0, 'msg' => '存在未付款成员'];
		}
		$joinMap = array(
			'status' 		=> 1,
			'tuan_start_id' => $start['id'],
			'is_pay'		=> 1,
			'is_order'		=> 0,
		);

		$list = M('tuan_join')->where($joinMap)->select();
		foreach ($list as $value) {
			$start 		= M('tuan_start')->find($value['tuan_start_id']);
			$goods 		= M('goods')->find($start['goods_id']);
			$pay 		= M('tuan_join_pay')->find($value['tuan_join_pay_id']);
			$address 	= M('shopping_address')->find($value['address_id']);
			$attrList 	= M('goods_attr_list')->find($value['goods_attr_list_id']);
			$o_no 		= $this->create_orderno('OG',$value['uid']);
			$s_no 		= $this->create_orderno('DD',$value['uid']);
			$orders = array(
				'status' 	=> 1,
				'ip'		=> get_client_ip(),
				'uid' 		=> $value['uid'],
				'o_no' 		=> $o_no,
				'is_pay' 	=> 1,
				'pay_price' => $value['price'] * $value['num'],
				'pay_time' 	=> $pay['atime'],
				'pay_type' 	=> $pay['pay_type'],
				'province' 	=> $address['province'],
				'city' 		=> $address['city'],
				'district' 	=> $address['district'],
				'town' 		=> $address['town'],
				'street' 	=> $address['street'],
				'linkname' 	=> $address['linkname'],
				'tel' 		=> $address['tel'],
				'mobile' 	=> $address['mobile'],
				'postcode' 	=> $address['postcode'],
				'score' 	=> $goods['score_ratio'] * $value['price'] * $value['num'] * 100,
			);
			$oId = M('orders')->data($orders)->add();

			$orders_shop = array(
				'ip'					=> get_client_ip(),
				'status' 				=> 2,
				'terminal' 				=> 1,
				'o_id' 					=> $oId,
				'o_no' 					=> $o_no,
				's_no' 					=> $s_no,
				'shop_id' 				=> $goods['shop_id'],
				'uid' 					=> $value['uid'],
				'seller_id' 			=> $goods['seller_id'],
				'goods_price' 			=> $value['price'],
				'total_price' 			=> $value['price'] * $value['num'],
				'pay_price' 			=> $pay['pay_price'],
				'is_pay' 				=> 1,
				'pay_type' 				=> $pay['pay_type'],
				'pay_time' 				=> $pay['atime'],
				'express_type' 			=> 1,
				'express_id' 			=> 0,
				'express_company_id' 	=> 0,
				'express_code' 			=> 0,
				'express_remark' 		=> '',
				'remark' 				=> '',
				'goods_num' 			=> $value['num'],
				'score' 				=> $goods['score_ratio'] * $value['price'] * $value['num'] * 100,
				'inventory_type' 		=> 1,
				'is_sys' 				=> 1,
				'dtpay_no' 				=> $pay['pay_no'],
			);
			$sId = M('orders_shop')->data($orders_shop)->add();

			$orders_goods = array(
				'ip' 			=> get_client_ip(),
				's_id' 			=> $sId,
				'o_id' 			=> $oId,
				'o_no' 			=> $o_no,
				's_no' 			=> $s_no,
				'uid' 			=> $value['uid'],
				'seller_id' 	=> $goods['seller_id'],
				'goods_id' 		=> $goods['id'],
				'attr_list_id' 	=> $value['goods_attr_list_id'],
				'shop_id' 		=> $goods['shop_id'],
				'attr_id' 		=> $attrList['attr_id'],
				'attr_name' 	=> $attrList['attr_name'],
				'is_subnum' 	=> 0,
				'price' 		=> $value['price'],
				'num' 			=> $value['num'],
				'total_price' 	=> $value['price'] * $value['num'],
				'goods_name' 	=> $goods['goods_name'],
				'images' 		=> $goods['images'],
				'is_rate' 		=> 0,
				'score_ratio' 	=> $goods['score_ratio'],
				'score' 		=> $goods['score_ratio'] * $value['price'] * $value['num'] * 100,
			);
			
			$gId = M('orders_goods')->data($orders_goods)->add();

			if($oId == false || $sId == false || $gId == false){
				$model->rollback();
				return ['code' => 0];
			}
			$joinSave = M('tuan_join')->data(['is_order' => 1,'s_no' => $s_no])->where(['id' => $value['id']])->save();
			$startSave = M('tuan_start')->data(['is_order' => 1])->where(['id' => $start['id']])->save();
			if($joinSave == false || $startSave == false){
				$model->rollback();
				return ['code' => 0];
			}
		}
		$model->commit();
		return ['code' => 1];
	}

	/**
	 * 团购过期更改状态，即拼团失败
	 * api: /Tuan/tuan_time_out
	 * author: Lzy
	 * date: 2017-04-19
	 */
	public function tuan_time_out(){
		$this->check('');
		$res = $this->_tuan_time_out($this->post);
		$this->apiReturn($res);
	}

	public function _tuan_time_out($post){
		$model = M();
		$model->startTrans();
		# 暂定超时10分钟再处理,防止和生成订单的时候冲突
		$start = M('tuan_start')->field('id')->where(['status' => ['in',[0,1]],'is_order' => 0,'end' => ['elt',date('Y-m-d H:i:s',time() - 10 * 60)]])->select();
		if($start){
			$ids = array_column($start, 'id');
			$save1 = M('tuan_start')->where(['id' => ['in',$ids]])->data(['status' => 2])->save();
			$save2 = M('tuan_join')->where(['tuan_start_id' => ['in',$ids],'status' => ['in',[0,1]]])->data(['status' => 2])->save();
			if($save1 == false || $save2 == false){
				$model->rollback();
				return ['code' => 0];
			}
		}else{
			return ['code' => 0, 'msg' => '没有需要处理的记录'];
		}
		$model->commit();
		return ['code' => 1];
	}

	/**
	 * 团购失败，退款,每次退一条
	 * api: /Tuan/failed_refund
	 * author: Lzy
	 */
	public function failed_refund(){
		$model = M();
		$model->startTrans();
		$one = M('tuan_join')->where(['status' => 2,'is_pay' => 1, 'is_refund' => 0])->find();
		if($ids){
			$save = M('tuan_join')->where(['id' => $one['id']])->data(['is_refund' => 1])->save();
			if($save == false){
				$model->rollback();
				return ['code' => 0];
			}
			# 退款erp接口
			# code...
		}
		$model->commit();
		return ['code' => 1];
	}

	function writeLog($logs){
		$logsData = array(
			'type' 		=> $logs['type'] ? $logs['type'] : 0,
			'data' 		=> json_encode($logs['data'] ? (array) $logs['data'] : [], JSON_UNESCAPED_UNICODE),
			'content' 	=> $logs['content'] ? $logs['content'] : '',
		);
		return (bool) M('tuan_logs')->data($logsData)->add();
	}
}