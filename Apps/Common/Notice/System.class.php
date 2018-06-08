<?php
/**
 * 系统通知
 */
namespace Common\Notice;
class System extends Notice {
    private $_r_id; 
    private $_category_id; 
	private $title;
	
    function __construct($receive, $tplId, $subject = '',$category_id=1,$r_id=0) {
        if (is_array($tplId)) {
            $this->_message = $tplId['content'];
        } else {
            $this->_tplId   = $tplId;
            if (!empty($subject)) $this->_message = $this->getContent();
        }
		$this->_category_id = $category_id;
		$this->_r_id = $r_id;
        parent::__construct($receive, $tplId, $subject);
    }
    
    /**
     * 发送消息(non-PHPdoc)
     * @see \Common\Notice\Notice::push()
     */
    public function send() {
        $data['to_uid']     = $this->_receive;
		$data['to_nick'] = $this->_subject['nick'];
		$data['from_uid'] = 0;
		$data['from_nick'] = '系统';
		$data['r_id'] = $this->_r_id;
		$data['category_id'] = $this->_category_id;
        //if ($this->_tplId) $data['tpl_id']  = $this->_tplId;
        $data['content'] = trim(htmlspecialchars($this->_message), ' ');
		$data['title'] = empty($this->title) ? mb_substr(strip_tags($data['content']),0,20,'utf-8') : $this->title;
        $model = D('Msg');
        if(!$model->create($data)) {
            return ['code' => 0, 'msg' => $model->getError()];
        }
        $flag = $model->add();
        return $this->returnData($flag);
    }
    
    /**
     * 发送所有，需要使用队列
     */
    public function sendAll() {
        /**
         * to do queue code ...
         */
    }
    
    /**
     * 阅读站内信
     * @return Ambigous <mixed, boolean, NULL, string, unknown, multitype:, object>|boolean
     */
    public function read() {
        $map = [
            'to_uid' => $this->_receive,
            'id'  => $this->_tplId,
        ];
        $model = M('msg');
        $data = $model->cache(true)->where($map)->find();
        if ($data) {
            if ($data['is_read'] == 0) {
                $sData['is_read'] = 1;
                $sData['rtime'] = date('Y-m-d H:i:s', NOW_TIME);
                $map['is_read'] = 0;
                $model->where($map)->save($sData);
            }
            $data['content'] = html_entity_decode($data['content']);
            return $data;
        }
        return false;
    }
    
    /**
     * 获取msg内容
     */
    protected function getContent() {
        if (!empty($this->_tplId)) {
			$res = M('msg_tpl')->field('title,tpl_content')->cache(true)->where(['id' => $this->_tplId])->find();
			$this->title = $res['title'];
            return $res['tpl_content'];
			
        }
        return false;
    }
}