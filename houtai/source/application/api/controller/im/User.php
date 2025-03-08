<?php

namespace app\api\controller\im;

use app\api\controller\Controller;
use app\api\model\User as UserModel;
use app\api\model\im\Relation as RelationModel;
use app\api\service\Im as ImService;
use app\api\model\GameUsers as GameUsersModel;
use app\common\service\Push as Push;

/**
 * IMUser服务
 * Class service
 * @package app\api\controller\user\order
 */
class User extends Controller
{
    /* @var \app\api\model\User $user */
    private $user;

    private $_imService;

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
    }

    /**
     * 得到用户详情
     * @param bool $user_id
     * @return \app\api\model\User|bool|void|null
     */
    public function user($user_id)
    {
        $detail = $this->_imService->getUser($user_id);
        $isonline = $this->_imService->isOnline($user_id);

        return $this->renderSuccess(compact('detail', 'isonline'));
    }

    /**
     * 添加好友
     * @param $user_id
     * @param $focus_user_id
     */
    public function addfriend($focus_user_id)
    {
        $detail = $this->_imService->addFriend($this->user['user_id'], $focus_user_id);
        if($detail['rs']) {
            $this->_imService->sendUserText($this->user['user_id'], $focus_user_id, '很高兴认识你！');
            $push = new Push;
            $content = '有人想成为你的好友！';
            if($this->user['name']) {
                $content = $this->user['name'] . '想成为你的好友！';
            }
            if($this->user['nickName']) {
                $content = $this->user['nickName'] . '想成为你的好友！';
            }

            $push->pushsync($focus_user_id, ['title' => 'Citrus', 'content' => $content, 'game_id' => 0]);
        }

        return $detail['rs'] ? $this->renderSuccess('关注成功') : $this->renderError($detail['message']);
    }

    /**
     * 查看好友列表 即相互关注的
     * @param $user_id
     */
    public function showfriend($keyword = '')
    {
        $model = new RelationModel;
        $friendList = $model->getMyFriendList(['user_id' => $this->user['user_id']]);
        $list = $this->_imService->showFriends(array_column($friendList, 'focus_user_id'), $keyword);
        if($list) {
            $list->each(function($item, $key) {
//                $item['isonline'] = $this->_imService->isOnline($item['user_id']);
                $item['common_games'] = (new GameUsersModel)->getCiGame($this->user['user_id'], $item['user_id'], true); //共同的局的数量
                //加上最后的聊天记录

                return $item;
            });
        }
        $newfriend = $model->getNewFriendCount($this->user['user_id']);

        return $this->renderSuccess(compact('list', 'newfriend'));
    }

    /**
     * 删除好友
     * @param $user_id
     * @param $focus_user_id
     */
    public function delfriend($focus_user_id)
    {
        $res = $this->_imService->deleteFriend($this->user['user_id'], $focus_user_id);

        return $res ? $this->renderSuccess('取关成功') : $this->renderError('取关失败');
    }

    /**
     * 新的朋友列表 即别人关注了我 但是我没有关注别人
     */
    public function newfriend($keyword)
    {
        $model = new RelationModel;
        $list = $model->getNewFriendList(['focus_user_id' => $this->user['user_id'], 'keyword' => $keyword]);
//        if($list) {
//            $list->each(function($item, $key) {
////                $item['isonline'] = $this->_imService->isOnline($item['user_id']);
//                //加上最后的聊天记录
//
//
//                return $item;
//            });
//        }


        return $this->renderSuccess(compact('list'));
    }

    /**
     * 查看我的黑名单列表
     * @param $user_id
     */
    public function blacklist($keyword = '')
    {
        $list = $this->_imService->getBlacklist($this->user['user_id'], $keyword);

        if($list) {
            $list->each(function($item, $key) {
//                $item['isonline'] = $this->_imService->isOnline($item['user_id']);
                //加上最后的聊天记录

                return $item;
            });
        }


        return $this->renderSuccess(compact('list'));
    }

    /**
     * 拉黑用户
     * @param $user_id
     * @param $focus_user_id
     */
    public function addblack($focus_user_id)
    {
        $detail = $this->_imService->addUserForBlacklist($this->user['user_id'],$focus_user_id);

        return $detail['rs'] ? $this->renderSuccess() : $this->renderError($detail['message']);
    }

    /**
     * 将用户从黑名单中清除
     * @param $user_id
     * @param $focus_user_id
     */
    public function delblack($focus_user_id)
    {
        $detail = $this->_imService->deleteUserFromBlacklist($this->user['user_id'], $focus_user_id);

        return $detail['rs'] ? $this->renderSuccess() : $this->renderError($detail['message']);
    }

}