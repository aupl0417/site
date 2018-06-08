<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Behavior;
/**
 * 系统行为扩展：表单令牌生成
 */
class TokenBuildFormBehavior {
    public function run(&$content){
        $cache_name='form_token_'.session_id();
        $page=CONTROLLER_NAME.'/'.ACTION_NAME;
        if(C('TOKEN_ON')) {
            if(!is_null(C('NOTOKEN'))) if(in_array($page,C('NOTOKEN'))) return; //禁用表单令牌的页面

            if(C('TOKEN_TAG')){    //处理自定义标识
                preg_match_all('/'.C('TOKEN_TAG').'/is',$content,$match);
                $n=count($match[0]);
                if($n>0){
                    $tmp=explode('<!--token-->',$content);
                    $token=$this->getToken($n);
                    for($i=0;$i<$n;$i++){
                        $tmp[$i].='<input type="hidden" name="'.C('TOKEN_NAME').'" value="'.$token[$i].'">';
                    }
                    $content=implode('', $tmp);                
                }                
            }else{
                preg_match_all('/<\/form(\s*)>/is',$content,$match);
                $n=count($match[0]);


            }

        }
    }

    /**
    * 创建令牌字符串,用于表单
    * @param integer $n 创建n个令牌
    */
    private function getToken($n=null){
        $cache_name='form_token_'.session_id();
		//$prefix=md5($_SERVER["REQUEST_URI"])'_';
		S($cache_name,null);
        $token=array();
        if(is_null($n)) $token=md5(uniqid(md5(microtime(true)),true));
        elseif(is_array($n)){
            foreach($n as $val){
                $token[$val]=md5(uniqid(md5(microtime(true)),true));
            }
        }else{
            for($i=0;$i<$n;$i++){
                $token[]=md5(uniqid(md5(microtime(true)),true));
            }
        }
        S($cache_name,$token);
        return $token;
    }
}