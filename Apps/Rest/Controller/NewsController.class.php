<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 新闻中心
+----------------------------------------------------------------------
| Author: 李博
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class NewsController extends CommonController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }
	
	/**
	 * 新闻中心列表
	 */
	public function plist(){
		$this->_check_sign();
		// 分页参数p和每页显示记录数pagesize
		$map['status'] = 1;
        if(I('post.cid')) $map['category_id'] = I('post.cid');
        if(I('post.q')) $map['name'] = array('like','%'.trim(I('post.q')).'%');
        if(I('post.atime')) $map['atime'] = array('EGT',date('Y-m-d H:i:s', strtotime(I('post.atime'))));

        $pagesize = I('post.pagesize')?I('post.pagesize'):20;
        $order = I('post.order')?I('post.order'):'id desc';
        $pagelist = pagelist(array(
                'table'     => 'Common/NewsView',
                'do'        => 'D',
                'map'       => $map,
                'order'     => 'atime desc',
                'fields'    => 'id,category_id,name,hit,category_name,cid,content,atime',
                'order'     => $order,
                'pagesize'  => $pagesize,
                'action'            => I('post.action'),
                'query'             => I('post.query')?query_str_(I('post.query')):'',
                'p'                 => I('post.p'),
                'cache_name'        => md5(implode(',',$_POST).__SELF__),
                'cache_time'        => C('CACHE_LEVEL.OneDay'),
            ));


        if($pagelist['list']){
        	$old = $pagelist;
            $pagelist['list'] = cut_list($pagelist['list'],'content,120|name,20');
			foreach($pagelist['list'] as $k => $v){
				$pagelist['list'][$k]['name'] = str_replace('C+','C<sup>+</sup>',$v['name']);
				$pagelist['list'][$k]['content'] = str_replace('C+','C<sup>+</sup>',$v['content']);
				# 优化显示时间
				$pagelist['list'][$k]['atime'] = date('Y/m/d', strtotime($pagelist['list'][$k]['atime']));
				# 抽取一张缩略图
	            preg_match('/<img.*?src=[\"|\']?(.*?)[\"|\']?\s.*?>/i', html_entity_decode($old['list'][$k]['content']), $images);
	            $pagelist['list'][$k]['images'] = $images[1] ? $images[1] : '';
			}
            $this->apiReturn(1,array('data'=>$pagelist));
        }else{
            $this->apiReturn(3);
        }
	}
	
	/**
	 * 新闻中心文章详情
	 * @param int $id 文章ID
	 */
	public function view(){
		$this->need_param = ['id'];
        $this->_need_param();
        $this->_check_sign();
        $id = I('post.id', 0, 'int');
        // $model = M('news');
        $model = D('Common/NewsView');
        $info = $model->where(['status' => 1, 'id' => $id])->field('id,atime,hit,name,content,category_id,category_name,cid')->find();
		if($info){
            $model->where(['id' => $id])->setInc('hit',1,60);
            // 把 HTML 实体转换为字符
            $info['content'] = html_entity_decode($info['content']);
            # 抽取一张缩略图
            preg_match('/<img.*?>/i', $info['content'], $info['images']);
            $info['images'] = $info['images'][0] ? $info['images'][0] : '';

            $this->apiReturn(1, ['data' => $info]);
        }else{
            $this->apiReturn(3);
        }
	}
	
	/**
	* 新闻中心文章分类
	* 使用get_category函数获取分类
	*/
	public function category(){
		$this->_check_sign();
        $param = [
            'table' => 'news_category',
            'field' => 'id,category_name,sid',
            'map'   => ['satus' => 1],
            'order' => 'id asc',
        ];
        $list = get_category($param);
        if($list){
            $this->apiReturn(1, ['data' => $list]);
        }else{
            $this->apiReturn(3);
        }
	}
	
	/**
	 * 浏览加一
	 */
	public function setInc() {
	    $this->need_param = ['id'];
	    $this->_need_param();
	    $this->_check_sign();
	    M('news')->where(array('id' => I('post.id')))->setInc('hit', 1);
	    $this->apiReturn(1);
	}
	
	/**
	 * 后去最新N条数据
	 */
	public function getNew() {
	    $this->_need_param();
	    $this->_check_sign();
	    $model = M('news');
	    $sortArr    =   ['desc','asc'];
	    $num   =   isset($_POST['num']) ?  I('post.num') : '5';
	    $order =   isset($_POST['sort']) && in_array(I('post.sort'), $sortArr) ? 'id ' . I('sort') : 'id desc';
	    $list  =   $model->field('id,name,atime')->where(['status' => 1])->limit($num)->order($order)->select();
	    if ($list) {
	        $this->apiReturn(1, ['data' => $list]);
	    }
	    $this->apiReturn(3);
	}

	/**
	 * 随机新闻
	 */
	public function random(){
		$this->_check_sign();
		$map['status'] = 1;
		
		$limit = I('limit',5, 'int');

		//随机取新闻
        $count = M('news')->where($map)->count();
        if(($count - $limit) > 0){
        	$limit = rand(0,$count-$limit) . ',' . $limit;
        }
        $field = 'id,atime,hit,name,content,category_id,category_name,cid';
        $list = D('Common/NewsView')->where($map)->cache(true,C('CACHE_LEVEL.M'))->field($field)->limit($limit)->select();

        if($list){
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
            $this->apiReturn(1,array('data'=>$list));
        }else{
            $this->apiReturn(3);
        }
	}


}