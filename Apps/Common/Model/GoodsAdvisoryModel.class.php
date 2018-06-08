<?php
/**
 * 商品咨询认证模型
 */
namespace Common\Model;
use Think\Model;
class GoodsAdvisoryModel extends Model {
    protected $tableName = 'goods_advisory';
    
    protected $_validate = [
        ['goods_id', 'require', '商品不能为空', 1],
        ['shop_id', 'require', '商家不能为空', 1],
        ['attr_list_id', 'require', '商品属性不能为空', 1],
        ['content', 'require', '咨询内容不能为空', 1],
        ['uid', 'require', '咨询用户不能为空', 1],
        ['sid', 'require', '咨询类型不能为空', 1],
        ['sid', 'inAdvisoryType', '咨询类型不正确', 1, 'callback'],
    ];
    
    protected $_auto = [
        ['ip', 'get_client_ip', 1, 'function']
    ];
    
    /**
     * 判断咨询类型是否正确
     */
    protected function inAdvisoryType($var) {
        if (M('goods_advisory_category')->where(['id' => $var, 'status' => 1])->getField('id')) {
            return true;
        }
        return false;
    }
}