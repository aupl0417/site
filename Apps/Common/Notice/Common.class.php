<?php
namespace Common\Notice;
use Think\Controller;
class Common extends Controller {
    public function _initialize() {
		$tmp=C('cfg.api');
		unset($tmp['apiurl']);
		$this->api_cfg=$tmp;
		$this->api_url=C('cfg.api')['apiurl'];
	}
}