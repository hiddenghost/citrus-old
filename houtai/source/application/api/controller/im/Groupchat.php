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
use app\api\model\im\GroupChat as GroupChatModel;
use app\api\model\im\GroupChatRead as GroupChatReadModel;

/**
 * IMChat服务
 * Class service
 * @package app\api\controller\user\order
 */
class Groupchat extends Controller
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
        if(isEmpty($param['im_group_id'])) {
            return $this->renderError('目标群ID不能为空');
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
        $group = GroupModel::detail($param['im_group_id']);
        if(!$group) {
            return $this->renderError('局群记录不存在');
        }
        //需要验证目标用户 如果你在接受者的黑名单内则不允许发生
        $verify = $this->_verifySend($param['im_group_id']);
        if(!$verify['rs']) {
            return $this->renderError($verify['message']);
        }
        $res = $this->_imService->sendGroupText($this->user['user_id'], $param['im_group_id'], $group['groupid'], $group['game_id'], $param['content']);

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
            return $this->renderError('聊天室不能为空');
        }
        if(isEmpty($param['remotePath'])) {
            return $this->renderError('图片地址不能为空');
        }
        if(isEmpty($param['msgId'])) {
            return $this->renderError('消息ID不能为空');
        }
        $group = GroupModel::detail(['groupid' => $param['to']]);
        if(!$group) {
            return $this->renderError('局群记录不存在');
        }
        //需要验证目标用户 如果你在接受者的黑名单内则不允许发生
        $verify = $this->_verifySend($group['im_group_id']);
        if(!$verify['rs']) {
            return $this->renderError($verify['message']);
        }
        $data = [
            'im_group_id' => $group['im_group_id'],
            'game_id' => $group['game_id'],
            'user_im' => $param['from'],
            'target_group' => $param['to'],
            'file_type' => 'img',
            'file_path' => $param['remotePath'],
            'file_name' => $param['secret'],
            'width' => $param['width'],
            'height' => $param['height'],
            'file_size' => $param['fileSize'],
            'message_id'=> $param['msgId'],
        ];
        $res = $this->_imService->sendGroupImage($data);

        return $res['rs'] ? $this->renderSuccess($res['data']) : $this->renderError($res['message']);
    }

    /**
     * 验证发送
     * @param $im_group_id
     * @return array|bool[]
     * @throws \think\exception\DbException
     */
    private function _verifySend($im_group_id)
    {
        $user = GroupUserModel::detail(['im_group_id' => $im_group_id, 'user_id' => $this->user['user_id'], 'status' => 1, 'is_delete' => 0]);
        if(!$user) {
            return ['rs' => false, 'message' => '您不在该群组里面'];
        }

        return ['rs' => true];
    }

    /**
     * 聊天详细列表
     */
    public function lists($im_group_id)
    {
        $model = new GroupChatModel;
        $list = $model->getList($im_group_id);
        $list->each(function($item, $key) {
            $item['ext'] = json_decode($item['ext']);
            $item['chat_time'] = $item['create_time'];
            $item['chat_time_label'] = formatTime($item['chat_time']);
            if($item['user_id'] == $this->user['user_id']) {
                $item['location'] = 'right';
            }else{
                $item['location'] = 'left';
            }

            return $item;
        });
        (new GroupChatReadModel())->updateNoReads($this->user['user_id'], $im_group_id);

        return $this->renderSuccess(compact('list'));
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

}