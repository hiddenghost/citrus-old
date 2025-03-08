<?php

namespace app\api\model;

use app\common\model\GameCategory as GameCategoryModel;

/**
 * 局分类模型
 * Class Category
 * @package app\common\model
 */
class GameCategory extends GameCategoryModel
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
