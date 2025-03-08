<?php

namespace app\common\model\blog;

use app\common\model\BaseModel;

/**
 * Comment模型
 * Class Comment
 * @package app\common\model
 */
class Comment extends BaseModel
{
    protected $name = 'blog_comment';

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
     * @param $blog_comment_id
     * @return GameUsers|null
     * @throws \think\exception\DbException
     */
    public static function detail($blog_comment_id)
    {
        return static::get($blog_comment_id, ['user']);
    }

}
