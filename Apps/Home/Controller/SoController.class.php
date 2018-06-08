<?php
namespace Home\Controller;
use Xs\xs;
use Xs\XSDocument;
use Common\Builder\SearchGoods;
class SoController extends CommonController {
    protected $_xs;
    protected $_s;
    public function _initialize() {
        if (I('get.t') != 'goods')exit();
        import('Vendor.Xs.xs');
        $this->_xs = new xs(I('get.t'));
        $this->_s = $this->_xs->search;
        parent::_initialize();
    }
    
    
    public function s() {
        $parsm = I('get.');
        dump($parsm);
        $search = new SearchGoods(I('get.'));
        dump($search->_data);
    }
    
    public function index() {
        //return false;
        $q = I('get.q');
        $do = D('Common/SearchParamsRelation');
        $params = $do->where(['category_id' => '100841621', 'status' => 1])->relation(true)->field('group_name,id,category_id')->order('sort asc')->select();
        //dump($params[0]);
        //$query = '('.I('get.q').')'; // 这里的搜索语句很简单，就一个短语
        //dump($q);
        //$query = 'goods_name:' . $query . ' attrs:' . $query . ' params:' . $query;
        //dump($query);
        
        
        
        //分类
        $cate = null;
        if (isset($_GET['id']) && !empty(I('get.id'))) {
            if ($q) $cate = 'AND ';
            $cate .= '(category_id:'.I('get.id').')';
        }
        
        //筛选
        $option = null;
        if (isset($_GET['op']) && !empty(I('get.op'))) {
            if ($q) $option = 'AND ';
            $option .= '(params:'.I('get.op').')';
        }
        
        //自营
        $self = null;
        if (isset($_GET['is_self']) && !empty(I('get.is_self'))) {
            if ($q) $self = 'AND ';
            $self .= '(type_id:1)';
        }
        
        $score = null;
        if (isset($_GET['score_ratio']) && !empty(I('get.score_ratio'))) {
            if ($q) $self = 'AND ';
            $self .= '(score_ratio:'.I('get.score_ratio').')';
        }
        
        $query = '%s %s %s %s %s';
        
        if (!empty($q) && (strpos($q, ' ') === false)) {
            $qc = '(category_name:' . $q . ')';
            $queryc = trim(sprintf($query, $qc, $option, $cate, $self, $score), ' ');
        }
        dump($queryc);
        if ($this->_s->count($queryc) <= 0) {
            $query = trim(sprintf($query, $q, $option, $cate, $self, $score), ' ');
        } else {
            $query = $queryc;
        }
        dump($query);
        $this->_s->setQuery(''); // 设置搜索语句
//         $this->_s->addWeight('attrs', $query);
//         $this->_s->addWeight('params', $query);
        //if (!empty($q)) $this->_s->addWeight('category_name', $q);//分类权重
        
        //$search->addWeight('subject', 'xunsearch'); // 增加附加条件：提升标题中包含 'xunsearch' 的记录的权重
        $this->_s->setLimit(30); // 设置返回结果最多为 5 条，并跳过前 10 条
        
        $docs = $this->_s->search(); // 执行搜索，将搜索结果文档保存在 $docs 数组中
        dump($docs);
        //suggest建议，hot热门，correct纠错，related相关
        $count = $this->_s->count(); // 获取搜索结果的匹配总数估算值
        
        
        //$hots = $this->_s->
        
        dump($count);
        // 由于拼写错误，这种情况返回的数据量可能极少甚至没有，因此调用下面方法试图进行修正
        dump('纠错');
        $corrected = $this->_s->getCorrectedQuery();
        dump($corrected);
        if (count($corrected) !== 0)
        {
            // 有纠错建议，列出来看看；此情况就会得到 "测试" 这一建议
            echo "您是不是要找：\n";
            foreach ($corrected as $word)
            {
                echo $word . "\n";
            }
        }
        dump('相关');
        $re = $this->_s->getRelatedQuery();
        dump($re);
    }
    /*
    public function insert() {
        $d = new XSDocument();
        $data = [
            [
                'pid' => 9, // 此字段为主键，必须指定
                'subject' => '小米手机5',
                'message' => '小米手机5小米手机5小米手机5',
                'chrono' => time()
            ],
            [
            'pid' => 10, // 此字段为主键，必须指定
            'subject' => '小米5',
            'message' => '小米5小米5小米5小米5',
            'chrono' => time()
            ],
            [
            'pid' => 11, // 此字段为主键，必须指定
            'subject' => '红米note3',
            'message' => '红米note3红米note3红米note3红米note3',
            'chrono' => time()
            ],
            [
            'pid' => 12, // 此字段为主键，必须指定
            'subject' => '红米手机',
            'message' => '红米手机红米手机红米手机红米手机红米手机',
            'chrono' => time()
            ],
            [
            'pid' => 13, // 此字段为主键，必须指定
            'subject' => '小米note手机',
            'message' => '小米note手机小米note手机小米note手机小米note手机小米note手机',
            'chrono' => time()
            ],
            [
            'pid' => 14, // 此字段为主键，必须指定
            'subject' => '三星note2',
            'message' => '三星note2三星note2三星note2三星note2三星note2',
            'chrono' => time()
            ],
            [
            'pid' => 15, // 此字段为主键，必须指定
            'subject' => '三星note3',
            'message' => '三星note3三星note3三星note3三星note3三星note3',
            'chrono' => time()
            ],
            [
            'pid' => 16, // 此字段为主键，必须指定
            'subject' => '三星note4',
            'message' => '三星note4三星note4三星note4',
            'chrono' => time()
            ],
            [
            'pid' => 17, // 此字段为主键，必须指定
            'subject' => 'iPhone4',
            'message' => 'iPhone4iPhone4iPhone4iPhone4',
            'chrono' => time()
            ],
            [
            'pid' => 18, // 此字段为主键，必须指定
            'subject' => 'iPhone4s',
            'message' => 'iPhone4siPhone4siPhone4siPhone4s',
            'chrono' => time()
            ],
            [
            'pid' => 19, // 此字段为主键，必须指定
            'subject' => 'iPhone5s',
            'message' => 'iPhone5siPhone5siPhone5siPhone5s',
            'chrono' => time()
            ],
            [
            'pid' => 20, // 此字段为主键，必须指定
            'subject' => 'iPhone6s',
            'message' => 'iPhone6siPhone6siPhone6siPhone6siPhone6s',
            'chrono' => time()
            ],
        ];
        
        foreach ($data as $v) {
            $d->setFields($v);
            //$d->add();
            $this->_xs->index->add($d);
        }
    }
    
    
    public function goods() {
//         $pagelist=pagelist(array(
//                 'table'     =>'Common/GoodsRelation',
//                 'do'        =>'D',
//                 'map'       =>['status' => 1],
//                 'order'     =>'atime desc',
//                 'fields'    =>'id,goods_name,images,price,sale_num,seller_id,shop_id,score_ratio,round(score_ratio*price*100,2) as score,is_self,(pr+(unix_timestamp()-unix_timestamp(uptime))/86400) as pr,officialactivity_join_id,officialactivity_price,is_daigou',
//                 'order'     =>'id asc',
//                 'pagesize'  =>100,
//                 'relation'  =>true,
//                 'relationWhere'     =>array('goods_attr_list','num>0'),
//                 'relationOrder'     =>array('goods_attr_list','price asc'),
//                 'relationField'     =>array('goods_attr_list','id,attr_name,price,price_market,sale_num,view,concat("/Goods/view/id/",id,".html") as url'),
//                 'relationLimit'     =>array('goods_attr_list',1),
//                 'action'            =>I('post.action'),
//                 'query'             =>I('post.query')?query_str_(I('post.query')):'',
//                 'p'                 =>I('post.p'),
// //                 'cache_name'        =>md5(implode(',',$_POST).__SELF__),
// //                 'cache_time'        =>C('CACHE_LEVEL.L'),
//             ));
//         dump($pagelist);
//         [price]
//         [rate_good]
//         [rate_middle]
//         [fraction]
//         [view]
//         [fav_num]
//         [is_best]
//         [is_love]
//         [free_express]
//         [pr]
//         [sale_num]
        //$do = D('Common/SearchGoodsView');
//         $field = 'id,attr_list_id,images,goods_name,sub_name,pr,content,price,rate_good,rate_middle,fraction,view,fav_num,is_best,is_love,free_express,sale_num,is_self,code,officialactivity_price,officialactivity_join_id,shop_category_id,category_id,category_name,shop_name,b_name,type_id,qq,score_ratio';
//         $map = ['status' => 1];
//         $map['id'] = 2073;
//         //$map['goods_name'] = ['like', '%手机%'];
//         $count = $do->where($map)->count();
//         $data = $do->where($map)->field($field)->order('id asc')->select();
//         dump($data);
//         echo $do->getLastSql();
//         //dump($count);
//         //dump($data);
//         $sql = 'SELECT distinct goods.id,goods.id AS id,goods.images AS images,goods.goods_name AS goods_name,goods.sub_name AS sub_name,goods.pr AS pr,goods.price AS price,goods.rate_good AS rate_good,goods.rate_middle AS rate_middle,goods.fraction AS fraction,goods.view AS view,goods.fav_num AS fav_num,goods.is_best AS is_best,goods.is_love AS is_love,goods.free_express AS free_express,goods.sale_num AS sale_num,goods.is_self AS is_self,goods.code AS code,goods.officialactivity_price AS officialactivity_price,goods.officialactivity_join_id AS officialactivity_join_id,goods.shop_category_id AS shop_category_id,goods.category_id AS category_id,goods.score_ratio AS score_ratio,goods_attr_list.id AS attr_list_id,goods_content.content AS content,brand.b_name AS b_name,shop.shop_name AS shop_name,shop.type_id AS type_id,shop.qq AS qq,goods_category.category_name AS category_name FROM ylh_goods goods JOIN ylh_goods_attr_list goods_attr_list ON goods.id = goods_attr_list.goods_id JOIN ylh_goods_content goods_content ON goods.id = goods_content.goods_id JOIN ylh_brand brand ON goods.brand_id = brand.id LEFT JOIN ylh_shop shop ON goods.shop_id = shop.id JOIN ylh_goods_category goods_category ON goods_category.id = goods.category_id WHERE goods.status = 1 AND goods.goods_name LIKE \'%手机%\' ORDER BY goods.id asc LIMIT 0,100';
//         //$sql = 'SELECT distinct goods.id, goods.id AS id,goods.images AS images,goods.goods_name AS goods_name,goods.sub_name AS sub_name,goods.pr AS pr,goods.price AS price,goods.rate_good AS rate_good,goods.rate_middle AS rate_middle,goods.fraction AS fraction,goods.view AS view,goods.fav_num AS fav_num,goods.is_best AS is_best,goods.is_love AS is_love,goods.free_express AS free_express,goods.sale_num AS sale_num,goods.is_self AS is_self,goods.code AS code,goods.officialactivity_price AS officialactivity_price,goods.officialactivity_join_id AS officialactivity_join_id,goods.shop_category_id AS shop_category_id,goods.category_id AS category_id,goods.score_ratio AS score_ratio,goods_attr_list.id AS attr_list_id,goods_content.content AS content,brand.b_name AS b_name,shop.shop_name AS shop_name,shop.type_id AS type_id,shop.qq AS qq,goods_category.category_name AS category_name FROM ylh_goods goods JOIN ylh_goods_attr_list goods_attr_list ON goods.id = goods_attr_list.goods_id LEFT JOIN ylh_goods_content goods_content ON goods.id = goods_content.goods_id JOIN ylh_brand brand ON goods.brand_id = brand.id LEFT JOIN ylh_shop shop ON goods.shop_id = shop.id JOIN ylh_goods_category goods_category ON goods_category.id = goods.category_id WHERE goods.status = 1 AND goods.goods_name LIKE \'%手机%\' ORDER BY goods.id asc LIMIT 0,100';
//         $a = M()->query($sql);
//         dump($a);
//         exit;
        $doc = new XSDocument();
        $do = D('Common/SearchGoodsView');
        $field = 'id,attr_list_id,images,goods_name,sub_name,pr,content,price,rate_good,rate_middle,fraction,view,fav_num,is_best,is_love,free_express,sale_num,is_self,code,officialactivity_price,officialactivity_join_id,shop_category_id,category_id,category_name,shop_name,b_name,type_id,qq,score_ratio,category_id,ssid,cate_id';
        $map = ['status' => 1];
        $count = $do->where($map)->count();
        $num = ceil($count/100);
        //$this->_xs->index->openBuffer();
        $sid = M('goods_category')->where(['id' => 100845583])->getField('sid');
        $ssid = M('goods_category')->where(['id' => $sid])->getField('sid');
        echo($sid . $ssid);
        $num = 1;
        for ($i=1;$i<=$num;$i++) {
            $data = $do->where($map)->page($i, 100)->field($field)->order('id asc')->select();
            echo $do->getLastSql();
            foreach ($data as $v) {
                $v['content'] = str_replace(["&nbsp;", "\r", "\t", "\n", "\r\n", " "], '', strip_tags(html_entity_decode($v['content'])));
                if (!empty($v['shop_category_id'])) {
                    $maps = [
                        'id' => ['in', trim($v['shop_category_id'], ',')],
                        'status' => 1,
                    ];
                    $v['shop_category'] = join(',', M('shop_goods_category')->where($maps)->getField('category_name', true));
                }
                $v['attrs']  =   join('', M('goods_attr_value')->where(['goods_id' => $v['id']])->getField('attr_value', true));
                $v['params'] =   join('', M('goods_param')->where(['goods_id' => $v['id']])->getField('param_value', true));
                $v['id'] = M('goods_attr_list')->where(['goods_id' => $v['id']])->getField('id');
                $v['category_id'] = $v['category_id'] .',' . $v['cate_id'] . ',' . $v['ssid'];
                unset($v['attr_list_id'], $v['shop_category_id'], $v['ssid'], $v['cate_id']);
                dump($v);
                //$doc->setFields($v);
                //$this->_xs->index->add($doc);
            }
            usleep(100);
        }
        $this->_xs->index->closeBuffer();
    }
    
    public function clean() {
        $this->_xs->index->clean();
    }*/
}