<?php
namespace Common\Builder;
class Daigou {
    protected $default_max_cost;        //最大手续费
    protected $default_min_cost;        //最低手续费 
    protected $default_ratio;           //默认
    protected $_params = [];            //参数
    
    function __construct($params = []) {
        if (!empty($params)) {
            $this->_params = $params;
        }
        $cfg = getSiteConfig('daigou');
        $this->default_max_cost = $cfg['daigou_max_cost'];
        $this->default_ratio    = $cfg['daigou_cost_ratio'];
        $this->default_min_cost = $cfg['daigou_min_cost'];
    }
    
    /**
     * 计算
     * @param unknown $param
     */
    public function compute($param) {
        $this->getRatio($param); //获取手续费及手续百分比
        $res['daigou_ratio']        =   round($this->default_ratio, 2);       //手续费百分比
        $res['daigou_cost']         =   round($this->default_max_cost, 2);    //手续费
        //$res['goods_price_edit']    =   round($param['goods_price'] + $this->default_max_cost, 2);  //修改后的商品价格
        //$res['total_price_edit']    =   round($res['goods_price_edit'], 2);   //订单商品表
        return $res;
    }
    
    /**
     * 获取当前订单手续费
     * @param unknown $param
     */
    public function getRatio($param) {
        //取得商品的代购手续费百分比
        if ($param['daigou_ratio'] > 0) {   //如果存在单独设置的百分比则重新设置默认百分比
            $this->default_ratio = $param['daigou_ratio'];
        }
        $cost = (round($this->default_ratio * $param['total_price'], 2));   //计算手续费
        if ($cost < $this->default_min_cost) {  //如果手续费小于最低手续费时，则以最低为准
            $this->default_max_cost = $this->default_min_cost;
        } else if ($cost < $this->default_max_cost) { //如果计算出来的手续费小于最大默认值，则修改默认值为当前手续费
            $this->default_max_cost = $cost;
        }
    }
    
    /**
     * 获取预计价格
     * @param unknown $param
     */
    public function getCostPrice($price) {
        return number_format($price + ($this->default_ratio * $price), 2);
    }
}