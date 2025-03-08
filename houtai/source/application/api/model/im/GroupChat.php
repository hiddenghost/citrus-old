<?php

namespace app\api\model\im;

use app\common\model\im\GroupChat as GroupChatModel;

/**
 * 关系模型
 * Class GroupChat
 * @package app\api\model\im
 */
class GroupChat extends GroupChatModel
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
     * 获取我的群消息列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($im_group_id, $limit = 15)
    {

        return $this->alias('chat')
            ->field(['chat.*,user.avatarUrl,user.nickName,user.country,user.province,user.city'])
            ->join('user', 'chat.user_id = user.user_id')
            ->where('chat.im_group_id', '=', $im_group_id)
            ->where('chat.is_delete', '=', 0)
            ->order(['chat.create_time' => 'desc'])
            ->paginate($limit, false, [
                'query' => request()->request()
            ]);
    }

}