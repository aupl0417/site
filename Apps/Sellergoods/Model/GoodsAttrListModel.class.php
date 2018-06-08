<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/4
 * Time: 9:31
 */

namespace Sellergoods\Model;


use Think\Model;

class GoodsAttrListModel extends Model
{
    protected $tableName='goods_attr_list';
    protected $_validate = array(
        //array('goods_id','require','商品ID不能为空!',1,'regex',3),
        array('attr','require','库存属性值不能为空!',1,'regex',3),
        array('attr_name','require','库存属性名称不能为空!',1,'regex',3),
        array('attr_id','require','库存属性ID不能为空!',1,'regex',3),
        array('num','checkform','库存数量必须大于0',1,'function',3,array('egt')),
        array('price','checkform','价格不得小于0.1元',1,'function',3,array('egt',0.1)),
        array('price_market', 'require', '市场价不能为空', 1),
        array('price', 'checkPrice', '销售价不能大于市场价', 1, 'callback'),

    );

    protected $_auto = array (
        array('ip','get_client_ip',3,'function'),
        array('seller_id', 'getSellerId', 3, 'callback'),
    );

    /**
     * 获取买家ID
     *
     * @return mixed
     */
    public function getSellerId($data) {
		if($data){
			return $data;
		}else{
			return session('user.id');
		}
        
    }

    /**
     * 检测销售价是否大于市场价
     *
     * @param $var
     */
    protected function checkPrice($var) {
        if ($var > $data['price_market']);
    }
	
	
	
}