<?php
namespace Admin\Model;
use Think\Model\ViewModel;
class ConfigViewModel extends ViewModel {
   public $viewFields = array(
     'config'=>array('*'),
     'config_sort'=>array('ac', '_on'=>'config.sid=config_sort.id'),
   );
}
?>