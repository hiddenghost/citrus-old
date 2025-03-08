<?php

namespace app\common\model\im;

use app\common\model\BaseModel;

/**
 * im成员关系模型
 * Class GroupChatRead
 * @package app\common\model
 */
class GroupChatRead extends BaseModel
{
    protected $name = 'im_group_chat_read';

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
    public function group()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\Group", 'im_group_id', 'im_group_id');
    }

    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function chat()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\im\\GroupChat", 'im_group_chat_id', 'im_group_chat_id');
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
            $filter['im_group_chat_read_id'] = (int)$where;
        }

        return static::get($filter, ['user', 'group', 'chat']);
    }



}
