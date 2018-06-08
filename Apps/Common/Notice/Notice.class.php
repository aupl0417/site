<?php
/**
 * 消息推送基类
 */
namespace Common\Notice;
abstract class Notice {
    protected $_receive;                        //接收对象
    protected $_message;                        //要推送的消息
    protected $_tplId;                          //模板ID
    protected $_boundaryLeft    =   '{';        //左边界
    protected $_boundaryRight   =   '}';        //右边界
    protected $_replace         =   [];         //替换的字符串
    protected $_replaceTo       =   [];         //替换后的字符串
    protected $_subject         =   [];         //用于存储替换信息的数组 格式为['nick' => 'test', 'shop_name' => 'test_shop_name', 'goods_name' => 'test_goods_name'];
    protected $_map             =   [];         //用户获取用户信息的条件
    /**
     * 
     * @param integer $uid
     * @param integer $tplId
     * @param array $subject
     */
    function __construct($uid, $tplId, $subject = '') {
        $this->_receive = $uid;
        $this->_subject = $subject;
        if (!empty($this->_message)) {    //如果是数组则解析message
            $this->getNoticeMessage();    //消息模板
        }
    }
    
    /**
     * 推送
     */
    abstract function send();
    
    /**
     * 获取短信内容
     */
    protected function getNoticeMessage() {
        $this->getReplaceKey(); //设置需要替换的数组
        $this->_replaceTo = array_values($this->_subject);  //获取替换后的值
        if (is_array($this->_message)) {
            $this->_message['content'] = str_replace($this->_replace, $this->_replaceTo, html_entity_decode($this->_message['tpl_content'] ? : $this->_message['content']));
        } else {
            $this->_message = str_replace($this->_replace, $this->_replaceTo, html_entity_decode($this->_message));
        }
    }
    
    /**
     * 为key加上左右边界
     */
    protected function getReplaceKey() {
        $this->_replace = array_keys($this->_subject);
        foreach ($this->_replace as &$v) {
            $v = $this->_boundaryLeft . $v . $this->_boundaryRight;
        }
        unset($v);
    }
    
    /**
     * 获取用户信息
     */
    protected function getUserInfo($field = true) {
        //$map = $this->getNoticeType();
        if (!empty($this->_map)) { 
            return M('user')->cache(true)->where($this->_map)->field($field)->find();
        }
        return false;
    }
    
    /**
     * 获取推送类型
     */
    protected function getNoticeType() {
        $type = strtolower(get_class($this));
        if (strpos($type, '\\') !== false) {
            $tmp = explode('\\', $type);
            $type = end($tmp);
            unset($tmp);
        }
        $map = [];
        switch ($type) {
            case 'system':
                $map['id'] = $this->_receive;
                break;
            case 'email':
                $map['email'] = $this->_receive;
                break;
            case 'message':
                $map['mobile'] = $this->_receive;
                break;
            case 'push' :
                $map['device_id'] = $this->_receive;
                break;
        }
        return $map;
    }
    
    /**
     * 数据返回
     * @param boolean $flag
     */
    protected function returnData($flag) {
        if ($flag == true) return ['code' => 1, 'msg' => '发送成功'];
        return ['code' => 0, 'msg' => '发送失败'];
    }
    
    public function __get($name) {
        if (isset($this->$name)) {
            return $this->$name;
        }
        return null;
    }
    
    public function __set($name, $val) {
        $this->$name = $val;
    }
    
    function __destruct() {
        if (!empty($this->_subject)) {
            $this->_subject = [];
        }
        $this->_receive = null;
        $this->_tplId = null;
        $this->_message = null;
    }
}