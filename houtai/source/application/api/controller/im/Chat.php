<?php

namespace app\api\controller\im;

use app\api\controller\Controller;
use app\api\model\im\Relation as RelationModel;
use app\api\service\Im as ImService;
use app\api\model\im\ChatText as ChatTextModel;
use app\api\model\im\User as ImUserModel;
use app\api\model\City as CityModel;
use app\api\model\im\GroupChatRead as GroupChatReadModel;
use app\api\model\im\Group as GroupModel;

/**
 * IMChat服务
 * Class service
 * @package app\api\controller\user\order
 */
class Chat extends Controller
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
     * 发送用户文本消息
     */
    public function sendusertext()
    {
        $param = $this->request->param();
        if(isEmpty($param['target_user_id'])) {
            return $this->renderError('目标用户ID不能为空');
        }
        //解决表情发送不了的问题
        if(isset($_POST['content']) && $_POST['content']) {
            $param['content'] = $_POST['content'];
        }
        if(isset($_GET['content']) && $_GET['content']) {
            $param['content'] = $_GET['content'];
        }
        if(isEmpty($param['content'])) {
            return $this->renderError('文本消息内容不能为空');
        }
        $param['content'] = urldecode($param['content']);
        //需要验证目标用户 如果你在接受者的黑名单内则不允许发生
        $verify = $this->_verifySend($param['target_user_id']);
        if(!$verify['rs']) {
            return $this->renderError($verify['message']);
        }

        $res = $this->_imService->sendUserText($this->user['user_id'], $param['target_user_id'], $param['content']);

        return $res['rs'] ? $this->renderSuccess($res['data']) : $this->renderError($res['message']);
    }

    /**
     * 发送用户图片消息
     */
    public function senduserimage()
    {
        $param = $this->request->param();
        if(isEmpty($param['from'])) {
            return $this->renderError('发送人不能为空');
        }
        if(isEmpty($param['to'])) {
            return $this->renderError('接受人不能为空');
        }
        if(isEmpty($param['remotePath'])) {
            return $this->renderError('图片地址不能为空');
        }
        if(isEmpty($param['msgId'])) {
            return $this->renderError('消息ID不能为空');
        }
        //需要验证目标用户 如果你在接受者的黑名单内则不允许发生
//        $verify = $this->_verifySend($param['target_user_id']);
//        if(!$verify['rs']) {
//            return $this->renderError($verify['message']);
//        }
        $data = [
            'user_im' => $param['from'],
            'target_user_im' => $param['to'],
            'file_type' => 'img',
            'file_path' => $param['remotePath'],
            'file_name' => $param['secret'],
            'width' => $param['width'],
            'height' => $param['height'],
            'file_size' => $param['fileSize'],
            'message_id'=> $param['msgId'],
        ];
        $res = $this->_imService->sendUserImage($data);

        return $res['rs'] ? $this->renderSuccess($res['data']) : $this->renderError($res['message']);
    }

    /**
     * 验证发送
     * @param $target_user_id
     * @return array|bool[]
     * @throws \think\exception\DbException
     */
    private function _verifySend($target_user_id)
    {
        $relation = RelationModel::detail(['user_id' => $target_user_id, 'focus_user_id' => $this->user['user_id'], 'status' => 3]);
        if($relation) {
            return ['rs' => false, 'message' => '您在对方的黑名单内不允许发送消息'];
        }

        return ['rs' => true];
    }

    /**
     * 未读数 朋友聊天、陌生人聊天、群聊聊天
     */
    public function noreads()
    {
        $model = new RelationModel;
        $friendList = $model->getMyFriendList(['user_id' => $this->user['user_id']]);
        $focusUserMap = array_column($friendList, 'focus_user_id');
        $chatTextModel = new ChatTextModel;
        $friend = $chatTextModel->getNoReads($this->user['user_id'], $focusUserMap);

        $allUserList = ChatTextModel::getAllUserId($this->user['user_id']); //得到所有与之聊天的用户
        $strangerlist = [];
        foreach($allUserList as $key => $user_id) {
            if(!in_array($user_id, $focusUserMap)) {
                $strangerlist[] = $user_id;
            }
        }
        log_write(json_encode($allUserList) . '-11111');
        log_write(json_encode($strangerlist) . '-22222222');
        $stranger = $chatTextModel->getNoReads($this->user['user_id'], $strangerlist);

        $list = (new GroupModel)->getList($this->user['user_id'], '', 100);
        $this->_group_noreads = 0;
        $list->each(function($item, $key) {
            $this->_group_noreads += (new GroupChatReadModel)->getNoReads($this->user['user_id'], $item['im_group_id']);
        });
        $data = [
            'friend' => $friend,
            'stranger' => $stranger,
            'group' => $this->_group_noreads,
        ];

        return $this->renderSuccess(compact('data'));
    }

    private $_group_noreads;

    /**
     * 好友消息列表
     */
    public function friendlist()
    {
        //获取好友列表
        $model = new RelationModel;
        $friendList = $model->getMyFriendList(['user_id' => $this->user['user_id']]);
        $focusUserMap = array_column($friendList, null, 'focus_user_id');
        $list = (new ImUserModel)->getAll(['userList' => array_column($friendList, 'focus_user_id')]);
        $chatTextModel = new ChatTextModel;
        foreach($list as $key => &$item) {
            $item['isonline'] = $this->_imService->isOnline($item['user_id']);
            $chatList = $chatTextModel->getList($this->user['user_id'], $item['user_id'], 1)->toArray();
            $item['chat'] = null;
            $item['chat_time'] = '';
            if($item['city']) {
                $city = CityModel::detail($item['city']);
                $item['city_name'] = $city['name'];
            }
            $item['no_reads'] = $chatTextModel->getNoReads($this->user['user_id'], $item['user_id']);
            if(isset($chatList['data']) && count($chatList['data']) > 0) {
                $item['chat'] = $chatList['data'][0];
                $item['chat']['ext'] = json_decode($item['chat']['ext']);
                $item['chat_time'] = $chatList['data'][0]['create_time'];
                $item['chat_time_label'] = formatTime($item['chat_time']);
            }else{
                unset($list[$key]);
            }

        }
        if($list) {
            sort_array_multi($list, ['chat_time'], ['desc']);
        }

        return $this->renderSuccess(compact('list'));
    }

    /**
     * 单聊 置顶或者取消置顶的用户
     * @param $user_id
     */
    public function istop($user_id)
    {
        $record = RelationModel::detail(['user_id' => $this->user['user_id'], 'focus_user_id' => $user_id]);
        $is_top = $record['is_top'] > 0 ? 0 : 1;
        $record->save(['is_top' => $is_top]);

        return $this->renderSuccess();
    }

    /**
     * 陌生人消息列表
     * 改逻辑：只要是发了消息的 但是 去掉双向为好友的
     */
    public function strangerlist()
    {
        $model = new RelationModel;
        $allUserList = ChatTextModel::getAllUserId($this->user['user_id']); //得到所有与之聊天的用户
        $friendList = $model->getMyFriendList(['user_id' => $this->user['user_id']]);
        $friendMap = array_column($friendList, 'focus_user_id');
        $userList = [];
        foreach($allUserList as $key => $user_id) {
            if(!in_array($user_id, $friendMap)) {
               $userList[] = $user_id;
            }
        }
        $list = (new ImUserModel)->getAll(['userList' => $userList]);
        log_write(json_encode(array_column($list, 'user_id')) . '-77777777');
        $chatTextModel = new ChatTextModel;
        foreach($list as $key => &$item) {
            $item['isonline'] = $this->_imService->isOnline($item['user_id']);
            $chatList = $chatTextModel->getList($this->user['user_id'], $item['user_id'], 1)->toArray();
            $item['chat'] = null;
            $item['chat_time'] = '';
            if(isset($chatList['data']) && count($chatList['data']) > 0) {
                $item['chat'] = $chatList['data'][0];
                $item['chat']['ext'] = json_decode($item['chat']['ext']);
                $item['chat_time'] = $chatList['data'][0]['create_time'];
                $item['chat_time_label'] = formatTime($item['chat_time']);
            }
            if($item['city']) {
                $city = CityModel::detail($item['city']);
                $item['city_name'] = $city['name'];
            }
            $item['no_reads'] = $chatTextModel->getNoReads($this->user['user_id'], $item['user_id']);
        }
        if($list) {
            sort_array_multi($list, ['chat_time'], ['desc']);
        }

        return $this->renderSuccess(compact('list'));
    }

    /**
     * 聊天详细列表
     */
    public function detail($target_user_id)
    {
        $model = new ChatTextModel;
        $list = $model->getList($this->user['user_id'], $target_user_id);
        $list->each(function($item, $key) {
            $item['ext'] = json_decode($item['ext']);
            if($item['user_id'] == $this->user['user_id']) {
                $item['location'] = 'right';
            }else{
                $item['location'] = 'left';
            }

            return $item;
        });
        $detail = ImUserModel::detail($target_user_id);
        $detail['isonline'] = $this->_imService->isOnline($target_user_id);
        $model->updateNoReads($this->user['user_id'], $target_user_id);

        return $this->renderSuccess(compact('list', 'detail'));
    }

    /**
     * 清空聊天
     * @param $target_user_id
     */
    public function emptychat($target_user_id)
    {
        $model = new ChatTextModel;
        $list = $model->getAllList($this->user['user_id'], $target_user_id);
        $ids = array_column($list, 'im_chat_text_id');
        if($ids) {
            $data = [];
            foreach($ids as $id) {
                $data[] = [
                    'data' => ['is_delete' => 1],
                    'where'=> ['im_chat_text_id' => $id]
                ];
            }
            $model->updateAll($data);
        }

        return $this->renderSuccess();
    }

    /**
     * 标记信息已读
     * @param $message_id
     */
    public function isread($message_id)
    {
        if(!$message_id) {
            return $this->renderError('消息ID不能为空');
        }
        $message = ChatTextModel::detail(['message_id' => $message_id]);
        if(!$message) {
            return $this->renderError('消息不存在');
        }
        $message->save(['is_read' => 1]);

        return $this->renderSuccess();
    }

    /**
     * 底部菜单栏消息未读数
     */
    public function totalnoread()
    {
//        $chatTotal = (new ChatTextModel)->getTotalNoReads($this->user['user_id']);
//        $groupTotal = (new GroupChatReadModel)->getTotalNoReads($this->user['user_id']);
//        $total = $chatTotal + $groupTotal;


        $model = new RelationModel;
        $friendList = $model->getMyFriendList(['user_id' => $this->user['user_id']]);
        $focusUserMap = array_column($friendList, 'focus_user_id');
        $chatTextModel = new ChatTextModel;
        $friend = $chatTextModel->getNoReads($this->user['user_id'], $focusUserMap);

        $allUserList = ChatTextModel::getAllUserId($this->user['user_id']); //得到所有与之聊天的用户
        $strangerlist = [];
        foreach($allUserList as $key => $user_id) {
            if(!in_array($user_id, $focusUserMap)) {
                $strangerlist[] = $user_id;
            }
        }
        log_write(json_encode($allUserList) . '-11111');
        log_write(json_encode($strangerlist) . '-22222222');
        $stranger = $chatTextModel->getNoReads($this->user['user_id'], $strangerlist);

        $list = (new GroupModel)->getList($this->user['user_id'], '', 100);
        $this->_group_noreads = 0;
        $list->each(function($item, $key) {
            $this->_group_noreads += (new GroupChatReadModel)->getNoReads($this->user['user_id'], $item['im_group_id']);
        });

        $total = $friend + $stranger + $this->_group_noreads;

        return $this->renderSuccess(compact('total'));
    }

}