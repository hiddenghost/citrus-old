<?php

namespace app\common\model\im;

use app\common\model\BaseModel;

/**
 * im成员关系模型
 * Class ImUser
 * @package app\common\model
 */
class ChatText extends BaseModel
{
    protected $name = 'im_chat_text';

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
            $filter['im_chat_text_id'] = (int)$where;
        }

        return static::get($filter, ['user', 'targetuser']);
    }



}
