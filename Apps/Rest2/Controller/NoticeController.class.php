<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 消息相关接口
 * ----------------------------------------------------------
 * Author:lizuheng <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-02-21
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller;
use Common\Notice\System;
class NoticeController extends ApiController {
    protected $action_logs = array('saveRead','delete_notice');
	private $categoryArr = [1=>'系统通知',3=>'促销通知',4=>'站内信',5=>'交易通知',6=>'退款通知',7=>'售后通知'];
	private $statusArr = ['未读', '已读'];
	
    /**
     * subject: Wap消息列表
     * api: /Notice/index
     * author: lizuheng
     * day: 2017-02-21
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: p,int,0,当前页(默认第一页)
     * param: pagesize,int,0,当前页数量，默认15条
	 * param: status,int,0,是否阅读，默认全部
	 * param: category_id,int,0,消息分类，默认全部，多个分类用，隔开
     */
    public function index(){
		$field = 'openid,sign';
		$this->check($field,false);
		
		$res = $this->_index($this->post);
        $this->apiReturn($res);		
	}
	
    public function _index($param){		
		//$map['to_uid'] = $this->user['id'];
		$map['to_uid'] = ['in',$this->user['id'].',0'];	

		if (isset($param['status']) && array_key_exists($param['status'], $this->statusArr))  {
            $map['is_read'] = $param['status'];
        } else {
            $map['is_read'] = ['in', '0,1'];
			
        }
		if(isset($param['category_id']) && !empty($param['category_id'])){
			$map['category_id'] = ['in',$param['category_id']];
		}
		 $list = pagelist([
            'do'        => 'M',
            'table'     => 'msg',
            'map'       => $map,
            'pagesize'  => 15,
            'p'         => I('post.p', 1, 'int'),
            'order'     => 'id desc',
            //'cache_name'=> md5(implode(',', I('post.')) . ACTION_NAME),
        ]);
		if (!empty($list['list'])) {
            foreach ($list['list'] as &$v) {
                $v['status_name'] = $this->statusArr[$v['is_read']];
                $v['category_name'] = $this->categoryArr[$v['category_id']];
                $v['content']     = html_entity_decode($v['content']);
				//$v['title']       = mb_substr(strip_tags($v['content']),0,20,'utf-8');//html_entity_decode($v['content']);
            }
            unset($v);
            return ['code' => 1,'data' => $list];
        }
		return ['code' => 3,'msg' => '找不到记录！'];
		/*
		$map['uid'] = $this->user['id'];
		$statusArr = ['未读', '已读'];
        if (isset($param['status']) && array_key_exists($param['status'], $statusArr))  {
            $map['is_read'] = $param['status'];
        } else {
            $map['is_read'] = ['in', '0,1'];
        }

        $list = pagelist([
            'table'     => 'message_notice',
            'map'       => $map,
            'pagesize'  => 15,
            'order'     => 'id desc',
            'p'         => $param['p'],
            //'cache_name'=> md5(implode(',', I('post.')) . ACTION_NAME),
        ]);
        if (!empty($list['list'])) {
            foreach ($list['list'] as &$v) {
                $v['status_name'] = $statusArr[$v['is_read']];
                $v['content']     = html_entity_decode($v['content']);
				$v['title']       = mb_substr(strip_tags($v['content']),0,20,'utf-8');//html_entity_decode($v['content']);
            }
            unset($v);
            return ['code' => 1,'data' => $list];
        }
        return ['code' => 3,'msg' => '找不到记录！'];
		*/
	}	
	
	/**
     * subject: Wap消息详情
     * api: /Notice/read
     * author: lizuheng
     * day: 2017-02-22
     *
     * [字段名,类型,是否必传,说明]
	 * param: id,string,1,消息ID
     * param: openid,string,1,用户openid
     */
    public function read() {
		$field = 'openid,id,sign';
		$this->check($field);

        $res = $this->_read($this->post);
        $this->apiReturn($res);
    }
	public function _read($param){
		/*
		$res = M('msg')->where(['to_uid'=>$this->uid,'id'=>$param['id']])->save(['is_read'=>1,'rtime' => date('Y-m-d H:i:s', NOW_TIME)]);
        if ($res == false) {
            $this->apiReturn(3);
        }
        $this->apiReturn(1, ['data' => $res]);
		*/
        $data = (new System($this->user['id'], $param['id']))->read();
        if ($data == false) {
            return ['code' => 3,'msg' => '找不到记录！'];
        }
		return ['code' => 1,'data' => $data];
		
    }
	
	/**
     * subject: 设为已阅读和未阅读状态
     * api: /Notice/saveRead
     * author: lizuheng
     * day: 2017-02-22
     *
     * [字段名,类型,是否必传,说明]
	 * param: id,string,1,消息ID
     * param: 用户openid,string,1,用户openid
     * param: is_read,int,1,设置阅读状态
     */
    public function saveRead() {
		$field = 'id,is_read,openid,sign';
		$this->check($field);

        $res = $this->_saveRead($this->post);
        $this->apiReturn($res);
    }
	public function _saveRead($param){
		$res = M('msg')->where(['to_uid'=>$this->user['id'],'id'=>$param['id']])->save(['is_read'=>$param['is_read'],'rtime' => date('Y-m-d H:i:s', NOW_TIME)]);
		if ($res) {
			$cacheName = md5('Notice_count_' . $param['openid']);
            S($cacheName, null);			
            return ['code' => 1];
        }
        return ['code' => 3,'msg' => '设置失败！'];
		/*
        $flag = M('message_notice')->where('id='.$param['id'])->save(['is_read' => $param['is_read'], 'rtime' => date('Y-m-d H:i:s', NOW_TIME)]);
        if ($flag) {
			$cacheName = md5('Notice_count_' . $param['openid']);
            S($cacheName, null);
            return ['code' => 1,'data' => $param];
        }
        return ['code' => 3,'msg' => '设置失败！'];
		*/
    }

	/**
     * subject: Wap删除消息
     * api: /Notice/delete_notice
     * author: lizuheng
     * day: 2017-02-22
     *
     * [字段名,类型,是否必传,说明]
	 * param: id,string,1,消息ID
     * param: openid,string,1,用户openid
     */
    public function delete_notice() {
		$field = 'id,openid,sign';
		$this->check($field);

        $res = $this->_delete_notice($this->post);
        $this->apiReturn($res);
    }
	public function _delete_notice($param){
		
		$res = M('msg')->where(['to_uid'=>$this->user['id'],'id'=>$param['id']])->delete();
		
        if ($res) {
			return ['code'=>1,'data'=>$res];
        }
		return ['code' => 3,'msg' => '删除失败！'];
		/*
		$data['id'] = $param['id'];
		$data['uid']= $this->user['id'];
        $flag = M('message_notice')->where($data)->save(['is_read' => 2]);
        if ($flag) {
            return ['code' => 1,'data' => $flag];
        }
        return ['code' => 3,'msg' => '删除失败！'];
		*/
	}
	/**
     * subject: Wap总消息统计
     * api: /Notice/notice_count
     * author: liangfeng
     * day: 2017-05-15
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     */
    public function notice_count() {
		$field = 'openid,sign';
		$this->check($field,false);
        $res = $this->_notice_count($this->post);
        $this->apiReturn($res);
    }
	public function _notice_count($param){
		//$cacheName = md5('notice_all_count_' . $param['openid']);
		//if(S($cacheName)){
		//	return ['code' => 1,'data' => S($cacheName)];
		//}else{
			$res = M('msg')->field('category_id,is_read,count(id) as num')->where(['to_uid'=>$this->user['id']])->group('category_id,is_read')->select();
			if($res){
				foreach($this->categoryArr as $k => $v){
					foreach($this->statusArr as $ke => $va){
						$arr[$k.$ke] = ['category_id'=>$k,'categoey_name'=>$v,'is_read'=>$ke,'is_read_name'=>$va,'num'=>0];
					}
				}
				
				foreach($res as $v){
					$arr[$v['category_id'].$v['is_read']]['num'] = $v['num'];
				}
				//S($cacheName, $arr);
				return ['code' => 1,'data' => $arr];
			}
			return ['code' => 3,'msg' => '暂无消息！'];
		//}
	}	
	/**
     * subject: Wap消息统计
     * api: /Notice/count
     * author: lizuheng
     * day: 2017-02-23
     *
     * [字段名,类型,是否必传,说明]
	 * param: status,string,1,消息状态
     * param: openid,string,1,用户openid
     */
    public function count() {
		$field = 'openid,status,sign';
		$this->check($field,false);

        $res = $this->_count($this->post);
        $this->apiReturn($res);
    }
	public function _count($param){
		$cacheName = md5('Notice_count_' . $param['openid']);
        $count = S($cacheName);
		$data['uid'] = $this->user['id'];
		$data['is_read'] = $param['status'];
        $count = M('msg')->where(['to_uid'=>$this->user['id'],'is_read'=>0])->count();
        if ($count) {
			if ($count > 99) $count = 99;   //最多为99
            S($cacheName, $count);
            return ['code' => 1,'data' => $count];
        }
        return ['code' => 3,'msg' => '暂无消息！'];
		/*
		$cacheName = md5('Notice_count_' . $param['openid']);
        $count = S($cacheName);
		$data['uid'] = $this->user['id'];
		$data['is_read'] = $param['status'];
        $count = M('message_notice')->where($data)->count();
        if ($count) {
			if ($count > 99) $count = 99;   //最多为99
            S($cacheName, $count);
            return ['code' => 1,'data' => $count];
        }
        return ['code' => 3,'msg' => '暂无消息！'];
		*/
	}	

	/**
     * subject: Wap获取最新的一条消息
     * api: /Notice/new_notice
     * author: lizuheng
     * day: 2017-02-22
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     */
    public function new_notice() {
		$field = 'openid,sign';
		$this->check($field,false);

        $res = $this->_new_notice($this->post);
        $this->apiReturn($res);
    }
	public function _new_notice($param){
		$data['to_uid']= $this->user['id'];
        $flag = M('msg')->where($data)->order("id desc")->find();
        if ($flag) {
			$flag['content'] = html_entity_decode($flag['content']);
            return ['code' => 1,'data' => $flag];
        }
        return ['code' => 3,'msg' => '获取失败！'];
		/*
		$data['uid']= $this->user['id'];
        $flag = M('message_notice')->cache(true)->where($data)->order("atime desc")->find();
        if ($flag) {
			$flag['content'] = html_entity_decode($flag['content']);
            return ['code' => 1,'data' => $flag];
        }
        return ['code' => 3,'msg' => '获取失败！'];
		*/
	}
	
	/**
     * subject: 获取
     * api: /Notice/get_msg_category
     * author: liangfeng
     * day: 2017-05-18
     *
     * [字段名,类型,是否必传,说明]
     */
    public function get_msg_category() {
		$field = '';
		$this->check($field,false);
        $res = $this->_get_msg_category($this->post);
        $this->apiReturn($res);
    }
	public function _get_msg_category($param){
		$res = M('msg_category')->cache(true)->field('id,category_name')->select();
		if($res){
			return ['code' => 1,'data' => $res];
		}
		return ['code' => 3,'msg' => '获取失败！'];
		
	}
	
	
}