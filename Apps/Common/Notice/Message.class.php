<?php
/**
 * 短信通知
 */
namespace Common\Notice;
class Message extends Notice {
    function __construct($receive, $tplId, $subject = '') {
        if (is_array($tplId)) {
            $this->_message = $tplId['content'];
        } else {
            $this->_tplId = $tplId;
            if (!empty($subject)) $this->_message = $this->getContent();
        }
        parent::__construct($receive, $tplId, $subject);
    }
    
    /**
     * 发送短信(non-PHPdoc)
     * @see \Common\Notice\Notice::send()
     */
    public function send() {
        $flag = sms_send(['mobile' => $this->_receive, 'content' => trim($this->_message, ' ')]);
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
     * 获取msg内容
     */
    protected function getContent() {
        if (!empty($this->_tplId)) {
            return M('sms_tpl')->cache(true)->where(['id' => $this->_tplId])->getField('tpl_content');
        }
        return false;
    }
}