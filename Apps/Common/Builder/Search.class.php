<?php
namespace Common\Builder;
use Xs\xs;
use Xs\XSTokenizerScws;
use Xs\XSDocument;
class Search {
    public $_data  = [];    //返回数据
    public $params = [];    //参数
    public $_params= [];    //修改数据
    protected $s;
    protected $xs;
    protected $pagesize = 30;
    protected $project;
    //suggest建议，hot热门，correct纠错，related相关
    function __construct($params = null) {
        import('Vendor.Xs.xs');
        $this->params = $params;
        $this->xs = new xs($this->project);
        $this->s = $this->xs->search;
    }
    
    /**
     * 分词
     */
    public function xsToken() {
        return new XSTokenizerScws();
    }
    
    /**
     * 索引文档
     */
    public function xsDocument() {
        return new XSDocument();
    }
    
    /**
     * 解析关键字
     */
    protected function parseQ() {
        $data['keywords'] = $this->params['keywords'] ? : null;
        if ($data['keywords']) {
            if ($this->s->count($data['keywords']) <= 0) {  //如果找不到数据，则将转换
                if (preg_match("/[\x7f-\xff]/", $data['keywords']) == false) { //如果关键字不含中文，则将关键字转换成中文
                    //没有任何中文
                    $this->s->setLimit(1,0);
                    $this->s->setQuery($data['keywords']);
                    $this->s->search();
                    $corrected = $this->s->getCorrectedQuery();  //获取Pinyin
                    if (!empty($corrected)) {
                        foreach ($corrected as $v) {
                           $data['keywords'] = $v;
                           break;
                       }
                    }
                }
            }
            if ($this->project == 'goods') {    //搜索商品时则查询分类
                if (!eregi("[^\x80-\xff]",$data['keywords']) && strlen($data['keywords']) <= 15) {
                    //全是中文，并且能够查询出来分类
                    if ($this->s->count('(category_name:' . $data['keywords'] . ')') > 0) {
                        $this->s->setLimit(1);
                        $tmpData = $this->s->search('(category_name:' . $data['keywords'] . ')');
                        $data['cate'] = $tmpData[0]['category_id'];
                        unset($tmpData);
                        $data['keywords'] = 'category_name:' . $data['keywords'];
                    }
                }
            }
            
            /**
             * 如果查不到任何数据，则使用scws分词
             */
            if ($this->s->count($data['keywords']) <= 0) {
                $tokenizer = new XSTokenizerScws();
                //$rs = $tokenizer->getResult($data['keywords']);   //分词
                //获取5个热门词
                $tops = $tokenizer->getTops($data['keywords'], 5, 'n,v,vn'); // 提取前 5 个重要词，要求词性必须是 n 或v 或 vn
                if (!empty($tops)) {
                    $data['keywords'] = null;
                    foreach ($tops as $v) {
                        $data['keywords'] .= $v['word'] . ' ';
                    }
                    $data['keywords'] = trim($data['keywords'], ' ');
                }
            }
            
        }
        return $data;
    }
    
    
    /**
     * 获取当前页面数据
     */
    protected function getNumber() {
        $this->params['p'] = (isset($this->params['p']) ? $this->params['p'] : 1) - 1;
        return ($this->params['p'] * $this->pagesize);
    }
    
    
    /**
     * 获取分页信息
     */
    protected function pages() {
        $pages = new \Think\Page($this->_data['count'], $this->pagesize);
        $this->_data['page'] = $pages->show();
    }
    
    /**
     * 生成url
     * @param string $key
     * @param string $val
     */
    protected function genUrl($key, $val) {
        $url = [];
        $url[$key] = urlencode(str_replace('/', '_', $val));
        if ($url) {
            $url = array_merge($this->params, $url);
        }
        array_unique($url);
        return U('/index/index', $url);
    }
    
    /**
     * 排序
     */
    protected function setSort() {
        if (isset($this->params['sort']) && !empty($this->params['sort'])) {
            $sort = explode('-', $this->params['sort']);
            $this->s->setSort($sort[0], $sort[1] == 'desc' ? false : true);
        }
    }
    
    /**
     * 搜索建议
     */
    protected function correctedQuery() {
        $this->_data['corrected'] = $this->s->getCorrectedQuery();
    }
    
    /**
     * 相关搜索
     */
    protected function relatedQuery() {
        $this->_data['related'] = $this->s->getRelatedQuery();
    }
    
    /**
     * 删除索引
     * @param integer|array $param
     */
    public function del($param = null) {
        if (is_null($param)) $param = $this->_params;
        if (empty($param)) die('需要删除的数据不能为空');
        $this->xs->index->del($param);
    }
    
    /**
     * 更新
     * @param array $param
     */
    public function update($param = null) {
        if (is_null($param)) $param = $this->_params;
        if (empty($param)) die('需要更新的数据不能为空');
        $doc = new XSDocument();
        $doc->setFields($param);
        $this->xs->index->update($doc);
    }
    
    /**
     * 添加索引
     * @param array $param
     */
    public function add($param = null) {
        if (is_null($param)) $param = $this->_params;
        if (empty($param)) die('需要添加的数据不能为空');
        $doc = $this->xsDocument();
        $doc->setFields($param);
        $this->xs->index->add($doc);
    }

}  