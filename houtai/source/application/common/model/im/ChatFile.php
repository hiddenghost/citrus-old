<?php

namespace app\common\model\im;

use app\common\model\BaseModel;

/**
 * im成员关系模型
 * Class ChatFile
 * @package app\common\model
 */
class ChatFile extends BaseModel
{
    protected $name = 'im_chat_file';

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
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function targetuser()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\User", 'target_user_id', 'user_id');
    }

    /**
     * 获取Im信息详情
     * @param $gameId
     * @param $userId
     * @return GameUsers|null
     * @throws \think\exception\DbException
     */
    public static function detail($where)
    {
        $filter = ['is_delete' => 0];
        if (is_array($where)) {
            $filter = array_merge($filter, $where);
        } else {
            $filter['im_chat_file_id'] = (int)$where;
        }

        return static::get($filter, ['user', 'targetuser']);
    }



}
