<?php

namespace app\common\model\user;

use app\common\model\BaseModel;

/**
 * 用户图片模型
 * Class UserImage
 * @package app\common\model\sharing
 */
class Images extends BaseModel
{
    protected $name = 'user_images';
    protected $updateTime = false;

    /**
     * 关联会员记录表
     * @return \think\model\relation\BelongsTo
     */
    public function category()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\Category");
    }

    /**
     * 获取用户信息
     * @param $where
     * @param $with
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($where, $with = [])
    {
        $filter = ['is_delete' => 0];
        if (is_array($where)) {
            $filter = array_merge($filter, $where);
        } else {
            $filter['user_id'] = (int)$where;
        }
        return static::get($filter, $with);
    }

}