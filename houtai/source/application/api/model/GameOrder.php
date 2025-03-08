<?php

namespace app\api\model;

use app\common\model\GameOrder as GameOrderModel;

/**
 * 局订单模型
 * Class GameOrder
 * @package app\api\model
 */
class GameOrder extends GameOrderModel
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