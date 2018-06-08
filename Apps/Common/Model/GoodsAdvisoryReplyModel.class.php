<?php
/**
 * 商品咨询回复验证模型
 */
namespace Common\Model;
use Think\Model;
class GoodsAdvisoryReplyModel extends Model {
    protected $tableName = 'goods_advisory';
    
    protected $_validate = [
        ['reply_content', 'require', '回复内容不能为空', 1, 'regex', 2],
        ['reply_uid', 'require', '回复用户不能为空', 1, 'regex', 2],
        ['id', 'checkPermissions', '您没有回复当前咨询的权限', 1, 'callback', 2],
    ];
    
    protected $_auto = [
        ['reply_ip', 'get_client_ip', 2, 'function'],
        ['reply_time', 'getReplyTime', 2, 'callback'],
        ['status', 2, 2],
    ];
    
    
    /**
     * 时间转换
     */
    protected function getReplyTime() {
        return date('Y-m-d H:i:s', NOW_TIME);
    }
    
    /**
     * 判断当前用户是否可以回复当前咨询
     * @param integer $var
     */
    protected function checkPermissions($var) {
        $info = $this->where(['id' => $var])->find();
        if (M('shop')->where(['id' => $info['shop_id'], 'uid' => I('post.reply_uid')])->getField('id')) {
            return true;
        }
        return false;
    }
}