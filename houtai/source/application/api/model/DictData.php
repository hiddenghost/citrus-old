<?php

namespace app\api\model;

use app\common\exception\BaseException;
use app\common\model\DictData as DictDataModel;

/**
 * 字典数据模型
 * Class Dict
 * @package app\api\model
 */
class DictData extends DictDataModel
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