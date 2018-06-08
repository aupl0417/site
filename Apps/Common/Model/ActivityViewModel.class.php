<?php
namespace Common\Model;
use Think\Model\ViewModel;
class ActivityViewModel extends ViewModel {
    protected $tableName    =   'activity';
    public $viewFields      =   [
        'activity'          =>  ['*', '_type' => 'RIGHT'],
        'activity_type'     =>  ['activity_name','icon', '_on' => 'activity.type_id = activity_type.id AND activity_type.status = 1'],
    ];
}