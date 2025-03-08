<?php

namespace app\api\service;

use think\Cache;
use app\common\library\emchat\Easemob;
use app\api\model\im\User as ImUserModel;
use app\api\model\im\Relation as RelationModel;
use app\api\model\im\ChatText as ChatTextModel;
use app\api\model\im\ChatFile as ChatFileModel;
use app\api\model\im\Group as GroupModel;
use app\api\model\im\GroupUser as GroupUserModel;
use app\api\model\Game as GameModel;
use app\api\model\im\GroupChat as GroupChatModel;
use app\api\model\im\GroupChatRead as GroupChatReadModel;

class Im
{
    /* @var array $im 对象 */
    private $im;
    /**
     * 构造方法
     */
    public function __construct()
    {
        $options['client_id']       = 'YXA6M9c3BVRmTpetdbZi5qx5VQ';
        $options['client_secret']   = 'YXA6cIeknUOoN7k6W3-Ez2BCYElcQIU';
        $options['org_name']        = '1111230823175035';
        $options['app_name']        = 'demo';

        $this->im = new Easemob($options);
    }

    /**
     * @param $name juzi_用户ID
     * @param $password md5(邮箱+秘钥)
     */
    public function createUser($user_id)
    {
        /* 暂停Im推送 */
        return;
        $imUser = ImUserModel::detail($user_id);
        if($imUser) {
            return $imUser;
        }
        $name = 'juzi_' . $user_id;
        $password = md5($name . '@im123');
        $res = $this->im->createUser($name, $password);
        if(isset($res['error']) && $res['error']) {
            log_write('创建IM用户-'. json_encode($res));
            return;
        }
        //并且在系统中创建IM账号
        return (new ImUserModel)->add([
            'name' => $name,
            'password' => $password,
            'user_id' => $user_id,
            'status' => 1,
        ]);
    }

    /**
     * 获取单个用户信息
     * @param $user_id
     */
    public function getUser($user_id)
    {
        $name = 'juzi_' . $user_id;
        $imUserInfo = $this->im->getUser($name);

        return $imUserInfo;
    }

    /**
     * 添加好友
     * @param $user_id
     * @param $focus_user_id
     */
    public function addFriend($user_id, $focus_user_id)
    {
        $name = 'juzi_' . $user_id;
        $fname = 'juzi_' . $focus_user_id;
        $relation = RelationModel::detail(['user_id' => $user_id, 'focus_user_id' => $focus_user_id]);
        if($relation && $relation['status'] == 3) {
            return ['rs' => false, 'message' => '你已拉黑他'];
        }
        if($relation && ($relation['status'] == 1 || $relation['status'] == 2)) {
            return ['rs' => false, 'message' => '你已关注他'];
        }
        $frelation = RelationModel::detail(['user_id' => $focus_user_id, 'focus_user_id' => $user_id]);
        if($frelation && $frelation['status'] == 3) {
            return ['rs' => false, 'message' => '他已拉黑你'];
        }
        $res = $this->im->addFriend($name, $fname);
        if(isset($res['error'])) {
            log_write('添加好友-'. json_encode($res));
            return ['rs' => false, 'message' => $res['error']];
        }
        $model = new RelationModel;
        $res = $model->add([
            'user_id' => $user_id,
            'focus_user_id' => $focus_user_id,
            'status' => 1
        ]);
        if($res) {
            //判断是否也被对方关注了 如果都被关注则为双向关注即为好友关系
            if($frelation && $frelation['status'] == 1) {
                $frelation->save(['status' => 2]);
                $model->save(['status' => 2], ['im_relation_id' => $model['im_relation_id']]);
            }

            return ['rs' => true, 'message' => '成功'];
        }
        return ['rs' => false, 'message' => '失败'];
    }

    /**
     * 查看我关注的好友列表
     * @param $user_id
     */
    public function showFriends($list, $keyword)
    {
        return (new ImUserModel)->getList(['userList' => $list, 'keyword' => $keyword]);
    }

    /**
     * 删除好友
     * @param $user_id
     */
    public function deleteFriend($user_id, $focus_user_id)
    {
        $name = 'juzi_' . $user_id;
        $fname = 'juzi_' . $focus_user_id;
        $relation = RelationModel::detail(['user_id' => $user_id, 'focus_user_id' => $focus_user_id]);
        if(!$relation) {
            return true;
        }
        $res = $this->im->deleteFriend($name, $fname);
        if($res['entities']) {
            $res = $relation->save(['is_delete' => 1]);
            return $res ? true : false;
        }else{
            return false;
        }
    }

    /**
     * 查看我的黑名单列表
     * @param $user_id
     */
    public function getBlacklist($user_id, $keyword = '')
    {
        $name = 'juzi_' . $user_id;
//        $res = $this->im->getBlacklist($name);
        $list  = (new RelationModel)->getBlackFriendAll($user_id);
        $nameList = [];
        foreach($list as $key => $item) {
            $nameList[] = 'juzi_' . $item['focus_user_id'];
        }

        return (new ImUserModel)->getList(['nameList' => $nameList, 'keyword' => $keyword]);;
    }

    /**
     * 往黑名单中加人
     * @param $user_id
     * @param $focus_user_id
     */
    public function addUserForBlacklist($user_id, $focus_user_id)
    {
        $name = 'juzi_' . $user_id;
        $fname = 'juzi_' . $focus_user_id;
        //可以批量化拉入
        $relation = RelationModel::detail(['user_id' => $user_id, 'focus_user_id' => $focus_user_id]);
        if(!$relation) {
            return ['rs' => false, 'message' => '好友关系不存在'];
        }
        if($relation && $relation['status'] == 3) {
            return ['rs' => false, 'message' => '已在黑名单'];
        }
        $blackList = [
            'usernames' => [$fname]
        ];
        $res = $this->im->addUserForBlacklist($name, $blackList);
        if(isset($res['error']) && $res['error']) {
            log_write('拉入黑名单-'. json_encode($res));
            return ['rs' => false, 'message' => $res['error']];
        }
        if($relation->save(['status' => 3])) {
            return ['rs' => true, 'message' => '成功'];
        }
        return ['rs' => false, 'message' => '失败'];
    }

    /**
     * 从黑名单中减人
     * @param $user_id
     * @param $focus_user_id
     */
    public function deleteUserFromBlacklist($user_id, $focus_user_id)
    {
        $name = 'juzi_' . $user_id;
        $fname = 'juzi_' . $focus_user_id;
        $relation = RelationModel::detail(['user_id' => $user_id, 'focus_user_id' => $focus_user_id]);
        $frelation = RelationModel::detail(['user_id' => $focus_user_id, 'focus_user_id' => $user_id]);
        if(!$relation) {
            return ['rs' => false, 'message' => '好友关系不存在'];
        }
        if($relation && $relation['status'] != 3) {
            return ['rs' => false, 'message' => '已不在黑名单中'];
        }
        $res = $this->im->deleteUserFromBlacklist($name, $fname);
        if(isset($res['error']) && $res['error']) {
            log_write('黑名单减人-'. json_encode($res));
            return ['rs' => false, 'message' => $res['error']];
        }
        if($frelation['status'] == 2) {
            if($relation->save(['status' => 2])) {
                return ['rs' => true, 'message' => '成功'];
            }
        }else{
            if($relation->save(['status' => 1])) {
                return ['rs' => true, 'message' => '成功'];
            }
        }

        return ['rs' => false, 'message' => '失败'];
    }

    /**
     * 查看对方用户是否在线
     * @param $user_id
     */
    public function isOnline($user_id)
    {
        //直接返回已下线 因为影响速度性能
        return 'offline';
        $name = 'juzi_' . $user_id;
        $res = $this->im->isOnline($name);
        if(isset($res['data'])) {
           return $res['data'][$name];
        }
    }

    //--------------------------------------------------------发送消息

    /**
     * 用户文本发送
     * @param $user_id
     * @param $target_user_id
     * @param $content
     * @param $ext
     */
    public function sendUserText($user_id, $target_user_id, $content)
    {
        $user_im = 'juzi_' . $user_id;
        $target_user_im = 'juzi_' . $target_user_id;
        $from = $user_im;
        $target_type = 'users';
        $target = [$target_user_im];
        $ext['a']="a"; //扩展数据
        $startTime = microtime(true);
        $res = $this->im->sendText2($from, $target_type, $target, $content, $ext);
        $endTime = microtime(true);
        log_write('环信发送消息请求时间：' . ($endTime - $startTime));
        if(isset($res['error']) && $res['error']) {
            log_write('发送文本消息-'. json_encode($res));
            return ['rs' => false, 'message' => $res['error_description']];
        }
        $model = new ChatTextModel;
        $res = $model->add([
            'user_id' => $user_id,
            'target_user_id' => $target_user_id,
            'user_im' => $user_im,
            'target_user_im' => $target_user_im,
            'target_type' => $target_type,
            'content' => $content,
            'message_id' => $res['data'][$target_user_im],
            'ext' => json_encode($ext)
        ]);
        if($res) {
            return ['rs' => true, 'data' => ChatTextModel::detail($model['im_chat_text_id'])];
        }else{
            return ['rs' => false, 'message' => '发送失败'];
        }
    }

    /**
     * 用户文件发送 之前前端发送图片消息后后端记录
     * @param $user_id
     * @param $target_user_id
     * @param $image
     */
    public function sendUserImage($data)
    {
        $userIm = explode('_', $data['user_im']);
        $targetUserIm = explode('_', $data['target_user_im']);
        $target_type = 'users';
        //扩展数据
        $ext['width'] = $data['width'];
        $ext['height'] = $data['height'];
        $detail = ChatTextModel::detail(['message_id' => $data['message_id']]);
        if($detail) {
            $detail['ext'] = json_decode($detail['ext']);

            return ['rs' => true, 'data' => $detail];
        }
        $model = new ChatTextModel;
        $res = $model->add([
            'user_id' => $userIm[1],
            'target_user_id' => $targetUserIm[1],
            'user_im' => $data['user_im'],
            'target_user_im' => $data['target_user_im'],
            'target_type' => $target_type,
            'file_path' => $data['file_path'],
            'file_name' => $data['file_name'],
            'file_type' => $data['file_type'],
            'file_size' => $data['file_size'],
            'content' => $data['file_path'],
            'message_id' => $data['message_id'],
            'ext' => json_encode($ext)
        ]);
        if($res) {
            $detail = ChatTextModel::detail($model['im_chat_text_id']);
            $detail['ext'] = json_decode($detail['ext']);

            return ['rs' => true, 'data' => $detail];
        }else{
            return ['rs' => false, 'message' => '发送失败'];
        }
    }

    /**
     * 根据局创建群组
     * @param $game_id
     */
    public function createGroup($game_id)
    {
        if(!$game_id) {
            return ['rs' => false, 'message' => '局不能为空'];
        }
        if(GroupModel::detail(['game_id' => $game_id])) {
            return ['rs' => false, 'message' => '群组已创建'];
        }
        $game = GameModel::detail($game_id);
        $owner = 'juzi_' . $game['user_id'];
        $data = [
            'groupname' => $game['name'],
            'description' => $game['desc'],
            'public' => true, //是否是公开群 true：公开群 false：私有群
            'scale' => 'normal', //群组规模 normal 普通群(群成员总数不超过 3000) large 大型群(群成员总数超过 3000)
            'maxusers' => $game['people'] > 0 ? $game['people'] : 200, //群组最大成员数（包括群主）
            'allowinvites' => false, // 是否允许群成员邀请用户加入群组
            'membersonly' => true, //用户申请入群是否需要群主或者群管理员审批
            'invite_need_confirm' => false, //邀请用户入群时是否需要被邀用户同意
            'owner' => $owner, //群主的用户 ID。
            'members' => [], //群成员
            'custom' => '',
        ];
        $res = $this->im->createGroup($data);
        log_write('创建局群-'. json_encode($res));
        if(isset($res['error']) && $res['error']) {
            log_write('创建局群-'. json_encode($res));
            return ['rs' => false, 'message' => $res['error_description']];
        }
        $model = new GroupModel;
        //创建群聊
        $res = $model->add([
            'groupid' => $res['data']['groupid'],
            'name' => $game['name'],
            'desc' => $game['desc'],
            'public' => $data['public'] ? 1 : 2, //是否是公开群 true：公开群 false：私有群
            'scale' => $data['scale'], //群组规模 normal 普通群(群成员总数不超过 3000) large 大型群(群成员总数超过 3000)
            'maxusers' => $data['maxusers'], //群组最大成员数（包括群主）
            'allowinvites' => $data['allowinvites'] ? 1 : 0, // 是否允许群成员邀请用户加入群组
            'membersonly' => $data['membersonly'] ? 1 : 0, //用户申请入群是否需要群主或者群管理员审批
            'invite_need_confirm' => $data['invite_need_confirm'] ? 1 : 0, //邀请用户入群时是否需要被邀用户同意
            'owner' => $owner, //群主的用户 ID。
            'user_id' => $game['user_id'],
            'game_id' => $game_id,
            'custom' => $data['custom'],
            'status' => 1,
        ]);
        //创建群聊管理员用户
        $groupUser = new GroupUserModel;
        $groupUser->add([
            'name' => $owner,
            'user_id' => $game['user_id'],
            'status' => 1,
            'im_group_id' => $model['im_group_id'],
            'role' => 1
        ]);
        if($res) {
            return ['rs' => true, 'message' => '创建成功'];
        }else{
            return ['rs' => false, 'message' => '创建失败'];
        }
    }

    /**
     * 往群组中加人
     * @param $im_group_id
     * @param $user_id
     * @param int $role
     */
    public function addGroupMember($im_group_id, $user_id, $role = 2)
    {
        $group = GroupModel::detail($im_group_id);
        $name = 'juzi_' . $user_id;
        $res = $this->im->addGroupMember($group['groupid'], $name);
        log_write('往群组中加人-'. json_encode($res));
        if(isset($res['error']) && $res['error']) {
            log_write('往群组中加人-'. json_encode($res));
            return ['rs' => false, 'message' => $res['error_description']];
        }
        //创建群聊用户
        $groupUser = new GroupUserModel;
        $res = $groupUser->add([
            'name' => $name,
            'user_id' => $user_id,
            'status' => 1,
            'im_group_id' => $im_group_id,
            'role' => $role
        ]);
        if($res) {
            return ['rs' => true, 'message' => '创建成功'];
        }else{
            return ['rs' => false, 'message' => '创建失败'];
        }
    }

    /**
     * 往群组中减人
     * @param $im_group_id
     * @param $user_id
     */
    public function deleteGroupMember($im_group_id, $user_id)
    {
        $group = GroupModel::detail($im_group_id);
        $name = 'juzi_' . $user_id;
        $res = $this->im->deleteGroupMember($group['groupid'], $name);
        log_write('往群组中减人-'. json_encode($res));
        if(isset($res['error']) && $res['error']) {
            log_write('往群组中减人-'. json_encode($res));
            return ['rs' => false, 'message' => $res['error_description']];
        }
        //移除群聊用户
        $groupUserDetail = GroupUserModel::detail(['user_id' => $user_id, 'im_group_id' => $im_group_id, 'is_delete' => 0]);
        $res = $groupUserDetail->save(['is_delete' => 1]);
        if($res) {
            return ['rs' => true, 'message' => '创建成功'];
        }else{
            return ['rs' => false, 'message' => '创建失败'];
        }
    }

    /**
     * 用户文本发送
     * @param $user_id
     * @param $target_user_id
     * @param $content
     * @param $ext
     */
    public function sendGroupText($user_id, $im_group_id, $target_group, $game_id, $content)
    {
        $user_im = 'juzi_' . $user_id;
        $from = $user_im;
        $target_type = 'chatgroups';
        $target = [$target_group];
        $ext['game_id']= $game_id; //扩展数据
        $res = $this->im->sendText2($from, $target_type, $target, $content, $ext);
        if(isset($res['error']) && $res['error']) {
            log_write('发送群聊文本消息-'. json_encode($res));
            return ['rs' => false, 'message' => $res['error_description']];
        }
        $model = new GroupChatModel;
        $res = $model->add([
            'user_id' => $user_id,
            'im_group_id' => $im_group_id,
            'user_im' => $user_im,
            'target_group' => $target_group,
            'target_type' => $target_type,
            'content' => $content,
            'message_id' => $res['data'][$target_group],
            'ext' => json_encode($ext)
        ]);
        if($res) {
            //需要同步其他用户未读消息
            (new GroupChatReadModel)->addForChat($user_id, $im_group_id, $model['im_group_chat_id']);
            return ['rs' => true, 'data' => GroupChatModel::detail($model['im_group_chat_id'])];
        }else{
            return ['rs' => false, 'message' => '发送失败'];
        }
    }

    /**
     * 群聊用户文件发送 之前前端发送图片消息后后端记录
     * @param $user_id
     * @param $target_user_id
     * @param $image
     */
    public function sendGroupImage($data)
    {
        $userIm = explode('_', $data['user_im']);
        $target_type = 'chatgroups';
        //扩展数据
        $ext['game_id']= $data['game_id'];
        $ext['width'] = $data['width'];
        $ext['height'] = $data['height'];
        $detail = GroupChatModel::detail(['message_id' => $data['message_id']]);
        if($detail) {
            $detail['ext'] = json_decode($detail['ext']);

            return ['rs' => true, 'data' => $detail];
        }
        $model = new GroupChatModel;
        $res = $model->add([
            'user_id' => $userIm[1],
            'im_group_id' => $data['im_group_id'],
            'user_im' => $data['user_im'],
            'target_group' => $data['target_group'],
            'target_type' => $target_type,
            'file_path' => $data['file_path'],
            'file_name' => $data['file_name'],
            'file_type' => $data['file_type'],
            'file_size' => $data['file_size'],
            'content' => $data['file_path'],
            'message_id' => $data['message_id'],
            'ext' => json_encode($ext)
        ]);
        if($res) {
            (new GroupChatReadModel)->addForChat($userIm[1], $data['im_group_id'], $model['im_group_chat_id']);
            $detail = GroupChatModel::detail($model['im_group_chat_id']);
            $detail['ext'] = json_decode($detail['ext']);

            return ['rs' => true, 'data' => $detail];
        }else{
            return ['rs' => false, 'message' => '发送失败'];
        }
    }


}