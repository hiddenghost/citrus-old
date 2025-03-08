<?php

namespace app\common\model\im;

use app\common\model\BaseModel;

/**
 * im成员关系模型
 * Class ImUser
 * @package app\common\model
 */
class Relation extends BaseModel
{
    protected $name = 'im_relation';

    protected $append = ['user_name', 'focus_user_name'];

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
    public function focususer()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\User", 'focus_user_id', 'user_id');
    }

    /**
     * 获取器：Im用户名
     * @param $value
     * @return array
     */
    public function getUserNameAttr($value, $data)
    {
        return 'juzi_' . $data['user_id'];
    }

    /**
     * 获取器：Im用户名
     * @param $value
     * @return array
     */
    public function getFocusUserNameAttr($value, $data)
    {
        return 'juzi_' . $data['focus_user_id'];
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
            $filter['im_relation_id'] = (int)$where;
        }

        return static::get($filter, ['user', 'focususer']);
    }

    /**
     * 得到用户关注列表 status 0陌生人   1单项关注 2双向关注 3拉黑
     * @param $user_id
     * @param $status
     */
    public function getFocusList($user_id, $status = 1)
    {
        return $this->where('is_delete', '=', 0)
            ->where('user_id', '=', $user_id)
            ->where('status', '=', $status)
            ->select();
    }



}
