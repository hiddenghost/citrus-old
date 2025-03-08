<?php

namespace app\api\model;

use app\common\model\WxUser as WxUserModel;

/**
 * 微信用户模型
 * Class WxUser
 * @package app\api\model
 */
class WxUser extends WxUserModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'create_time',
        'update_time'
    ];

}
