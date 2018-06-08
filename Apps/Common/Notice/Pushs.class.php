<?php
/**
 * 客户端推送通知(android、IOS、windows)
 */
namespace Common\Notice;
use Common\Notice\Common;
class Pushs extends Notice {
    //protected $_type;   //类型1.IOS，2.android
    function __construct($receive, $tplId, $subject) {
        if (is_array($tplId)) {
            $this->_message = $tplId;
        } else {
            $this->_tplId = $tplId;
            if (!empty($subject)) $this->_message = $this->getContent();
        }
        parent::__construct($receive, $tplId, $subject);
    }
    
    /**
     * 获取实例
     * @param number $devicerType 1.ios 2.android
     * @return SingleServices
     */
    protected function getInstance($devicerType = 2) {
        require_once VENDOR_PATH . 'XgPush/SingleServices.php';
        if ($devicerType == 1) {
            return \SingleServices::GIIos();
        } else {
            return \SingleServices::GIAndriod();
        }
    }
    
    /**
     * 发送(non-PHPdoc)
     * @see \Common\Notice\Notice::send()
     */
    public function send() {
        $device = $this->getUserToken();
        $flag = true;
        $tmp = [];
        $msg = '发送成功';
        log_add('pushs', ['device' => $device, 'receive' => $this->_receive]);
        writeLog($this->_message);
        if (!empty($device)) {
            foreach ($device as $k => $v) {
                $instance = $this->getInstance($v);
                if ($v == 1) {  //ios设备
                    $tmp[$k] = $instance->toPushSingleDeviceIOS($k, $this->_message['content']);
                } elseif ($v == 2) {    //安卓设备
                    $tmp[$k] = $instance->toPushSingleDeviceNotification($k, $this->_message['title'], $this->_message['content']);
                }
                if ($tmp[$k]['ret_code'] != 0) {
                    $flag = false;
                    $msg .= serialize($tmp[$k]) . "\n";
                }
                log_add('pushs', ['res' => $tmp[$k], 'dotime' => date('Y-m-d H:i:s', NOW_TIME), 'device' => $v, 'msg' => $this->_message]);
            }
            $msg .= serialize($tmp);
            if ($tmp['ret_code'] != 0) $flag = false;
        }else{
			$flag = false;
			$msg = '没有此设备';
		}
        if ($flag == false) {
            return ['code' => 0, 'msg' => $msg];
        }
        return ['code' => 1, 'msg' => $msg];
    }
    
    /**
     * 发送至所有设备
     * type 1 IOS， 2 android， null所有
     */
    public function sendAll($type = null) {
        $flag = true;
        $ret = [];
        $msg = '发送成功';
        switch ($type) {
            case 1: //IOS设备
                $ret[1] = $this->getInstance(1)->toPushAllDeviceNotificationIOS($this->_message['content']);  //1为IOS
                if ($ret[1]['ret_code'] != 0) {
                    $flag = false;
                    $msg  = serialize($ret[1]);
                }
                break;
            case 2: //android设备
                $ret[2] = $this->getInstance()->toPushAllDeviceNotification($this->_message['title'], $this->_message['content']);   //默认为安卓
                if ($ret[2]['ret_code'] != 0) {
                    $flag = false;
                    $msg = serialize($ret[2]);
                }
                break;
            default: //所有设备
                $ret[2] = $this->getInstance()->toPushAllDeviceNotification($this->_message['title'], $this->_message['content']);   //默认为安卓
                if ($ret[2]['ret_code'] != 0) {
                    $flag = false;
                    $msg = serialize($ret[2]);
                }
                $ret[1] = $this->getInstance(1)->toPushAllDeviceNotificationIOS($this->_message['content']);  //1为IOS
                if ($ret[1]['ret_code'] != 0) {
                    $flag = false;
                    $msg .= serialize($ret[1]);
                }
        }
        
        if ($flag == false) {
            return ['code' => 0, 'msg' => $msg];
        }
        return ['code' => 1, 'msg' => $msg];
    }
    
    /**
     * 获取msg内容
     */
    protected function getContent() {
        if (!empty($this->_tplId)) {
            return M('msg_tpl')->cache(true)->where(['id' => $this->_tplId])->field('tpl_content,title')->find();
        }
        return false;
    }
    
    /**
     * 获取当前用户所有的token
     */
    protected function getUserToken() {
        $device = [];
        if ($this->_receive) {
            $device = M('user_device')->cache(true)->where(['uid' => $this->_receive])->getField('token,type', true);
			//如果不存在，从erp获取token
			if(!$device){
//				$erp_uid = M('user')->where(['id' => $this->_receive])->getField('erp_uid');
//				$res = (new Common())->doApi('/Erp/user_token',['erp_uid' => $erp_uid]);
//
//				if($res->code == 1){
//					$data['token'] = $res->data->token;
//					$data['type'] = $res->data->type;
//					$data['uid'] = $this->_receive;
//					M('user_device')->data($data)->add();
//					$device[$res->data->token] = $res->data->type;
//
//				}
			}
        }
        return $device;
    }
}