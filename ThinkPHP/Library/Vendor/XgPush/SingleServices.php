<?php

/**
 * XingGe IOS ANDROID 公共类
 * @author Dolen
 */
require_once ('XingeApp.php');
class SingleServices {

    //单例对象
    public static $_instance_ios;
    public static $_instance;
    //集合对象
    public $_push;
    public $_mess;

    /**
     * 构造函数
     */
    public function __construct($access_id, $secret_key, $plat) {
        $this->_push = new XingeApp($access_id, $secret_key);
        if ($plat == 1) {
            $this->_mess = new MessageIOS();
        } else {
            $this->_mess = new Message();
        }
    }

    //单例Android
    public static function GIAndriod() {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self(2100253782, '8727973d0960817e1ea004352f274a15', 2);
        }
        return self::$_instance;
    }

    //单例IOS
    public static function GIIos() {
        if (!(self::$_instance_ios instanceof self)) {
            self::$_instance_ios = new self(2200253783, 'f5003c601bcfa403a1d9c982271a65e9', 1);
        }
        return self::$_instance_ios;
    }

    /**
     * 下发消息到单个设备
     * @param type $token 设备token
     * @param type $title 标题
     * @param type $content 内容
     * @return type
     */
    public function toPushSingleDevice($token, $title, $content) {
        $this->_mess->setTitle($title);
        $this->_mess->setContent($content);
        $this->_mess->setType(\Message::TYPE_MESSAGE);
        $ret = $this->_push->PushSingleDevice($token, $this->_mess);
        return $ret;
    }

    /**
     * 下发消息到所有设备
     * @param type $title 标题
     * @param type $content 内容
     * @param type $time 离线存储时间
     * @return type
     */
    public function toPushAllDevices($title, $content, $time = 0) {
        $this->_mess->setTitle($title);
        $this->_mess->setContent($content);
        $this->_mess->setExpireTime($time);
        $this->_mess->setType(Message::TYPE_MESSAGE);
        $ret = $this->_push->PushAllDevices(0, $this->_mess);
        return $ret;
    }

    /**
     * 下发通知到单个设备
     * @param type $token 设备token
     * @param type $title 标题
     * @param type $content 内容
     * @param type $time 离线存储时间
     * @param type $url 点击打开的url
     * @param type $custom 自定义的key-value数组，格式为 array('key1'=>'value1', 'key2'=>'value2')
     * @return type
     */
    public function toPushSingleDeviceNotification($token, $title, $content, $time = 0, $url = '', $custom = array()) {

        $this->_mess->setType(\Message::TYPE_NOTIFICATION);
        $this->_mess->setTitle($title);
        $this->_mess->setContent($content);
        $this->_mess->setExpireTime($time);
        //$style = new Style(0);
        #含义：样式编号0，响铃，震动，不可从通知栏清除，不影响先前通知
        $style = new Style(0, 1, 1, 0, 0);
        $action = new ClickAction();
        if ($url != '') {
            $action->setActionType(ClickAction::TYPE_URL);
            $action->setUrl($url);
            $action->setComfirmOnUrl(1);
        }
        $this->_mess->setAction($action);
        $this->_mess->setStyle($style);
        $this->_mess->setCustom($custom);
        $acceptTime1 = new \TimeInterval(0, 0, 23, 59);
        $this->_mess->addAcceptTime($acceptTime1);
        $ret = $this->_push->PushSingleDevice($token, $this->_mess);
        return $ret;
    }

    /**
     * 下发通知到所有设备
     * @param type $title 标题
     * @param type $content 内容
     * @param type $time 离线存储时间
     * @param type $url 点击打开的url
     * @param type $custom 自定义的key-value数组，格式为 array('key1'=>'value1', 'key2'=>'value2')
     * @return type
     */
    public function toPushAllDeviceNotification($title, $content, $time = 0, $url = '', $custom = array()) {

        $this->_mess->setType(Message::TYPE_NOTIFICATION);
        $this->_mess->setTitle($title);
        $this->_mess->setContent($content);
        $this->_mess->setExpireTime($time);
        $style = new Style(0, 1, 1, 0, 0);
        $action = new ClickAction();
        if ($url != '') {
            $action->setActionType(ClickAction::TYPE_URL);
            $action->setUrl($url);
            $action->setComfirmOnUrl(1);
        }
        $this->_mess->setAction($action);
        $this->_mess->setStyle($style);
        $this->_mess->setCustom($custom);
        $acceptTime1 = new \TimeInterval(0, 0, 23, 59);
        $this->_mess->addAcceptTime($acceptTime1);
        $ret = $this->_push->PushAllDevices(0, $this->_mess);
        return $ret;
    }

    /**
     * 下发消息到单个IOS设备
     * @param type $token 设备token
     * @param type $message 消息，可以是字符串或数组【array('key1'=>'value1')】
     * @param type $custom 自定义的key-value数组，格式为 array('key1'=>'value1', 'key2'=>'value2')
     * @param type $time 离线存储时间
     * @return type
     */
    public function toPushSingleDeviceIOS($token, $message, $custom = array(), $time = 0) {

        $this->_mess->setExpireTime($time);
        //$mess->setSendTime("2014-03-13 16:00:00");
        $this->_mess->setAlert($message);
        $this->_mess->setBadge(1);
        $this->_mess->setSound("beep.wav");

        $this->_mess->setCustom($custom);
        $acceptTime = new TimeInterval(0, 0, 23, 59);
        $this->_mess->addAcceptTime($acceptTime);
        $ret = $this->_push->PushSingleDevice($token, $this->_mess, XingeApp::IOSENV_PROD);
        return $ret;
    }

    /**
     * 下发消息到所有IOS设备
     * @param type $message 消息，可以是字符串或数组【array('key1'=>'value1')】
     * @param type $custom 自定义的key-value数组，格式为 array('key1'=>'value1', 'key2'=>'value2')
     * @param type $time 离线存储时间
     * @return type
     */
    public function toPushAllDeviceNotificationIOS($message, $custom = array(), $time = 0) {

        $this->_mess->setExpireTime($time);
        $this->_mess->setAlert($message);
        $this->_mess->setBadge(1);
        $this->_mess->setSound("beep.wav");
        $this->_mess->setCustom($custom);
        $acceptTime = new TimeInterval(0, 0, 23, 59);
        $this->_mess->addAcceptTime($acceptTime);
        $ret = $this->_push->PushAllDevices(0, $this->_mess, XingeApp::IOSENV_PROD);
        return $ret;
    }

    //查询设备数量
    public function toQueryDeviceCount() {
        $ret = $this->_push->QueryDeviceCount();
        return $ret;
    }

    /**
     * 查询消息推送状态
     * @param type $pushIdList 消息id队列
     * @return type
     */
    public function toQueryPushStatus($pushIdList) {
        if (!is_array($pushIdList)) {
            $pushIdList = explode(',', $pushIdList);
        }
        $ret = $this->_push->QueryPushStatus($pushIdList);
        return $ret;
    }

}
