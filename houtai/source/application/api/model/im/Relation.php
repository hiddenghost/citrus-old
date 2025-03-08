<?php

namespace app\api\model\im;

use app\common\model\im\Relation as RelationModel;
use app\api\model\User as UserModel;

/**
 * 关系模型
 * Class User
 * @package app\api\model\dealer
 */
class Relation extends RelationModel
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
     * 更新记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 获取我的好友朋友  即双向关注
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getMyFriendList($filter)
    {
        if(isset($filter['user_id']) && $filter['user_id']) {
            $this->where('relation.user_id', '=', $filter['user_id']);
        }
        return $this->alias('relation')
            ->join('user', 'relation.user_id = user.user_id')
            ->where('relation.is_delete', '=', 0)
            ->where('relation.status', '=', 2)
            ->select()->toArray();
    }

    /**
     * 获取我的好友朋友数量
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getMyFriendCount($user_id)
    {
        $list = $this->where('user_id', '=', $user_id)
            ->where('is_delete', '=', 0)
            ->where('status', '=', 2)
            ->select()
            ->toArray();
        $userList = array_column($list, 'focus_user_id');

        return count((new UserModel)->getFilterUserList($userList));
    }

    /**
     * 我的好友里面的一些人也关注了他
     * @param $user_id
     * @param $friend_user_id
     */
    public function getSamllFriendList($user_id, $friend_user_id)
    {
        $list = $this->where('user_id', '=', $user_id)
            ->where('is_delete', '=', 0)
            ->where('status', '=', 2)
            ->select()->toArray();

        $focus_user_ids = array_column($list, 'focus_user_id');
        $this->where('relation.user_id', 'IN', count($focus_user_ids) > 0  ? $focus_user_ids : []);
        $this->where('relation.focus_user_id', '=', $friend_user_id);

        return $this->alias('relation')
            ->join('user', 'relation.user_id = user.user_id')
            ->where('relation.is_delete', '=', 0)
            ->where('relation.status', 'IN', [1,2])
            ->order(['relation.create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 获取我的新朋友的数量
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getNewFriendCount($user_id)
    {
        return $this->where('focus_user_id', '=', $user_id)
            ->where('is_delete', '=', 0)
            ->where('status', '=', 1)
            ->count();
    }

    /**
     * 获取我的新朋友  即别人关注了我 但是我没有关注别人
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getNewFriendList($filter)
    {
        if(isset($filter['focus_user_id']) && $filter['focus_user_id']) {
            $this->where('relation.focus_user_id', '=', $filter['focus_user_id']);
        }
        if(isset($filter['keyword']) && $filter['keyword']) {
            $this->where('user.nickName|user.name', 'like', '%' . $filter['keyword'] . '%');
        }
        return $this->alias('relation')
            ->join('user', 'relation.user_id = user.user_id')
            ->where('relation.is_delete', '=', 0)
            ->where('relation.status', '=', 1)
            ->order(['relation.create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 获取我的新朋友  即别人关注了我 但是我没有关注别人
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getNewFriendAll($user_id)
    {
        return $this->where('focus_user_id', '=', $user_id)
            ->where('is_delete', '=', 0)
            ->where('status', '=', 1)
            ->select()->toArray();
    }

    /**
     * 获取我的有效朋友
     * 我关注的 和我双向关注的 未删除的
     * @param $user_id
     */
    public function getValidFriendAll($user_id)
    {
        $this->where(function ($query) use ($user_id) {
            $query->whereOr(function ($query2) use ($user_id) {
                $query2->where('user_id', '=', $user_id)
                    ->where('is_delete', '=', 0)
                    ->where('status', 'IN', [1,2]);
            });
            $query->whereOr(function ($query2) use ($user_id) {
                $query2->where('focus_user_id', '=', $user_id)
                    ->where('is_delete', '=', 0)
                    ->where('status', 'IN', [1,2]);
            });
        });

        return $this->select()->toArray();
    }

    /**
     * 获取我拉黑的人
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getBlackFriendAll($user_id)
    {
        return $this->where('user_id', '=', $user_id)
            ->where('is_delete', '=', 0)
            ->where('status', '=', 3)
            ->select()->toArray();
    }

}