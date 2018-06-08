<?php
namespace Home\Controller;
class SalesController extends CommonController{
    public function _initialize() {
        parent::_initialize();
    }
    
    
    public function act() {
        C('VIEW_PATH', './Theme/');
        C('DEFAULT_THEME', '');
        $id = think_decrypt(I('get.id'), C('CRYPT_PREFIX'));
        //dump(think_encrypt(I('get.id'), C('CRYPT_PREFIX')));
        //exit;MDAwMDAwMDAwMH6edZ8
        $salesCahe = S('CACHE_SALES_' . $id);
        if (!$salesCahe) {
            $model = D('Sales');
            $map = array(
                'id' => $id,
                'active' => 1,
                'activity_start_time' => array('lt', NOW_TIME),
                'activity_end_time' => array('gt', NOW_TIME),
            );
            $data = $model->where($map)->relation(true)->find();
            if (!$data) {
                $this->redirect('Empty/404');exit;
            }
            foreach ($data['SalesFloor'] as &$v) {
                foreach ($data['SalesSign'] as &$va) {
                    if ($v['condition_id'] == $va['cid']) {
                        $va['name'] = $this->getGoodsName($va['goods_id']);
                        $va['goods_price'] = $this->getGoodsPrice($va['goods_id']);
                        $va['id'] = think_encrypt($va['id'], C('CRYPT_PREFIX'));
                        $v['child'][] = $va;
                    }
                }
            }
            unset($v,$va,$data['SalesSign']);
            S('CACHE_SALES_' . $id, serialize($data), array('expire' => ($data['activity_end_time'] - $data['activity_start_time'])));
        } else {
            $data = unserialize($salesCahe);
        }
        M('sales')->where(array('id' => $id))->setInc('view', 1, 600);
        $this->assign('title', $data['name']);
        $this->assign('desc', $data['content']);
        $this->assign('top_images', $data['top_images']);
        $this->assign('menu_images', $data['menu_images']);
        $this->assign('backgroundcolor', $data['backgroundcolor']);
        $this->assign('backgroundimages', $data['backgroundimages']);
        $this->assign('list', $data['SalesFloor']);
        $tpl = !empty($data['template']) ? $data['template'] : 'act';
        $this->display($tpl);
    }
    
    /**
     * 增加点击次数
     */
    public function click() {
        $id = think_decrypt(I('get.id'), C('CRYPT_PREFIX'));
        if ($id > 0) {
            $cacheData = S('CACHE_SALES_SIGN_VIEW_' . $id);
            if (!$cacheData) {
                $sginInfo = D('SalesSignView')->where(array('id' => $id, 'active' => 1))->find();
                S('CACHE_SALES_SIGN_VIEW_' . $id, serialize($sginInfo), array('expire' => ($sginInfo['activity_end_time'] - $sginInfo['activity_start_time'])));
            } else {
                $sginInfo = unserialize($cacheData);
            }
            
            if($sginInfo) {
                $url = '';
                switch ($sginInfo['ads_content']) {
                    case 'goods' :
                        $url = C('sub_domain.detail') . '/item/' . $sginInfo['goods_id'] . '?spm=' . md5(NOW_TIME . $sginInfo['goods_id']);
                        break;
                    case 'shop' :
                        $url = user_domain($sginInfo['seller_id']);
                        break;
                    case 'brand' :
                        $url = C('sub_domain.detail') . '/item/' . $sginInfo['goods_id'] . '?spm=' . md5(NOW_TIME . $sginInfo['goods_id']);
                        break;
                    case 'ads' :
                        $url = C('sub_domain.www') . '/sales/act/id/'.think_encrypt($sginInfo['goods_id'], C('CRYPT_PREFIX'));
                        break;
                    default:
                }
                M('sales_sign')->where(array('id' => $id))->setInc('click', 1, 600);
                redirect($url);
            } else {
                $this->redirect('index' , array('spm' => md5(NOW_TIME)));
            }
        } else {
            $this->redirect('index' , array('spm' => md5(NOW_TIME . $sginInfo['goods_id'])));
        }
    }
    
    /**
     * 获取商品名称
     * @param unknown $goodsId
     * @return Ambigous <mixed, NULL, unknown, multitype:Ambigous <string, unknown> unknown , object>
     */
    private function getGoodsName($goodsId) {
        return M('products')->where(array('id' => $goodsId))->getField('name');
    }
    
    private function getConditionsName($id) {
        return M('test_conditions')->where(array('id' => $id))->getField('ads_name');
    }
    
    /**
     * 获取分类名称
     * @param unknown $sid
     */
    private function getCateName($sid) {
        return M('products_sort')->where(array('id' => $sid))->getField('name');
    }
    
    /**
     * 获取商品价格
     * @param unknown $goodsId
     * @return Ambigous <mixed, NULL, unknown, multitype:Ambigous <string, unknown> unknown , object>
     */
    private function getGoodsPrice($goodsId) {
        return M('products')->where(array('id' => $goodsId))->getField('price');
    }
}