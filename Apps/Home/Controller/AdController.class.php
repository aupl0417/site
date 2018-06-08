<?php
namespace Home\Controller;
class AdController extends CommonController {
    
    protected $_positionData = [];
    protected $_id;
    protected $_type = [1 => '图片', 2 => '焦点图', 3 => '文本'];
    protected $_model;
    
    public function _initialize() {
        parent::_initialize();
        if (session('admin') == false) {
            echo '<div class="text-center">请登录雇员账号</div>';
            exit;
        }
        $this->_id = I('get.id', 0, 'int');
    }
    
    public function add() {
        $this->_model = D('AdPositionRelation');
        if (IS_POST) {
            $post = I('post.');
            $id = I('post.id', 0, 'int');
            $tmps = [];
            $info = M('ad_position')->where(['id' => $id])->field('width,height,background_width,background_height')->find();
            $imagesMsg = null;
            foreach ($post as $k => $v) {
                if (strpos($k, '__') !== false) {
                    $tmp = explode('__', $k);
                    $tmps[$tmp[1]][$tmp[0]] = $v;
                    $check[$tmp[1]]=$this->qn_imgsize($v,$info['width'],$info['height']);
                    if ($check[$tmp[1]] == false) {
                        $imagesMsg = '第' . ($tmp[1] + 1) . '图片尺寸不正确';
                        break;
                    }
                    if ($info['background_width'] > 0) {
                        $bcheck[$tmp[1]]=$this->qn_imgsize($v,$info['background_width'],$info['background_height']);
                        if ($bcheck[$tmp[1]] == false) {
                            $imagesMsg = '第' . ($tmp[1] + 1) . '背景图片尺寸不正确';
                            break;
                        }
                    }
                }
            }
            
            if (!is_null($imagesMsg)) {
                $this->ajaxReturn(['code' => 0, 'msg' => $imagesMsg]);
            }
            
            $data = $this->pareseData($post['ad']);
            if (!empty($tmps)) { //如果包含图片
                foreach ($data as $key => &$val) {
                    $data[$key] = array_merge($val, $tmps[$key]);
                }
            }
            $data['ad'] = $data;
            $data['etime'] = date('Y-m-d H:i:s', NOW_TIME);
            $this->_model->startTrans();
            if ($id > 0) {
                $flag = $this->_model->relation(true)->where(['id' => $id])->save($data);
            } else {
                $flag = $this->_model->relation(true)->add($data);
            }
            if ($flag) {
                $this->_model->commit();
                $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']);
            }
            $this->_model->rollback();
            $this->ajaxReturn(['code' => 0, 'msg' => '操作失败' . $flag]);
        } else {
            $data = $this->_model->relation(true)->where(['id' => $this->_id, 'status' => 1])->field('id,position_name,type,device,num,width,content,height,background_width,background_height,is_seat')->find();
            //重新排序，使用sort作为key
            $data['ad'] = array_reduce($data['ad'], function (&$ad, $val) {
                $ad[$val['sort']] = $val;
                return $ad;
            });
            $num = count($data['ad']);
            if ($num < $data['num']) {
                $sort = [];
                if ($num > 0) {
                    foreach ($data['ad'] as $v) {
                        $sort[] = $v['sort'];
                    }
                }
                for ($i=0;$i<$data['num'];$i++) {   //填充没有数据的广告位
                    if (!in_array($i, $sort)) {
                        $data['ad'][$i]['id']               = '';
                        $data['ad'][$i]['name']             = '';
                        $data['ad'][$i]['subcontent']       = '';
                        $data['ad'][$i]['sort']             = $i;
                        $data['ad'][$i]['images']           = '';
                        $data['ad'][$i]['background_images']= '';
                        $data['ad'][$i]['url']              = '';
                        $data['ad'][$i]['type']             = '';
                        $data['ad'][$i]['goods_id']         = '';
                        $data['ad'][$i]['shop_id']          = '';
                    }
                }
                ksort($data['ad']);
            }
            $data['type_name'] = $this->_type[$data['type']];
            $this->assign('data', $data);
            $this->display();
        }
    }
    
    public function edit() {
        //             $this->api('/Ad/ad', ['position_id' => $this->_id]);
        //             if ($this->_data['code'] != 1) return false;
        //             $data = $this->_data['data'];
        /*$builder = '$this->builderForm()->keyId(\'position_id\')';
         $f = 0;
         for ($i=0;$i<$data['num'];$i++) {
         $f++;
         $builder .= '->keySelect(\'name\', \'链接方式\', [1=>1,2=>2])->keyHtmltext(\'楼层位置\', '.$f.')->keyText(\'name['.$i.']\', \'广告标题\', 1)->keyTextArea(\'subcontent['.$i.']\', \'广告介绍\', 1)';
         if ($data['type'] < 3) {
         $builder .= '->keySingleImages(\'images__'.$i.'\', \'广告图片\', 1)';
         }
         if ($data['background_width'] > 0) {
         $builder .= '->keySingleImages(\'background_images__'.$i.'\', \'广告图片\', 1)';
         }
         $builder .= '->keyText(\'url['.$i.']\', \'广告链接\', 1)';
         }
         $builder .= '->data($data)->view();';*/
        //eval($builder);
    }
    
    /**
     * 获取商品或者商家数据
     */
    public function getData() {
        if (IS_POST) {
            $post = I('post.');
            $typeArr = [0,1];
            if (!in_array($post['type'], $typeArr)) $this->ajaxReturn(['code' => 0, 'msg' => '投放类型请选择商品或者商家!']);
            if ($post['type'] == 1) {
                $model = M('shop');
                $subName = '商家';
                $field = 'shop_name,about,domain';
                $url = DM('%s');
                $order = null;
                $map['id'] = $post['id'];
            } else {
                $model = D('AdGoodsAttrListView');
                $subName = '商品';
                $field = 'goods_name,sub_name,price,id';
                $url = DM('item') . '/goods/%s.html';
                $order = 'price asc';
                $map['goods_id'] = $post['id'];
            }
            $data = $model->where($map)->field($field)->order($order)->find();
            if ($data) {
                if ($post['type'] == 1) {
                    $data['url'] = sprintf($url, $data['domain'] ? : $post['id']);
                } else {
                    $data['url'] = sprintf($url, $data['id']);
                }
                $this->ajaxReturn(['code' => 1, 'msg' => '操作成功', 'data' => $data]);
            }
            $this->ajaxReturn(['code' => 0, 'msg' => '找不到记录']);
        }
    }
    
    /**
     * post数组解析
     * @param unknown $param
     */
    private function pareseData($param) {
        if (is_array($param)) {
            $data = [];
            $typeArr = [0 => 'goods_id', 1 => 'shop_id'];
            foreach ($param as $k => $v) {
                foreach ($v as $key => $val) {
                    if ($val != '') {
                        $data[$key][$k]             = $val;
                        $data[$key]['sort']         = $key;
                        $data[$key]['is_default']   = 1;
                    }
                }
            }
            unset($k,$key,$v,$val);
            foreach ($data as &$v) {
                if (array_key_exists($v['type'], $typeArr)) {
                    $v[$typeArr[$v['type']]] = $v['subject'];
                }
            }
            unset($v);
            return $data;
        }
    }
    
    /**
     * 检查七牛图片尺寸
     * @param string $_POST['img'] 图片url 
     * @param int    $_POST['width'] 宽度
     * @param int    $_POST['height'] 高度
     */
    private function qn_imgsize($img,$width,$height){
        $res=$this->curl_get($img.'?imageInfo');
        $res=json_decode($res);
        if($width==$res->width && $height==$res->height) return true;
        else return false;
    }
}