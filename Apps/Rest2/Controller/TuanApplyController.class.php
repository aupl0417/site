<?php
namespace Rest2\Controller;
use Common\Controller\TuanApplyBaseController as Base;

/**
 * 拼团购商家管理模块
 * @author Lzy 2017-03-29
 */

class TuanApplyController extends ApiController
{

	protected $goods;
	/**
	 * subject: 商家申请拼团购活动的列表
	 * api: /TuanApply/plist
	 * author: Lzy
	 * date 2017-03-29
	 * param: openid,string,1,商家openid
	 * param: status,int,0,审核状态 (0审核中,1审核通过,2审核失败)
	 * param: p,int,0,当前页数，默认第一页
	 * param: pagesize,int,0,每页记录数，默认10条
	 */
	public function plist(){

		$this->check('openid');

		$res = $this->_plist($this->post);

        $this->apiReturn($res);
	}

	public function _plist($param){
		$map['uid'] = $this->user['id'];

		if(isset($param['status'])){
			$map['status'] = $param['status'];
		}

		$pagelist = pagelist(array(
            'table'     => 'Common/TuanApplyRelation',
            'map'       => $map,
            'do'		=> 'D',
            'relation'	=> true,
            'fields'    => '*',
            # 'relationField' => ['begin,end'],
            'pagesize'  => $param['pagesize'] ? $param['pagesize'] : 10,
            'p'         => $param['p'] ? $param['p'] : 1,
        ));
        
        if($pagelist['list']){
        	$now = date('Y-m-d H:i:s');
        	foreach ($pagelist['list'] as $key => $value) {
    			if($now < $value['begin'] || $value['status'] != 1){
    				$status_name = '未开始';
    			}else if($now > $value['end']){
    				$status_name = '已结束';
    			}else if($now >= $value['begin'] && $now <= $value['end']){
    				$status_name = '活动中';
    			}
    			$pagelist['list'][$key]['status_name'] = $status_name;
        	}
        	return ['code'=> 1, 'data' => $pagelist];
        }else{
        	return ['code' => 3];
        }
	}

	/**
	 * subject: 申请list详情
	 * api: /TuanApply/list_view
	 * author: Lzy
	 * date: 2017-04-15
	 * param: tuan_apply_list_id,int,1,申请list id
	 */
	public function list_view(){
		$this->check('tuan_apply_list_id');
		$res = $this->_list_view($this->post);
		$this->apiReturn($res);
	}

	public function _list_view($post){
		$applyList = M('tuan_apply_list')->find($post['tuan_apply_list_id']);
		if($applyList){
			$applyList['goods_attr_list'] 	= M('goods_attr_list')->field('goods_id,attr_name,images')->find($applyList['goods_attr_list_id']);
			$applyList['goods'] 			= M('goods')->field('goods_name,seller_id,score_ratio')->find($applyList['goods_attr_list']['goods_id']);
			$applyList['shop'] 				= M('shop')->where(['uid' => $applyList['goods']['seller_id']])->field('id,uid,shop_logo,shop_name')->find();
			return ['code' => 1, 'data' => $applyList];
		}else{
			return ['code' => 3];
		}
	}

	/**
	 * subject: 商家申请拼团详情
	 * api: /TuanApply/view
	 * author: Lzy
	 * date 2017-03-29
	 *
	 * param: openid,string,1,商家openid
	 * param: tuan_apply_id,string,1,团购申请id
	 */
	public function view(){
		$this->check('openid,tuan_apply_id');

		$res = $this->_view($this->post);

		$this->apiReturn($res);
	}

	public function _view($param){
		$one = D('Common/TuanApplyRelation')->relation(true)->find($param['tuan_apply_id']);
		if($one['id'] && $one['uid'] == $this->user['id']){
			$now = date('Y-m-d H:i:s');
			if($now < $one['begin'] || $one['status'] != 1){
				$status_name = '未开始';
			}else if($now > $one['end']){
				$status_name = '已结束';
			}else if($now >= $one['begin'] && $now <= $one['end']){
				$status_name = '活动中';
			}
    		$one['status_name'] = $status_name;
			return ['code' => 1, 'data' => $one];
		}else{
			return ['code' => 3, 'msg' => '没有找到记录'];
		}
	}


	/**
	 * subject: 创建拼团购活动申请
	 * author: Lzy
	 * api: /TuanApply/create
	 * date: 2017-03-29
	 *
	 * param: openid,string,1,商家openid
	 * param: goods_id,int,1,商品id
	 * param: category_id,int,1,分类id
	 * param: number,int,1,成团人数
	 * param: days,int,1,活动天数
	 * param: tuan_apply_list,array,1,商品属性设置列表
	 */
	public function create(){
		$this->post['tuan_apply_list'] = html_entity_decode($this->post['tuan_apply_list']);
		$this->check('openid,goods_id,category_id,number,days,tuan_apply_list');

		$res = $this->_create($this->post,$this->user);

		$this->apiReturn($res);
	}

	/**
	 * 创建
	 */
	public function _create($post,$user){
		$model = M();
		$model->startTrans();
		$this->goods = M('goods')->find($post['goods_id']);

		if($this->goods['seller_id'] != $user['id']) return ['code' => 0, 'msg' => '商品不存在'];
		if($this->goods['status'] != 1) return ['code' => 0, 'msg' => '商品' . $key . '不在上架状态'];
		# 选择商品数量
		$list = json_decode($post['tuan_apply_list'], TRUE);
		if(is_array($list) == FALSE){
			$model->rollback();
			return ['code' => 0];
		}
		if(count($list) <= 0){
			$model->rollback();
			return ['code' => 0, 'msg' => '参加拼团购活动必须选择1款库存商品'];
		}
		# 检测商品重复
		foreach ($list as $key => $value) {
			$g[] = $value['goods_attr_list_id'];
		}
		if(count($g) != count(array_unique($g))){
			$model->rollback();
			return ['code' => 0, 'msg' => '存在重复库存商品'];
		}
		# 检测是否在申请
		if(M('tuan_apply')->field('id')->where(['goods_id' => $this->goods['id'],'status' => ['in','0,2']])->getField('id')){
			$model->rollback();
			return ['code' => 0, 'msg' => '商品正在申请拼团购活动，不能重复申请'];
		}
		# 检测是否在活动
		$check_activity = $this->check_activity();
		if($check_activity['code'] == 0){
			return $check_activity;
		}
		# 拼团购申请表添加记录
		$ta_no = $this->create_orderno('TG',$user['id']);
		$data = array(
			'ip' 			=> get_client_ip(),
			'uid' 			=> $user['id'],
			'ta_no'			=> $ta_no,
			'category_id' 	=> $post['category_id'],
			'status' 		=> 0,
			'goods_id'		=> $post['goods_id'],
			'number'		=> $post['number'],
			'days'			=> $post['days'],
			'goods_name'	=> $this->goods['goods_name'],
		);
		$applyModel = D('Common/TuanApply');
		if($applyModel->create($data) == FALSE){
			$model->rollback();
			return ['code' => 0, 'msg' => $applyModel->getError()];
		}
		
		$tuan_apply_id = $applyModel->add();
		if(FALSE == $tuan_apply_id){
			$model->rollback();
			return ['code' => 0, 'msg' => $applyModel->getError()];
		}
		
		# 店铺检测
		$shop_map = $this->_shop_condition($user['id']);
		if($shop_map['code'] == 0){
			$model->rollback();
			return $shop_map;
		}
		# 添加list记录
		foreach (array_values($list) as $key => $value) {
			$key = $key + 1;
			# 验证数据
			$value['ta_no'] 		= $ta_no;
			$value['tuan_apply_id']	= $tuan_apply_id;
			$applyList = D('Common/TuanApplyList');
			if($applyList->create($value) == FALSE){
				$model->rollback();
				return ['code' => 0, 'msg' => '库存商品' . ($key) . $applyList->getError()];
			}
			# 商品属性检测
			$goods_map = unserialize(html_entity_decode(M('officialactivity')->find(254)['goods_map']));
			if($value['price'] > M('goods_attr_list')->where(['id' => $value['goods_attr_list_id']])->getField('price') * $goods_map['discount_ratio'] / 10){
				$model->rollback();
				return ['code' => 0, 'msg' => '库存商品' . $key . '折扣不符合要求。'];
			}
			$goodsAttr = M('goods_attr_list')->where(['id' => $value['goods_attr_list_id'],'goods_id' => $this->goods['id']])->find();
			if($goodsAttr == FALSE){
				$model->rollback();
				return ['code' => 0, 'msg' => '库存商品' . $key . '不存在'];
			}
			if($goodsAttr['num'] < $num){
				$model->rollback();
				return ['code' => 0, 'msg' => '库存商品' . $key . '库存不足'];
			}
			# 添加记录
			if($applyList->add() == FALSE){
				$model->rollback();
				return ['code' => 0];
			}
		}
		$model->commit();
		return ['code' => 1];
	}

	/**
	 * 检查是否在活动
	 */
	public function check_activity(){
		$now 	= date('Y-m-d H:i:s');
		$where 	= array(
			'goods_id' 	=> $this->goods['id'],
			'status' 	=> '1',
			'end' 		=> ['egt', $now],
		);
		$activity = M('tuan_apply')->field('begin')->where($where)->find();
		if($activity){
			if($activity['begin'] >= $now){
				return ['code' => 0, 'msg' => '商品即将开始拼团购活动，不能重复申请'];
			}else{
				return ['code' => 0, 'msg' => '商品已经在拼团购活动中，不能重复申请'];
			}
		}
		return ['code' => 1];
	}

	/**
	 * subject: 拼团购活动申请修改
	 * author: Lzy
	 * api: /TuanApply/edit
	 * date: 2017-03-29
	 *
	 * param: openid,string,1,商家openid
	 * param: id,int,1,申请id
	 * param: category_id,int,1,分类id
	 * param: number,int,1,成团人数
	 * param: days,int,1,活动天数
	 * param: tuan_apply_list,array,1,商品属性设置列表
	 */
	public function edit(){
		$this->post['tuan_apply_list'] = html_entity_decode($this->post['tuan_apply_list']);
		$this->check('openid,id,category_id,number,days,tuan_apply_list');

		$res = $this->_edit($this->post,$this->user);

		$this->apiReturn($res);
	}

	public function _edit($post,$user){
		$model = M();
		$model->startTrans();

		$one = M('tuan_apply')->where(['uid' => $user['id'],'id' => $post['id']])->find();
		if(empty($one)) return ['code' => 0];
		if($one['status'] != 2) return ['code' => 0, 'msg' => '此状态不允许编辑'];
		$this->goods = M('goods')->find($one['goods_id']);

		if($this->goods['seller_id'] != $user['id']) return ['code' => 0, 'msg' => '商品不存在'];
		if($this->goods['status'] != 1) return ['code' => 0, 'msg' => '商品' . $key . '不在上架状态'];

		# 选择商品数量
		$list = json_decode($post['tuan_apply_list'], TRUE);
		if(is_array($list) == FALSE){
			$model->rollback();
			return ['code' => 0];
		}
		if(count($list) <= 0){
			$model->rollback();
			return ['code' => 0, 'msg' => '参加拼团购活动必须选择1款库存商品'];
		}
		
		# 拼团购申请表修改记录
		$ta_no = $this->create_orderno('TG',$user['id']);
		$data = array(
			'id'			=> $one['id'],
			'ip' 			=> get_client_ip(),
			'uid' 			=> $user['id'],
			'ta_no'			=> $ta_no,
			'category_id' 	=> $post['category_id'],
			'status' 		=> 0,
			'goods_id'		=> $one['goods_id'],
			'number'		=> $post['number'],
			'days'			=> $post['days'],
		);
		$applyModel = D('Common/TuanApply');
		if($applyModel->create($data) == FALSE){
			$model->rollback();
			return ['code' => 0, 'msg' => $applyModel->getError()];
		}
		$save = $applyModel->save();
		if(FALSE == $save){
			$model->rollback();
			return ['code' => 0, 'msg' => $applyModel->getError()];
		}
		# 修改list记录
		foreach (array_values($list) as $key => $value) {
			$key = $key + 1;
			# 验证数据
			$tuanApplyList = M('tuan_apply_list')->where(['id' => $value['id'], 'tuan_apply_id' => $one['id']])->find();
			if(empty($tuanApplyList)) return ['code' => 0];

			$tuanApplyList['num'] 			= $value['num'];
			$tuanApplyList['price'] 		= $value['price'];
			$tuanApplyList['single_price'] 	= $value['single_price'];

			$applyList = D('Common/TuanApplyList');
			if($applyList->create($tuanApplyList) == FALSE){
				$model->rollback();
				return ['code' => 0, 'msg' => '库存商品' . ($key) . $applyList->getError()];
			}
			$goodsAttr = M('goods_attr_list')->where(['id' => $tuanApplyList['goods_attr_list_id'],'goods_id' => $this->goods['id']])->find();
			if($goodsAttr == FALSE){
				$model->rollback();
				return ['code' => 0, 'msg' => '库存商品' . $key . '不存在'];
			}
			if($goodsAttr['num'] < $num){
				$model->rollback();
				return ['code' => 0, 'msg' => '库存商品' . $key . '库存不足'];
			}
			# 添加记录
			if($applyList->save() === FALSE){
				$model->rollback();
				return ['code' => 0];
			}
		}
		$model->commit();
		return ['code' => 1];
	}

	/**
	 * subject: 检测店铺条件
	 * author: Lzy
	 * date: 2017-04-06
	 * api: /TuanApply/shop_condition
	 * param: openid,string,int,1,商家openid
	 */
	public function shop_condition(){
		$this->check('openid');
		$res = $this->_shop_condition($this->user['id']);
		$this->apiReturn($res);
	}

	/**
	 * 店铺条件检测
	 */
	public function _shop_condition($uid){
		$map = M('officialactivity')->find(254)['shop_map'];
		if(! $map){
			return ['code' => 0, 'msg' => '店铺条件检测失败。'];
		}
		$shop = M('shop')->where(['uid' => $uid])->find();
		if(empty($shop)){
			return ['code' => 0, 'msg' => '尚未开店。'];
		}
		$map = unserialize(html_entity_decode($map));
		if($map['fraction_speed'] > $shop['fraction_speed']){
			return ['code' => 0, 'msg' => '店铺物流评分不符合要求。'];
		}
		if($map['fraction_service'] > $shop['fraction_service']){
			return ['code' => 0, 'msg' => '店铺服务评分不符合要求。'];
		}
		if($map['fraction_desc'] > $shop['fraction_desc']){
			return ['code' => 0, 'msg' => '店铺描述评分不符合要求。'];
		}
		if($map['goods_num'] > $shop['goods_num']){
			return ['code' => 0, 'msg' => '店铺在售商品数不符合要求。'];
		}
		return ['code' => 1];
	}

	/**
	 * subject: 检测商品条件
	 * author: Lzy
	 * date: 2017-04-06
	 * api: /TuanApply/goods_condition
	 * param: goods_id,int,1,商品id
	 */
	public function goods_condition(){
		$this->check('goods_id');
		$res = $this->_goods_condition(I('goods_id'));
		$this->apiReturn($res);
	}



	/**
	 * 商品条件检测
	 */
	public function _goods_condition($goods_id){
		$map = M('officialactivity')->find(254)['goods_map'];
		if(! $map){
			return ['code' => 0, 'msg' => '商品条件检测失败。'];
		}
		$goods = M('goods')->where(['id',['in',$goods_id]])->select();
		$map = unserialize(html_entity_decode($map));
		foreach ($goods as $value) {
			if($map['fraction'] > $value['fraction']){
				return ['code' => 0, 'msg' => '商品好评率不符合要求。'];
			}
			# if($map['discount_ratio'] > $value['price'] * ){
				# return ['code' => 0, 'msg' => '商品折扣不符合要求。'];
			# }
			if($map['sale_num'] > $value['sale_num']){
				return ['code' => 0, 'msg' => '商品销售量不符合要求。'];
			}
		}
		return ['code' => 1];
		
	}

	/**
	 * subject: 审核
	 * author: Lzy
	 * date: 2017-04-15
	 * api: /TuanApply/audit
	 * param: id,int,1,申请id
	 * param: status,int,1,状态
	 * param: content,string,1,内容
	 * param: admin_id,int,1,管理员id
	 */
	public function audit(){
		$this->check('id,status,content,admin_id');
		$res = $this->_audit($this->post);
		$this->apiReturn($res);
	}

	function _audit($param){
		$model = M();
		$model->startTrans();
		$one 		= M('tuan_apply')->find($param['id']);
		$status 	= $param['status'];
		$content 	= $param['content'];

		if(empty($one) || $one['status'] != 0 || FALSE == in_array($status, [1,2])){
			$model->rollback();
			return ['status' => 'warnning', 'msg' => '操作失败，请稍后重试。(1)'];
		}
		$logs = array(
			'ta_no' 	=> $one['ta_no'],
			'admin_id' 	=> $param['admin_id'],
			'content' 	=> $content,
			'status' 	=> $status,
			'ip'		=> $this->ip,
		);
		$createLog = M('tuan_apply_logs')->data($logs)->add();
		if(FALSE == $createLog){
			$model->rollback();
			return ['status' => 'warnning', 'msg' => '操作失败，请稍后重试。(2)'];
		}
		if($status == 2){
			$save = M('tuan_apply')->data(['status' => $status])->where(['id' => $one['id']])->save();
			if(FALSE == $save){
				$model->rollback();
				return ['status' => 'warnning', 'msg' => '操作失败，请稍后重试。(3)'];
			}
		}
		if($status == 1){
			$now 		= time();
			$h 			= date('H', $now);
			$timing 	= 10;
			$begin 		= $now;
			$end 		= $now + $one['days'] * 3600 * 24;
			$dateformat = 'Y-m-d ' . $timing . ':00:00';
			# $dateformat2 = 'Y-m-d H:00:00';
			# $dateformat3 = 'Y-m-d H:i:s';
			$data = array(
				'status' 	=> $status,
				'begin'		=> date($dateformat, $h >= 10 ? $begin + 3600 * 24 	: $begin),
				'end'		=> date($dateformat, $h >= 10 ? $end + 3600 * 24 	: $end),
			);
			$save = M('tuan_apply')->data($data)->where(['id' => $one['id']])->save();
			if(FALSE == $save){
				$model->rollback();
				return ['status' => 'warnning', 'msg' => '操作失败，请稍后重试。(4)'];
			}
		}
		$model->commit();
		return ['status' => 'success', 'msg' => '操作成功。'];
	}
}