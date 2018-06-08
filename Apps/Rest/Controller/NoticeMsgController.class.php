<?php
namespace Rest\Controller;
use Common\Notice\System;
class NoticeMsgController extends CommonController {
    
    /**
     * 消息列表
     */
    public function index() {
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
        /*
        $map['uid']=$this->uid;
        $statusArr = ['未阅读', '已阅读'];
        if (isset($_POST['status']) && array_key_exists(I('post.status'), $statusArr))  {
            $map['is_read'] = I('post.status');
        } else {
            $map['is_read'] = ['in', '0,1'];
        }
        
        $list = pagelist([
            'do'        => 'M',
            'table'     => 'message_notice',
            'map'       => $map,
            'pagesize'  => 10,
            'p'         => I('post.p', 1, 'int'),
            'action'    => I('post.action'),
            'order'     => 'id desc',
            //'cache_name'=> md5(implode(',', I('post.')) . ACTION_NAME),
        ]);
        if (!empty($list['list'])) {
            foreach ($list['list'] as &$v) {
                $v['status_name'] = $statusArr[$v['is_read']];
                $v['content'] = html_entity_decode($v['content']);
            }
            unset($v);
            $this->apiReturn(1, ['data' => $list], 1, '操作成功');
        }
        $this->apiReturn(3);
		*/
		$map['to_uid']=$this->uid;
        $statusArr = ['未阅读', '已阅读'];
        if (isset($_POST['status']) && array_key_exists(I('post.status'), $statusArr))  {
            $map['is_read'] = I('post.status');
        } else {
            $map['is_read'] = ['in', '0,1'];
        }
        
        $list = pagelist([
            'do'        => 'M',
            'table'     => 'msg',
            'map'       => $map,
            'pagesize'  => 10,
            'p'         => I('post.p', 1, 'int'),
            'action'    => I('post.action'),
            'order'     => 'id desc',
            //'cache_name'=> md5(implode(',', I('post.')) . ACTION_NAME),
        ]);
        if (!empty($list['list'])) {
            foreach ($list['list'] as &$v) {
                $v['status_name'] = $statusArr[$v['is_read']];
                $v['content'] = html_entity_decode($v['content']);
            }
            unset($v);
            $this->apiReturn(1, ['data' => $list], 1, '操作成功');
        }
        $this->apiReturn(3);
    }
    
    /**
     * 消息阅读
     */
    public function read() {
        //必传参数检查
        $this->need_param=array('openid','sign', 'id');
        $this->_need_param();
        $this->_check_sign();
		
        $data = (new System($this->uid, I('post.id', 0, 'int')))->read();
        if ($data == false) {
            $this->apiReturn(3);
        }
        $this->apiReturn(1, ['data' => $data]);
		
    }
    
    /**
     * 设为已阅读状态
     */
    public function saveRead() {
        $this->need_param=array('openid','sign', 'id');
        $this->_need_param();
        $this->_check_sign();
		
		$res = M('msg')->where(['to_uid'=>$this->uid,'id'=>I('post.id')])->save(['is_read'=>1,'rtime' => date('Y-m-d H:i:s', NOW_TIME)]);
		if ($res) {
			$cacheName = md5('Notice_count_' . $param['openid']);
            S($cacheName, null);
            return ['code' => 1];
        }
        return ['code' => 0];
		
		/*
        $map['uid']=$this->uid;
        $map['id'] = I('post.id');
        if(M('message_notice')->where($map)->save(['is_read' => 1, 'rtime' => date('Y-m-d H:i:s', NOW_TIME)])) {
            $cacheName = md5('Notice_count_' . I('post.openid'));
            S($cacheName, null);
            $this->apiReturn(1);
        }
        $this->apiReturn(0);
		*/
    }
    
    /**
     * 删除消息
     */
    public function del() {
        //必传参数检查
        $this->need_param=array('openid','sign', 'id');
        $this->_need_param();
        $this->_check_sign();
		
		$res = M('msg')->where(['to_uid'=>$this->uid,'id'=>I('post.id')])->delete();
        if ($res == false) {
            $this->apiReturn(3);
        }
        $this->apiReturn(1, ['data' => $res]);
		/*
        $map['uid']=$this->uid;
        $map['id'] = I('post.id');
        $flag = M('message_notice')->where($map)->save(['is_read' => 2]);
        if ($flag) {
            $this->apiReturn(1);
        }
        $this->apiReturn(0);
		*/
    }
    
    /**
     * 统计
     */
    public function count() {
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
		
		$map['to_uid']     = $this->uid;
        $map['is_read']  = 0;
        $cacheName = md5('Notice_count_' . I('post.openid'));
        $count = S($cacheName);
        if ($count == false) {
            $count = M('msg')->where($map)->count();
            if ($count > 99) $count = 99;   //最多为99
            S($cacheName, $count);
        }
        $this->apiReturn(1, ['data' => ['count' => $count]]);
		/*
        $map['uid']     = $this->uid;
        $map['status']  = 0;
        $cacheName = md5('Notice_count_' . I('post.openid'));
        $count = S($cacheName);
        if ($count == false) {
            $count = M('message_notice')->where($map)->count();
            if ($count > 99) $count = 99;   //最多为99
            S($cacheName, $count);
        }
        $this->apiReturn(1, ['data' => ['count' => $count]]);
		*/
    }

    /**
     * 清空所有消息
     */
    public function clear() {
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
        $map['to_uid']     = $this->uid;
        if (M('msg')->where($map)->delete()) $this->apiReturn(1);
        $this->apiReturn(0);
    }
}