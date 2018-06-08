<?php
namespace Admin\Controller;
use Xs\xs;
use Xs\XSDocument;
class SearchController extends CommonController {
    protected $xs;
    protected $s;
    protected $index;
    protected $type = [
        'goods',
        'shop',
        'demo',
    ];
    public function _initialize() {
        parent::_initialize();
        import('Vendor.Xs.xs');
        if (IS_POST) {
            $pro = I('post.pro');
        } elseif (IS_GET) {
            $pro = I('get.pro');
        }
        $pro = $pro ? : 'goods';
        $this->xs = new xs($pro);
        $this->s = $this->xs->search;
        $this->index = $this->xs->index;
    }
    
    /**
     * 
     */
    public function index() {
        $this->display();
    }
    
    /**
     * 清除索引
     * @param unknown $param
     */
    public function clean() {
        if (IS_POST) {
            $type = I('post.pro');
            if (!in_array($type, $this->type)) {
                $this->ajaxReturn(['code' => 'warning', 'msg' => '类型错误']);
            }
            $this->index->clean();
            $this->ajaxReturn(['code' => 'success', 'msg' => '操作成功']);
        } else {
            $this->display();
        }
    }
    
    /**
     * 更新索引
     * @param unknown $param
     */
    public function flush() {
        if (IS_POST) {
            $type = I('post.pro');
            if (!in_array($type, $this->type)) {
                $this->ajaxReturn(['code' => 'warning', 'msg' => '类型错误']);
            }
            $this->index->flushIndex();
            $this->ajaxReturn(['code' => 'success', 'msg' => '操作成功']);
        } else {
            $this->display();
        }
    }
    
    /**
     * 获取热门搜索
     * @param unknown $param
     */
    public function hot() {
        $type = 'total';
        $typeArr = [
            'total',    //所有
            'currnum',  //本周
            'lastnum'   //下周
        ];
        if (isset($_GET['type']) && !empty($_GET['type'])) {
            $type = I('get.type');
        }
        if (!in_array($type, $typeArr)) {
            $type = 'total';
        }
        $hotKeys = $this->s->getHotQuery(50, $type);
        $this->assign('data', $hotKeys);
        $this->display();
    }
    
    /**
     * 同义词
     */
    public function synonym() {
        $data = $this->s->getAllSynonyms();
        $this->assign('data', $data);
        $this->display();
    }
    
    /**
     * 添加同义词
     */
    public function addSynonym() {
        if (IS_POST) {
            $data = I('post.');
            $this->index->addSynonym($data['key'], $data['synonym']);
            $this->ajaxReturn(['code' => 'success', 'msg' => '操作成功']);
        } else {
            $this->display();
        }
    }
    
    /**
     * 删除同义词
     */
    public function delSynonym() {
        if (IS_POST) {
            $data = I('post.');
            if (isset($data['delAll'])) {
                $this->index->delSynonym($data['key']); //删除key下面所有的同义词
            } else {
                $this->index->delSynonym($data['key'], $data['synonym']);   //删除key下面的synonym词
            }
            $this->ajaxReturn(['code' => 'success', 'msg' => '操作成功']);
        }
    }
    
    /**
     * 索引商品
     */
    public function indexGoods() {
        $doc = new XSDocument();
        $do = D('Common/SearchGoodsView');
        $field = 'id,attr_list_id,images,goods_name,sub_name,pr,content,price,rate_good,rate_middle,fraction,view,fav_num,is_best,is_love,free_express,sale_num,is_self,code,officialactivity_price,officialactivity_join_id,shop_category_id,category_id,category_name,shop_name,b_name,type_id,qq,score_ratio,category_id,ssid,cate_id,is_daigou,status,uptime';
        //$map = ['status' => 1];
        $map = [];
        $count = $do->where($map)->count();
        $num = ceil($count/1000);
        //$num = 3;
        $this->index->openBuffer();
        for ($i=1;$i<=$num;$i++) {
            $data = $do->where($map)->page($i, 1000)->field($field)->order('id asc')->select();
            foreach ($data as $v) {
               $v['content'] = str_replace(["&nbsp;", "\r", "\t", "\n", "\r\n", " "], '', strip_tags(html_entity_decode($v['content'])));
                if (!empty($v['shop_category_id'])) {
                    $maps = [
                        'id' => ['in', trim($v['shop_category_id'], ',')],
                        'status' => 1,
                    ];
                    $v['shop_category'] = join(',', M('shop_goods_category')->where($maps)->getField('category_name', true));
                }
                $v['uptime'] =   strtotime($v['uptime']);
                $v['attrs']  =   join('', M('goods_attr_value')->where(['goods_id' => $v['id']])->getField('attr_value', true));
                $v['params'] =   join('', M('goods_param')->where(['goods_id' => $v['id']])->getField('param_value', true));
                $v['id'] = M('goods_attr_list')->where(['goods_id' => $v['id']])->getField('id');
                $v['category_id'] = $v['category_id'] .',' . $v['cate_id'] . ',' . $v['ssid'];
                unset($v['attr_list_id'], $v['shop_category_id'], $v['ssid'], $v['cate_id']);
                //dump($v);
                $doc->setFields($v);
                $this->index->add($doc);
            }
            usleep(1000);
        }
        $this->index->closeBuffer();
        $this->ajaxReturn(['code' => 'success', 'msg' => '索引成功']);
    }
}