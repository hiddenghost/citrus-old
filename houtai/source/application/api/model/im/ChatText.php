<?php

namespace app\api\model\im;

use app\common\model\im\ChatText as ChatTextModel;
use app\api\model\im\Relation as RelationModel;
use app\api\model\User as UserModel;

/**
 * 关系模型
 * Class ChatText
 * @package app\api\model\im
 */
class ChatText extends ChatTextModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'update_time',
        'wxapp_id'
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
        $data['is_read'] = 0;
        $data['status'] = 1;

        return $this->allowField(true)->save($data);
    }

    /**
     * 获取列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($user_id, $target_user_id, $limit = 15)
    {
        $this->where(function ($query) use ($user_id, $target_user_id) {
            $query->whereOr(function ($query2) use ($user_id, $target_user_id) {
                $query2->where('chat.user_id', '=', $user_id)
                    ->where('chat.target_user_id', '=', $target_user_id);
            });
            $query->whereOr(function ($query2) use ($user_id, $target_user_id) {
                $query2->where('chat.target_user_id', '=', $user_id)
                    ->where('chat.user_id', '=', $target_user_id);
            });
        });

        $res = $this->alias('chat')
            ->field(['chat.*,user.avatarUrl,user.nickName,user.country,user.province,user.city'])
            ->join('user', 'chat.user_id = user.user_id')
            ->where('chat.is_delete', '=', 0)
            ->order(['chat.create_time' => 'desc'])
            ->paginate($limit, false, [
                'query' => request()->request()
            ]);

        return $res;
    }

    /**
     * 获取列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getAllList($user_id, $target_user_id)
    {
        $this->where(function ($query) use ($user_id, $target_user_id) {
            $query->whereOr(function ($query2) use ($user_id, $target_user_id) {
                $query2->where('chat.user_id', '=', $user_id)
                    ->where('chat.target_user_id', '=', $target_user_id);
            });
            $query->whereOr(function ($query2) use ($user_id, $target_user_id) {
                $query2->where('chat.target_user_id', '=', $user_id)
                    ->where('chat.user_id', '=', $target_user_id);
            });
        });

        return $this->alias('chat')
            ->field(['chat.*'])
            ->join('user', 'chat.user_id = user.user_id')
            ->where('chat.is_delete', '=', 0)
            ->select()->toArray();
    }

    /**
     * 得到所有给我发过消息和给我发过消息的用户ID列表
     * @param $user_id
     */
    public static function getAllUserId($user_id)
    {
        $self = new static;

        $list = $self->distinct(true)
            ->field('target_user_id')
            ->where('user_id', '=', $user_id)
            ->where('is_delete', '=', 0)
            ->select()
            ->toArray();
        $list = array_column($list, 'target_user_id');

        $list2 = $self->distinct(true)
            ->field('user_id')
            ->where('target_user_id', '=', $user_id)
            ->where('is_delete', '=', 0)
            ->select()
            ->toArray();
        $list2 = array_column($list2, 'user_id');

        foreach($list2 as $user_id) {
            if(!in_array($user_id, $list)) {
                $list[] = $user_id;
            }
        }
        $userList = (new UserModel)->getFilterUserList($list);

        return array_column($userList, 'user_id');
    }

    /**
     * 得到我对我的好友的未读数
     * @param $user_id
     * @param $target_user_id
     */
    public function getNoReads($user_id, $target_user_id)
    {
        if(is_array($target_user_id)) {
            return $this->where('target_user_id', '=', $user_id)
                ->where('user_id', 'IN', $target_user_id)
                ->where('is_delete', '=', 0)
                ->where('is_read', '=', 0)
                ->count();
        }else{
            return $this->where('target_user_id', '=', $user_id)
                ->where('user_id', $target_user_id)
                ->where('is_delete', '=', 0)
                ->where('is_read', '=', 0)
                ->count();
        }

    }

    /**
     * 得到总未读数
     * @param $user_id
     * @return int|string
     * @throws \think\Exception
     */
    public function getTotalNoReads($user_id)
    {
        $list = (new RelationModel)->getValidFriendAll($user_id);
        $userIdList = [];
        foreach($list as $key => $item) {
            if($item['user_id'] == $user_id) {
                $userIdList[] = $item['focus_user_id'];
            }
            if($item['focus_user_id'] == $user_id) {
                $userIdList[] = $item['user_id'];
            }
        }
        $userList = (new UserModel)->getFilterUserList($userIdList);
        $userIdList = array_column($userList, 'user_id');

        return $this->where('target_user_id', '=', $user_id)
            ->where('user_id', '<>', $user_id)
            ->where('user_id', 'IN', $userIdList)
            ->where('is_delete', '=', 0)
            ->where('is_read', '=', 0)
            ->count();
    }

    /**
     * 更新未读数为已读数
     * @param $user_id
     * @param $target_user_id
     */
    public function updateNoReads($user_id, $target_user_id)
    {
        return $this->save(['is_read' => 1], ['target_user_id' => $user_id, 'user_id' => $target_user_id]);
    }

}