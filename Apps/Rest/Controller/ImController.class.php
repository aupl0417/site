<?php
namespace Rest\Controller;

class ImController extends CommonController
{


	/**
	 * 根据商品属性id 获取商品和卖家信息
	 * @param int $attr_id 商品属性id
	 */
	public function goodsMessage(){
		$this->need_param = array('attr_id');
		$this->_need_param();
		$this->_check_sign();

		$attr_id = I('attr_id', 0, 'int');
		$model = D('Common/GoodsAttrGoodsUserShopRelation');
		$one = $model->field('goods_id,id as goods_attr_id,images as goods_attr_images,seller_id')->relation(true)->cache(true,86400)->find($attr_id);

		if(isset($one['goods_attr_id'])){
			$this->apiReturn(1, ['data' => $one]);
		}else{
			$this->apiReturn(3,['DATA' => $_POST]);
		}

	}





















}
