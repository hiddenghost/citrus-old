<?php

namespace app\common\model\blog;

use app\common\model\BaseModel;

/**
 * Great模型
 * Class Great
 * @package app\common\model
 */
class Great extends BaseModel
{
    protected $name = 'blog_great';

    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\User");
    }

    /**
     * 获取Im信息详情
     * @param $blog_great_id
     * @return GameUsers|null
     * @throws \think\exception\DbException
     */
    public static function detail($blog_great_id)
    {
        return static::get($blog_great_id, ['user']);
    }

}
