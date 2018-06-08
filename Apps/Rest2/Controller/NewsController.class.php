<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 新闻相关接口
 * ----------------------------------------------------------
 * Author:lizuheng 
 * ----------------------------------------------------------
 * 2017-03-20
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller;
class NewsController extends ApiController {
	/**
     * subject: Wap新闻列表
     * api: /News/index
     * author: lizuheng
     * day: 2017-03-20
     *
     * [字段名,类型,是否必传,说明]
     * param: pagesize,int,0,当前页数量，默认10条
     * param: p,int,0,当前页(默认第一页)
     * param: cid,int,0,分类id(查询条件)
     * param: name,string,0,新闻标题(查询条件)
	 * param: status,int,0,状态(默认为 1)
     * param: atime,int,0,发布时间(查询条件)
     */
    public function index(){
		$field = 'sign';
		$this->check($field,false);
		
		$res = $this->_index($this->post);
        $this->apiReturn($res);		
	}
	
    public function _index($param){		
		if(isset($param['cid'])) $map['category_id'] = $param['cid'];
		if(isset($param['name'])) $map['name'] =  array('like','%'.trim($param['name']).'%');
		if(isset($param['status'])){
			$map['status'] = $param['status'];
		}else{
			$map['status'] = 1;
		}
        if(isset($param['atime'])) $map['atime'] = array('EGT',date('Y-m-d H:i:s', strtotime($param['atime'])));

		$pagelist = pagelist([
            'table'     => 'Common/NewsView',
			'do'        => 'D',
            'map'       => $map,
            'pagesize'  => 10,
            'order'     => 'is_top,id desc',
            'p'         => $param['p'],
            //'cache_name'=> md5(implode(',', I('post.')) . ACTION_NAME),
        ]);

        if (!empty($pagelist['list'])) {
        	$old = $pagelist;
            $pagelist['list'] = cut_list($pagelist['list'],'content,120|name,20');
			foreach($pagelist['list'] as $k => $v){
				$pagelist['list'][$k]['name'] = str_replace('C+','C<sup>+</sup>',$v['name']);
				$pagelist['list'][$k]['content'] = mb_substr(str_replace('C+','C<sup>+</sup>',$v['content']),0,50,'utf-8');
				# 优化显示时间
				$pagelist['list'][$k]['atime'] = date('Y/m/d', strtotime($pagelist['list'][$k]['atime']));
				# 抽取一张缩略图
	            preg_match('/<img.*?src=[\"|\']?(.*?)[\"|\']?\s.*?>/i', html_entity_decode($old['list'][$k]['content']), $images);
	            $pagelist['list'][$k]['images'] = $images[1] ? $images[1] : '';
				$pagelist['list'][$k]['url']   =  C('sub_domain.m')."/News/view/id/".$pagelist['list'][$k]['id'].'/is_header/1'; 
			}
            return ['code' => 1,'data' => $pagelist];
        }
        return ['code' => 3,'msg' => '找不到记录！'];
	}	

	/**
     * subject: Wap新闻详情
     * api: /News/view
     * author: lizuheng
     * day: 2017-03-20
     *
     * [字段名,类型,是否必传,说明]
	 * param: id,string,1,新闻id
     */
    public function view() {
		$field = 'id,sign';
		$this->check($field);

        $res = $this->_view($this->post);
        $this->apiReturn($res);
    }
	public function _view($param){
        $model = D('Common/NewsView');
        $info = $model->where(['status' => 1, 'id' => $param['id']])->field('id,atime,hit,name,content,category_id,category_name,cid')->find();
		if($info){
			M('news')->where(array('id' => $param['id']))->setInc('hit', 1);
            // 把 HTML 实体转换为字符
            $info['content'] = html_entity_decode($info['content']);
            # 抽取一张缩略图
            preg_match('/<img.*?>/i', $info['content'], $info['images']);
            $info['images'] = $info['images'][0] ? $info['images'][0] : '';

			return ['code' => 1,'data' => $info];
        }
		return ['code' => 3,'msg' => '找不到记录！'];
    }	
	
	/**
     * subject: 新闻浏览加一
     * api: /News/view
     * author: lizuheng
     * day: 2017-03-20
     *
     * [字段名,类型,是否必传,说明]
	 * param: id,string,1,新闻id
     */
    public function setInc() {
		$field = 'id,sign';
		$this->check($field);

        $res = $this->_setInc($this->post);
        $this->apiReturn($res);
    }
	public function _setInc($param){
        $info = M('news')->where(array('id' => $param['id']))->setInc('hit', 1);
		if($info){
			return ['code' => 1,'data' => $info];
        }
		return ['code' => 3,'msg' => '操作失败!'];
    }	
	
	/**
     * subject: 取最新的新闻
     * api: /News/getNew
     * author: lizuheng
     * day: 2017-03-20
     *
     * [字段名,类型,是否必传,说明]
	 * param: num,string,0,默认为5条
     */
    public function getNew() {
		$field = 'sign';
		$this->check($field,false);

        $res = $this->_getNew($this->post);
        $this->apiReturn($res);
    }
	public function _getNew($param){
		$num = 5;
		if(isset($param['num'])) $num = $param['num'];
	    $list  =   M('news')->where(['status' => 1])->limit($num)->order("id desc")->select();
	    if ($list) {
			return ['code' => 1,'data' => $list];
	    }
		return ['code' => 3,'msg' => '操作失败!'];
    }
	
	/**
     * subject: 随机读取新闻
     * api: /News/random
     * author: lizuheng
     * day: 2017-03-20
     *
     * [字段名,类型,是否必传,说明]
	 * param: num,string,0,新闻数量，默认为5条
	 * param: status,string,0,新闻状态，默认为1
     */
    public function random() {
		$field = 'sign';
		$this->check($field);

        $res = $this->_random($this->post);
        $this->apiReturn($res);
    }
	public function _random($param){
		if(isset($param['status'])){
			$map['status'] = $param['status'];
		}else{
			$map['status'] = 1;
		}
		$num = 5;
		if(isset($param['num'])) $num = $param['num'];
		//随机取新闻
        $count = M('news')->where($map)->count();
        if(($count - $num) > 0){
        	$limit = rand(0,$count-$num) . ',' . $num;
        }
        $field = 'id,atime,hit,name,content,category_id,category_name,cid';
        $list = D('Common/NewsView')->where($map)->cache(true,C('CACHE_LEVEL.M'))->field($field)->limit($limit)->select();

	    if ($list) {
			$old = $list;
            $list = cut_list($list,'name,20');
			foreach($list as $k => $v){
				$list[$k]['name'] = str_replace('C+','C<sup>+</sup>',$v['name']);
				# $pagelist['list'][$k]['content'] = str_replace('C+','C<sup>+</sup>',$v['content']);
				# 优化显示时间
				# $pagelist['list'][$k]['atime'] = date('Y/m/d', strtotime($pagelist['list'][$k]['atime']));
				# 抽取一张缩略图
	            preg_match('/<img.*?>/i', html_entity_decode($old[$k]['content']), $images);
	            $list[$k]['images'] = $images[0] ? $images[0] : '';
			}
			return ['code' => 1,'data' => $list];
	    }
		return ['code' => 3,'msg' => '操作失败!'];
    }

	/**
     * subject: 商城新闻中心文章分类
     * api: /News/category
     * author: lizuheng
     * day: 2017-03-20
     *
     * [字段名,类型,是否必传,说明]
     */
    public function category() {
		$field = 'sign';
		$this->check($field);

        $res = $this->_category($this->post);
        $this->apiReturn($res);
    }
	public function _category($param){
        $param = [
            'table' => 'news_category',
            'field' => 'id,category_name,sid',
            'map'   => ['satus' => 1],
            'order' => 'id asc',
        ];
        $list = get_category($param);
	    if ($list) {
			return ['code' => 1,'data' => $list];
	    }
		return ['code' => 3,'msg' => '操作失败!'];
    }	
}