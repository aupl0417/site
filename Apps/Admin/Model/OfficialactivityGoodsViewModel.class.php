<?php
namespace Admin\Model;
use Think\Model\ViewModel;
class OfficialactivityGoodsViewModel extends ViewModel {
    public $viewFields = array(
        'officialactivity_floor_goods'  => array('join_id'),
        'officialactivity_join'         => array('goods_id','price', '_on'=>'officialactivity_floor_goods.activity_id=officialactivity_join.activity_id'),
    );
}