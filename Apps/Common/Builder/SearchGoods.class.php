<?php
namespace Common\Builder;
class SearchGoods extends Search {
    
    protected $_cateId = null;  //商品分类
    protected $_option = null; //商品筛选
    protected $project = 'goodscfg';
    protected $_corrected = []; //Pinyin转换
    protected $_hots = []; // 热门词汇
    protected $_tmp = [];
    function __construct($params = null) {
        parent::__construct($params);
        if (!is_null($params)) {
            $query = '%s %s %s %s %s %s %s';
            $this->_tmp = $this->parseQ();
            $this->options();
            if (isset($tmp['cate'])) {
                $this->_cateId = $this->_tmp['cate'];
            }
            $query = trim(sprintf($query, (!empty($this->_tmp['keywords']) ? '('.$this->_tmp['keywords'].')' : null), $this->_option, $this->cate(), $this->isSelf(), $this->score(), $this->daigou(), $this->status()), ' ');
            $this->search($query);
        }
        
//         $this->s->setQuery($this->_tmp['keywords']);
//         $q = 'shouji';
//         $a = $this->s->getExpandedQuery($q);//搜索建议
//         dump($a);
//         $b = $this->s->getCorrectedQuery($q); //搜索建议
//         dump($b);
        
//         $c = $this->s->getRelatedQuery($q); //相关
//         dump($c);
        
//         $tokenizer = $this->xsToken();
//         $d = $tokenizer->getResult($q);
//         dump($d);
        
//         $e = $tokenizer->getTops($q, 5, 'n,v,vn');
//         dump($e);
//         dump($query);
//         dump($this->_cateId);
    }
    
    /**
     * 筛选
     */
    protected function options() {
        $this->getCateId();
        if ($this->_cateId) {
            $do = D('Common/SearchParamsRelation');
            $option = null;
            $params = $do->where(['category_id' => ['in', $this->_cateId], 'status' => 1])->cache(600)->relation(true)->field('group_name,id,category_id')->order('sort asc')->select();
            if (!empty($params)) {
                $isOp = [];
                foreach ($params as $k => &$v) {
                    foreach ($v['option'] as $key => &$val) {
                        if (array_key_exists('op_'.$val['id'], $this->params)) {
                            $isOp[$val['id']] = urldecode($this->params['op_' . $val['id']]);
                            $option .= str_replace('_', '/', $isOp[$val['id']]) . ' ';
                            unset($params[$k]['option'][$key]);
                            continue;
                        }
                        $tmp = explode(',', $val['options']);
                        $count = count($tmp);
                        for ($i=0;$i<$count;$i++) {
                            $val['child'][$i]['value'] = $tmp[$i];
                            $val['child'][$i]['url'] = $this->genUrl('op_'.$val['id'], $tmp[$i]);
                        }
                    }
                }
                $this->_data['isOp'] = $isOp;
                $this->_data['options'] = $params;
            }
            if (!is_null($option)) {
                if ($this->params['keywords']) $this->_option = 'AND ';
                $this->_option .= '(params:'.urldecode(trim($option, ' ')).')';
            }
        }
    }
    
    /**
     * 搜索自营商品
     */
    private function isSelf() {
        $self = null;
        if (isset($this->params['is_self']) && !empty($this->params['is_self'])) {
            if ($this->params['keywords']) $self = 'AND ';
            $self .= '(type_id:1)';
        }
        return $self;
    }
    
    /**
     * 积分
     */
    private function score() {
        $score = null;
        if (isset($this->params['score_ratio']) && !empty($this->params['score_ratio'])) {
            if ($this->params['keywords']) $score = 'AND ';
            $score .= '(score_ratio:'.$this->params['score_ratio'].')';
        }
        return $score;
    }
    
    /**
     * 分类
     * @return Ambigous <NULL, string>
     */
    private function cate() {
        $cate = null;
        if (isset($this->params['id']) && !empty($this->params['id'])) {
            if ($this->params['keywords']) $cate = 'AND ';
            $cate .= '(category_id:'.$this->params['id'].')';
        }
        return $cate;
    }
    
    /**
     * 代购
     * @return Ambigous <NULL, string>
     */
    private function daigou() {
        $daigou = null;
        if (isset($this->params['is_daigou']) && !empty($this->params['is_daigou'])) {
            if ($this->params['keywords']) $daigou = 'AND ';
            $daigou .= '(is_daigou:1)';
        }
        return $daigou;
    }
    
    /**
     * 状态
     * @return Ambigous <NULL, string>
     */
    private function status() {
        $status = null;
        if ($this->params['keywords']) $status = 'AND ';
        $status .= '(status:1)';
        return $status;
    }
    
    /**
     * 获取分类ID
     */
    protected function getCateId() {
        if (isset($this->params['id'])) $this->_cateId = $this->params['id'];
        if (is_null($this->_cateId)) {  //如果没有分类ID，则找出第一个商品的分类
            if ($this->s->count('(' . $this->_tmp['keywords'] . ')') > 0) {
                $this->s->setLimit(1);
                $tmpData = $this->s->search('(' . $this->_tmp['keywords'] . ')');
                $this->_cateId = $tmpData[0]['category_id'];
            }
        }
    }
    
    /**
     * 搜索
     * @param unknown $query
     */
    protected function search($query) {
        $this->s->setTimeout(5);    //设置搜索超时
        $this->_data['count'] = $this->s->count($query);
        if ($this->_data['count'] > 0) {
            $this->s->setQuery($query); //设置搜索
            $this->setSort();   //排序
            $this->s->setSort('type_id', false);
            $this->s->addRange('price', $this->getMinPrice(), $this->getMaxPrice()); //价格区间
            $this->s->setLimit($this->pagesize, $this->getNumber());    //设置分页
            $this->_data['data'] = $this->s->search();  //执行搜索
            if ($this->getMaxPrice() || $this->getMinPrice()) $this->_data['count']= $this->s->count(); //如果有使用价格筛选，则重新统计搜索数量
            $this->pages(); //分页
            $this->relatedQuery(); //相关搜索
            $this->correctedQuery(); //搜索建议
        }
    }
    
    /**
     * 获取最低价
     */
    protected function getMinPrice() {
        return $this->params['min_price'] ? $this->params['min_price'] : null;
    }
    
    /**
     * 获取最高价
     */
    protected function getMaxPrice() {
        return $this->params['max_price'] ? $this->params['max_price'] : null;
    }
    
    /**
     * 设置数据
     * @param unknown $param
     */
    public function setParams($goodsId) {
        $do = D('Common/SearchGoodsView');
        $field = 'id,attr_list_id,images,goods_name,sub_name,pr,content,price,rate_good,rate_middle,fraction,view,fav_num,is_best,is_love,free_express,sale_num,is_self,code,officialactivity_price,officialactivity_join_id,shop_category_id,category_id,category_name,shop_name,b_name,type_id,qq,score_ratio,category_id,ssid,cate_id,is_daigou,status,uptime';
        $map['id']  =   $goodsId;
        $this->_params = $do->where($map)->field($field)->order('id asc')->find();
        if ($this->_params) {
            $this->_params['content'] = str_replace(["&nbsp;", "\r", "\t", "\n", "\r\n", " "], '', strip_tags(html_entity_decode($this->_params['content'])));
            if (!empty($v['shop_category_id'])) {
                $maps = [
                    'id' => ['in', trim($this->_params['shop_category_id'], ',')],
                    'status' => 1,
                ];
                $this->_params['shop_category'] = join(',', M('shop_goods_category')->where($maps)->getField('category_name', true));
            }
            $this->_params['uptime'] =   strtotime($this->_params['uptime']);
            $this->_params['attrs']  =   join('', M('goods_attr_value')->where(['goods_id' => $this->_params['id']])->getField('attr_value', true));
            $this->_params['params'] =   join('', M('goods_param')->where(['goods_id' => $this->_params['id']])->getField('param_value', true));
            $this->_params['id'] = M('goods_attr_list')->where(['goods_id' => $this->_params['id']])->getField('id');
            $this->_params['category_id'] = $this->_params['category_id'] .',' . $this->_params['cate_id'] . ',' . $this->_params['ssid'];
            unset($data['attr_list_id'], $data['shop_category_id'], $data['ssid'], $data['cate_id']);
        }
    }
}