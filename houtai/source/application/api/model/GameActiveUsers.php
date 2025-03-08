<?php

namespace app\api\model;

use app\common\model\GameActiveUsers as ActiveUsersModel;

/**
 * 拼团拼单成员模型
 * Class GameActiveUsers
 * @package app\api\model
 */
class GameActiveUsers extends ActiveUsersModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'create_time',
    ];

}
