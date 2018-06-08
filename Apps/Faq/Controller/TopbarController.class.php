<?php
namespace Faq\Controller;
use Home\Controller\CommonController;
class TopbarController extends CommonController {

    function topbar(){
        A('Home/Topbar')->topbar();
    }

}