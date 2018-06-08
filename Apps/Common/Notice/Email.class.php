<?php
/**
 * 邮件通知
 */
namespace Common\Notice;
class Email extends Notice {
    
    function __construct($receive, $tplId, $subject = '') {
        if (is_array($tplId)) {
            $this->_message = $tplId;
        } else {
            $this->_tplId = $tplId;
            if (!empty($subject)) $this->_message = $this->getContent();
        }
        parent::__construct($receive, $tplId, $subject);
    }
    
    /**
     * 发送(non-PHPdoc)
     * @see \Common\Notice\Notice::send()
     */
    public function send() {
        $user = $this->getUserInfo('nick');    //获取用户信息
        if ($user == false) $user['nick'] = '尊敬的乐兑用户';
        $flag = sendmail(['to' => $this->_receive, 'subject' => trim($this->_message['title'], ' '), 'body' => trim($this->_message['content'], ' '), 'to_name' => $user['nick']]);
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
            return M('email_tpl')->cache(true)->where(['id' => $this->_tplId])->field('tpl_content,title')->find();
        }
        return false;
    }
}