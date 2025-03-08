<?php

namespace app\api\controller\im;

use app\api\controller\Controller;
use app\api\model\im\Relation as RelationModel;
use app\api\service\Im as ImService;
use app\api\model\im\ChatText as ChatTextModel;
use app\api\model\im\User as ImUserModel;
use app\api\model\City as CityModel;
use app\api\model\im\Group as GroupModel;
use app\api\model\im\GroupUser as GroupUserModel;
use app\api\model\GameImage as GameImageModel;
use app\api\model\im\GroupChat as GroupChatModel;
use app\api\model\im\GroupChatRead as GroupChatReadModel;

/**
 * IMChat服务
 * Class service
 * @package app\api\controller\user\order
 */
class Group extends Controller
{
    /* @var \app\api\model\User $user */
    private $user;

    private $_imService;

    private $_model;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->user = $this->getUser(true);   // 用户信息
        $this->_imService = new ImService;
        $this->_model = new GroupModel;
    }

    /**
     * 获取到用户所有的群组
     */
    public function lists($keyword = '')
    {
        $list = (new GroupModel)->getList($this->user['user_id'], $keyword);
        $list->each(function($item, $key) {
            $item['join_peoples'] = (new GroupUserModel)->getUserNumsByGroup($item['im_group_id']);
            $item['image'] = (new GameImageModel)->getGameImage($item['game_id']);
            $chatList = (new GroupChatModel)->getList($item['im_group_id'], 1)->toArray();
            $item['chat'] = null;
            $item['chat_time'] = '';
            if(isset($chatList['data']) && count($chatList['data']) > 0) {
                $chat = $chatList['data'][0];
                if($chat['ext']) {
                    $chat['ext'] = json_decode($chat['ext']);
                }
                $item['chat'] = $chat;
                $item['chat_time'] = $chatList['data'][0]['create_time'];
                $item['chat_time_label'] = formatTime($item['chat_time']);
                if($chatList['data'][0]['city']) {
                    $city = CityModel::detail($chatList['data'][0]['city']);
                    $item['city_name'] = $city['name'];
                }
            }
            $item['no_reads'] = (new GroupChatReadModel)->getNoReads($this->user['user_id'], $item['im_group_id']);

            return $item;
        });
        $list = $list->toArray();
        if(count($list['data']) > 0) {
            $data = $list['data'];
            sort_array_multi($data, ['chat_time'], ['desc']);
            $list['data'] = $data;
        }



        return $this->renderSuccess(compact('list'));
    }

    /**
     * 群组详情
     * @param $im_group_id
     */
    public function detail($im_group_id)
    {
        $detail = GroupModel::detail($im_group_id);
        $gameImage = new GameImageModel;
        $detail['image'] = $gameImage->getGameImage($detail['game_id']);
        $userList = $detail['joinuser'];
        foreach($userList as $key => $item) {
            $item['is_friend'] = 0;
            if($this->user['user_id'] == $item['user_id']) {
                $item['is_friend'] = 1;
            }else{
                if(RelationModel::detail(['user_id' => $this->user['user_id'], 'focus_user_id' => $item['user_id'], 'status' => 2])
                    ||
                    RelationModel::detail(['focus_user_id' => $this->user['user_id'], 'user_id' => $item['user_id'], 'status' => 2])
                ){
                    $item['is_friend'] = 1;
                }
            }

            $userList[$key] = $item;
        }
        $detail['joinuser'] = $userList;

        return $this->renderSuccess(compact('detail'));
    }

}