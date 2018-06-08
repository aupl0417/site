<?php
namespace Seller\Model;
use Think\Model\ViewModel;
class ActivityViewModel extends ViewModel {
    protected $tableName    =   'activity';
    public $viewFields      =   [
        'activity'          =>  ['*'],
        'activity_type'     =>  ['activity_name', 'id' => 'type_id', 'icon', '_on' => 'activity.type_id = activity_type.id']
    ];
}