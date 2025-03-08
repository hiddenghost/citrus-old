<?php

namespace app\api\model\im;

use app\common\model\im\GroupChatRead as GroupChatReadModel;
use app\api\model\im\GroupUser as GroupUserModel;

/**
 * 关系模型
 * Class GroupChatRead
 * @package app\api\model\im
 */
class GroupChatRead extends GroupChatReadModel
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
     * 添加未读
     * @param Order $order
     * @param \think\Collection|OrderGoods $goodsList
     * @param $formJsonData
     * @return boolean
     * @throws \Exception
     */
    public function addForChat($user_id, $im_group_id, $im_group_chat_id)
    {
        //获取所有除了自己本身外的群聊其他用户
        $list = (new GroupUserModel)->getNeedSendMessageUser($user_id, $im_group_id);

        return $this->transaction(function () use ($im_group_id, $im_group_chat_id, $list) {
            $data = [];
            foreach($list as $key => $item) {
                $data[] = [
                    'user_id' => $item['user_id'],
                    'im_group_id' => $im_group_id,
                    'im_group_chat_id' => $im_group_chat_id,
                    'is_read'=> 0,
                    'status'=> 1,
                    'wxapp_id' =>self::$wxapp_id,
                    'create_time'=> time()
                ];
            }
            if(count($data) > 0) {
                $this->isUpdate(false)->saveAll($data);
            }

            return true;
        });
    }

    /**
     * 得到我对局群的未读数
     * @param $user_id
     * @param $game_id
     */
    public function getNoReads($user_id, $im_group_id)
    {
        return $this->where('im_group_id', '=', $im_group_id)
            ->where('user_id', $user_id)
            ->where('is_delete', '=', 0)
            ->where('is_read', '=', 0)
            ->count();
    }

    /**
     * 得到我所有局群的未读数
     * @param $user_id
     * @param $game_id
     */
    public function getTotalNoReads($user_id)
    {
        return $this->where('user_id', $user_id)
            ->where('is_delete', '=', 0)
            ->where('is_read', '=', 0)
            ->count();
    }

    /**
     * 更新未读数为已读数
     * @param $user_id
     * @param $im_group_id
     */
    public function updateNoReads($user_id, $im_group_id)
    {
        return $this->save(['is_read' => 1], ['user_id' => $user_id, 'im_group_id' => $im_group_id]);
    }

}