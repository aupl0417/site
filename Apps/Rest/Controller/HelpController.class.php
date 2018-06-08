<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 帮助中心
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
use Think\Controller;
class HelpController extends CommonController {

    public function index(){
    	redirect(C('sub_domain.www'));
    }
	
	/**
	* 帮助中心列表
	*/
	public function help_list(){
        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        $map['status']=1;
        if(I('post.cid')) $map['category_id']=I('post.cid');
        if(I('post.q')) $map['name']=array('like','%'.trim(I('post.q')).'%');

        $pagesize=I('post.pagesize')?I('post.pagesize'):20;
        $order=I('post.order')?I('post.order'):'id desc';
        $pagelist=pagelist(array(
                'table'     =>'Common/HelpView',
                'do'        =>'D',
                'map'       =>$map,
                'order'     =>'atime desc',
                'fields'    =>'id,category_id,name,hit,category_name,content',
                'order'     =>$order,
                'pagesize'  =>$pagesize,
                'action'            =>I('post.action'),
                'query'             =>I('post.query')?query_str_(I('post.query')):'',
                'p'                 =>I('post.p'),
                'cache_name'        =>md5(implode(',',$_POST).__SELF__),
                'cache_time'        =>C('CACHE_LEVEL.OneDay'),                
            ));


        if($pagelist['list']){
            $pagelist['list']=cut_list($pagelist['list'],'content,120');
			foreach($pagelist['list'] as $k => $v){
				$pagelist['list'][$k]['name'] = str_replace('C+','C<sup>+</sup>',$v['name']);
				$pagelist['list'][$k]['content'] = str_replace('C+','C<sup>+</sup>',$v['content']);
				
			}
            $this->apiReturn(1,array('data'=>$pagelist));
        }else{
            $this->apiReturn(3);
        }
	}
	
	/**
	 * 帮助中心文章详情
	 * @param int $id 文章ID
	 */
	public function view(){
        $this->need_param = ['id'];
        $this->_need_param();
        $this->_check_sign();

        $id = I('post.id', 0, 'int');
        $model = D('Common/HelpView');
        $info = $model->cache(true,C('CACHE_LEVEL.OneDay'))->where(['status' => 1, 'id' => $id])->field('id,atime,hit,name,content,category_id,category_name')->find();

		if($info){
            $model->where(['id' => $id])->setInc('hit',1,60);
            // 把 HTML 实体转换为字符
            $info['content'] = html_entity_decode($info['content']);
			//$info['name'] = str_replace('C+','C<sup>+</sup>',$info['name']);
			
            $this->apiReturn(1, ['data' => $info]);
        }else{
            $this->apiReturn(3);
        }
	}
	
	/**
	* 帮助中心文章分类
	* 使用get_category函数获取分类
	*/
	public function category(){
		$this->_check_sign();
        $param = [
            'table'         => 'help_category',
            'field'         => 'id,category_name',
            'map'           => ['satus' => 1],
            'order'         => 'id desc',
            'cache_name'    =>'help_category'
        ];
        $list = get_category($param);
        if($list){
            $this->apiReturn(1, ['data' => $list]);
        }else{
            $this->apiReturn(3);
        }
	}	


    /**
    * 会员等级权益
    */
    public function equity(){
        $this->_check_sign();

    	$do=M('user_level');
    	$list=$do->cache(true)->where(array('id'=>array('gt',1)))->order('sort asc,id asc')->getField('id,level_name,icon,upgrade_money,team_ratio,upgrade_ratio,upuser_ratio,about');
    	if($list) {
			foreach($list as $key=>$val){
				$list[$key]['about']=html_entity_decode($val['about']);
			}
			$this->apiReturn(1,array('data'=>$list));
		}else $this->apiReturn(3);
    }

    /**
    * 关于云康
    */
    public function about(){
    	$this->_check_sign();
    	
    	$do=M('help');
    	$id=3;    	
    	$rs=$do->cache(true)->where('id='.$id)->field('name,hit,content')->find();
    	if($rs) {
			$do->where('id='.$id)->setInc('hit',1,60);
    		$rs['content']=html_entity_decode($rs['content']);
    		$this->apiReturn(1,array('data'=>$rs));
    	}else $this->apiReturn(3);    	
    }

    /**
    * 联系云康
    */
    public function contact(){
    	$this->_check_sign();
    	
    	$do=M('help');
    	$id=5;    	
    	$rs=$do->cache(true)->where('id='.$id)->field('name,hit,content')->find();
    	if($rs) {
			$do->where('id='.$id)->setInc('hit',1,60);
    		$rs['content']=html_entity_decode($rs['content']);
    		$this->apiReturn(1,array('data'=>$rs));
    	}else $this->apiReturn(3);    	
    }
	
    /**
    * 注册协议
    */
    public function agreement(){
        $this->_check_sign();
        
        $do=M('help');
        $id=4;       
        $rs=$do->cache(true)->where('id='.$id)->field('name,hit,content')->find();
        if($rs) {
			$do->where('id='.$id)->setInc('hit',1,60);
            $rs['content']=html_entity_decode($rs['content']);
            $this->apiReturn(1,array('data'=>$rs));
        }else $this->apiReturn(3);      
    }
    
    /**
     * 浏览加一
     */
    public function setInc() {
        $this->need_param = ['id'];
        $this->_need_param();
        $this->_check_sign();
        M('help')->where(array('id' => I('post.id')))->setInc('hit', 1);
        $this->apiReturn(1);
    }
    
    /**
     * 后去最新N条数据
     */
    public function getNew() {
        $this->_need_param();
        $this->_check_sign();
        $model = M('help');
        $sortArr    =   ['desc','asc'];
        $num   =   isset($_POST['num']) ?  I('post.num') : '5';
        $order =   isset($_POST['sort']) && in_array(I('post.sort'), $sortArr) ? 'id ' . I('sort') : 'id desc';
        
        $list  =   $model->field('id,name')->where(['status' => 1])->limit($num)->order($order)->select();
        if ($list) {
            $this->apiReturn(1, ['data' => $list]);
        }
        $this->apiReturn(3);
    }

    /**
    * 获取买家帮助分类
    */
    public function category_buyer(){
        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        $list=get_category(['table'=>'help_category','field'=>'id,sid,category_name','sid'=>9,'level'=>2,'cache_name'=>'buyer_help_category']);
        if($list) $this->apiReturn(1,['data' => $list]);
        else $this->apiReturn(3);
    }

    /**
    * 获取卖家帮助分类
    */
    public function category_seller(){
        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        $list=get_category(['table'=>'help_category','field'=>'id,sid,category_name','sid'=>10,'level'=>2,'cache_name'=>'seller_help_category']);
        if($list) $this->apiReturn(1,['data' => $list]);
        else $this->apiReturn(3);
    }
}