<?php
namespace Home\Controller;
class TopbarController extends CommonController {

    function topbar(){
        if(session('user.id') >0 ){
            $result['topbar']   = '<a target="_blank" class="text_black" href="'.DM('seller').'"><span class="text_yellow">'.session('user.nick').'</span></a>
                                <a class="text_black" href="'.DM('user', '/logout').'">退出登录</a>';

            $result['user']     ='<img class="img-circle mg10 border-1px" width="80" src="'.session('user.face').'"><h5>嗨，你好！</h5>
                                    <p>
                                        <a href="'.DM('my').'" class="btn btn-rad btn-xs btn-primary btn_m plr20">'.session('user.nick').'</a>
                                    </p>';

            $this->authApi('/Cart/cart_total');
            $result['cart']     = $this->_data['data']['style_num'];

        }else{
            $result['topbar'] = '<a class="text_black" href="'.DM('user', '/login').'">请登录</a>
                                <a class="text_black" href="'.DM('user', '/register').'">免费注册</a>';

            $result['user']     ='<img class="img-circle mg10" width="80" src="/Public/images/face.jpg"><h5>嗨，你好！</h5>
                                    <p>
                                        <a href="'.DM('user','/login').'" class="btn btn-xs btn-primary btn_m plr20 mr15">登录</a>
                                        <a href="'.DM('user','/register').'" class="btn btn-xs btn-primary btn_m plr20">注册</a>
                                    </p>';

            $result['cart']     = 0;
        }

        $goods_q=cookie('goods_q');
        arsort($goods_q);

        $result['keywords'] = array();
        if($goods_q){

            foreach($goods_q as $i => $val){
                $result['keywords'][$i]    =   urldecode($val);
            }
        }   

        $this->ajaxReturn($result);
    }
    
    
    public function indexSide() {
        if (IS_POST) {
            $code = 1;
            $data = [
                'face' => '/Public/images/wait.png',
                'nick' => '<a class="btn btn-xs btn-primary btn_m plr20 mr15" href="'.DM('user', '/login').'">登录</a><a class="btn btn-xs btn-primary btn_m plr20" href="'.DM('user', '/register').'">注册</a>',
            ];
            if (session('user.id') > 0) {
                $data['nick'] = '<a href="'.DM('my').'" class="btn btn-rad btn-xs btn-primary btn_m plr20">'.session('user.nick').'</a>';
                $data['face'] = myurl(session('user.face'));
            }
            $this->ajaxReturn(['code' => $code, 'data' => $data]);
        }
    }
}