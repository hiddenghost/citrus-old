<?php

namespace app\api\model\im;

use app\common\model\im\GroupUser as GroupModel;

/**
 * 群组成员模型
 * Class GroupUser
 * @package app\api\model\dealer
 */
class GroupUser extends GroupModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'update_time',
    ];

    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        $data['status'] = 1;

        return $this->allowField(true)->save($data);
    }

    /**
     * 获取用户参加的群组
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getAll($user_id)
    {
        return $this->where('user_id', '=', $user_id)
            ->where('is_delete', '=', 0)
            ->where('status', '=', 1)
            ->select()->toArray();
    }

    /**
     * 根据群组获得参加的人员数
     * @param $im_group_id
     */
    public function getUserNumsByGroup($im_group_id)
    {
        return $this->where('im_group_id', '=', $im_group_id)
            ->where('is_delete', '=', 0)
            ->where('status', '=', 1)
            ->count();
    }

    /**
     * 获取需要发送消息的用户列表（除了自己外）
     */
    public function getNeedSendMessageUser($user_id, $im_group_id)
    {
        return $this->where('user_id', '<>', $user_id)
            ->where('im_group_id', '=', $im_group_id)
            ->where('is_delete', '=', 0)
            ->where('status', '=', 1)
            ->select()->toArray();
    }

}