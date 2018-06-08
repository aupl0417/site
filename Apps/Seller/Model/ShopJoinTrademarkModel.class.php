<?php
namespace Seller\Model;
use Think\Model;
class ShopJoinTrademarkModel extends Model {
    protected $tableName    =   'shop_join_cert';
    protected $_validate    =   [
        ['brand_id', 'require', '品牌不能为空', 1],
        ['brand_id', 'checkBrand', '品牌不正确', 1, 'callback'],
        ['brand_id', 'brandIsUse', '该品牌已添加资质', 1, 'callback', 1],
        ['reg_type', 'require', '注册类型不能为空', 1],
        ['reg_type', [1,2], '注册类型 不正确', 1, 'in'],
        ['reg_type', 'checkRegData', 'checkRegDataMsg', 1, 'callback'],
        //['reg_people', 'require', '商标注册人不能为空', 1],
        //['reg_no', 'require', '商标注册号不能为空', 1],
        //['apply_people', 'require', '商标申请人不能为空', 1],
        //['apply_no', 'require', '商标申请号不能为空', 1],
        ['reg_date', 'require', '商标申请时间不能为空', 1],
        ['is_import', 'require', '商标原产地不能为空', 1],
        ['is_import', [0,1],'商标原产地不正确', 1, 'in'],
        ['license_images', 'require', '商标证不能为空', 1],
        ['is_proxy', 'require', '是否为代理不能为空', 1],
        ['is_porxy', [0,1], '是否为代理不正确', 1, 'in'],
        ['proxy_cert', 'isProxy', '代理有效资格证不能为空', 1, 'callback'],
    ];
    
    
    protected $_auto    =   [
        ['ip', 'get_client_ip', 3, 'function'],
        ['uid', 'getUid', 1, 'function'],
    ];
    
    /**
     * 判断品牌是否为当前用户
     * @param string $var   判断的数据
     */
    protected function checkBrand($var) {
        if (M('shop_join_brand')->where(['uid' => getUid(), 'id' => $var])->find()) {
            return true;
        }
        return false;
    }
    
    
    /**
     * 判断当前品牌是否已添加资质
     * @param unknown $var
     * @return boolean
     */
    protected function brandIsUse($var) {
        if ($this->where(['uid' => getUid(), 'brand_id' => $var])->find()) {
            return false;
        }
        return true;
    }
    
    /**
     * 判断用户是否有选择必填项
     * @param integer $var
     */
    protected function checkRegData($var) {
        if ($var == 1 && (empty(I('post.reg_people')) || empty(I('post.reg_no')))) {
            return false;
        } elseif ($var == 2 && (empty(I('post.apply_people')) || empty(I('post.apply_no')))) {
            return false;
        }
        return true;
    }
    
    /**
     * 当为代理的时候，代理资格证为必填
     * @param unknown $var
     */
    protected function isProxy($var) {
        $proxy  =   I('post.is_proxy');
        if ($proxy == 1 && empty($var)) {
            return false;
        }
        return true;
    }
}