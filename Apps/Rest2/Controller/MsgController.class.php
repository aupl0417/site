<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 消息相关接口
 * ----------------------------------------------------------
 * Author:liangfeng
 * ----------------------------------------------------------
 * 2017-05-13
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;

class MsgController extends ApiController {
   
	
	
	/**
     * subject: 消息列表
     * api: /Notice/msg_list
     * author: liangfeng
     * day: 2017-05-13
     *
     * [字段名,类型,是否必传,说明]
	 * param: openid,string,1,用户openid
	 * param: category_id,int,0,消息分类,默认全部
     * param: p,int,0,当前页(默认第一页)
     * param: pagesize,int,0,当前页数量，默认15条
     */
    public function msg_list(){
		$this->check('openid',false);
		$res = $this->_msg_list($this->post);
        $this->apiReturn($res);		
	}
    public function _msg_list($param){
		$map['to_uid'] = $this->user['id'];
		if($param['category_id'] != 0 && isset($param['category_id'])){
			$map['category_id'] = $param['category_id'];
		}
        $list = pagelist([
            'table'     => 'msg',
            'map'       => $map,
            'pagesize'  => $param['pagesize'],
            'order'     => 'id desc',
            'p'         => $param['p'],
        ]);
        if (!empty($list['list'])) {
            foreach ($list['list'] as &$v) {
                $v['content']     = html_entity_decode($v['content']);
            }
            return ['code' => 1,'data' => $list];
        }
        return ['code' => 3,'msg' => '找不到记录！'];
	}	
	
	/**
     * subject: 消息详情
     * api: /Notice/msg_view
     * author: liangfeng
     * day: 2017-05-13
     *
     * [字段名,类型,是否必传,说明]
	 * param: openid,string,1,用户openid
	 * param: msg_id,int,0,消息id
     */
    public function msg_view(){
		$this->check('openid,msg_id',false);
		$res = $this->_msg_view($this->post);
        $this->apiReturn($res);		
	}
    public function _msg_view($param){
		$res = M('msg')->where(['to_uid'=>$this->user['id'],'id'=>$param['msg_id']])->find();
        if($res) {
			if($res['is_read'] == 0){
				M('msg')->where(['id'=>$param['msg_id']])->data(['is_read'=>1])->save();	
			}
            return ['code' => 1,'data' => $res];
        }
        return ['code' => 3,'msg' => '找不到记录！'];
	}	
	/**
     * subject: 删除消息
     * api: /Notice/msg_delete
     * author: liangfeng
     * day: 2017-05-13
     *
     * [字段名,类型,是否必传,说明]
	 * param: openid,string,1,用户openid
	 * param: msg_id,int,0,消息id
     */
    public function msg_delete(){
		$this->check('openid,msg_id',false);
		$res = $this->_msg_delete($this->post);
        $this->apiReturn($res);		
	}
    public function _msg_delete($param){
		$res = M('msg')->where(['to_uid'=>$this->user['id'],'id'=>$param['msg_id']])->delete();
        if($res) {
            return ['code' => 1];
        }
        return ['code' => 0];
	}	
	

	
}