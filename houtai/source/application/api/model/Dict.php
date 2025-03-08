<?php

namespace app\api\model;

use app\common\exception\BaseException;
use app\common\model\Dict as DictModel;

/**
 * 字典模型
 * Class Dict
 * @package app\api\model
 */
class Dict extends DictModel
{
    /**
     * 追加字段
     * @var array
     */
    protected $append = [

    ];

    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'is_delete',
        'wxapp_id',
        'create_time',
        'update_time'
    ];

}