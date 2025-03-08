<?php

namespace app\common\model\im;

use app\common\model\BaseModel;

/**
 * im成员模型
 * Class ImUser
 * @package app\common\model
 */
class User extends BaseModel
{
    protected $name = 'im_user';

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
     * @param $gameId
     * @param $userId
     * @return GameUsers|null
     * @throws \think\exception\DbException
     */
    public static function detail($userId)
    {
        return static::get(['user_id' => $userId], ['user']);
    }

}
