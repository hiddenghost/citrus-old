<?php

namespace app\common\service;

use app\common\library\emchat\Easemob;
use app\common\model\User as UserModel;
use app\common\model\im\User as ImUserModel;
use app\common\model\push\Tag as PushTagModel;
use app\common\model\push\TagUser as PushTagUserModel;
use app\common\model\push\Task as PushTaskModel;
use app\common\model\push\Log as PushLogModel;


/**
 * 环信推送
 * Class Push
 * @package app\store\service
 */
class Push
{
    /* @var array $push 推送对象 */
    private $push;
    /**
     * 构造方法
     */
    public function __construct()
    {
        $options['client_id']       = 'YXA6M9c3BVRmTpetdbZi5qx5VQ';
        $options['client_secret']   = 'YXA6cIeknUOoN7k6W3-Ez2BCYElcQIU';
        $options['org_name']        = '1111230823175035';
        $options['app_name']        = 'demo';

        $this->push = new Easemob($options);
    }

    /**
     * 创建推送标签
     * @param $name
     * @param $description
     */
    public function addPushLabel($name, $description)
    {
        //暂停Im推送
        return ['rs' => true];
        $tag = PushTagModel::detail(['name' => $name]);
        if($tag) {
            return ['rs' => false, 'message' => '不能创建重复标签'];
        }
        $res = $this->push->addPushLabel($name, $description);
        log_write('创建推送标签-'. json_encode($res));
        if(isset($res['error']) && $res['error']) {
            return ['rs' => false, 'message' => $res['error']];
        }
        $res = (new PushTagModel())->add(['name' => $name, 'description' => $description, 'status' => 1, 'total_nums' => 0]);
        return $res ? ['rs' => true] : ['rs' => false, 'message' => '创建失败'];
    }

    /**
     * 删除指定的标签
     * @param $name
     * @param $description
     */
    public function deletePushLabel($name)
    {
        //暂停Im推送
        return ['rs' => true];
        $tag = PushTagModel::detail(['name' => $name]);
        if(!$tag) {
            return ['rs' => false, 'message' => '标签已删除'];
        }
        $res = $this->push->deletePushLabel($name);
        log_write('删除指定的标签-' . $name . '-'. json_encode($res));
        if(isset($res['error']) && $res['error']) {
            return ['rs' => false, 'message' => $res['error']];
        }


        return $tag->save(['is_delete' => 1]) ? ['rs' => true] : ['rs' => false, 'message' => '创建失败'];
    }

    /**
     * 在推送标签下添加用户
     * @param $name
     * @param $list 需要追加的用户列表
     */
    public function addPushLabelUser($name, $list)
    {
        //暂停Im推送
        return ['rs' => true];
        $tag = PushTagModel::detail(['name' => $name]);
        if(!$tag) {
            return ['rs' => false, 'message' => '标签已删除'];
        }
        $usernames = [];
        $data = [];
        foreach($list as $key => $item) {
            $imName = 'juzi_' . $item['user_id'];
            $tagUser = PushTagUserModel::detail(['name' => $imName, 'push_tag_id' => $tag['push_tag_id']]);
            if(!$tagUser) {
                $usernames[] = $imName;
                $data[] = [
                    'name' => $imName,
                    'user_id' => $item['user_id'],
                    'push_tag_id' => $tag['push_tag_id'],
                ];
            }
        }
        if(count($usernames) < 1) {
            return ['rs' => false, 'message' => '暂无可添加的用户'];
        }
        $res = $this->push->addPushLabelUser($name, $usernames);
        if(isset($res['error']) && $res['error']) {
            return ['rs' => false, 'message' => $res['error']];
        }
        log_write('在推送标签下添加用户列表-' . json_encode($data));
        log_write('在推送标签下添加用户-' . $name . '-'. json_encode($res));
        $res = (new PushTagUserModel)->add($data);

        return $res ? ['rs' => true] : ['rs' => false, 'message' => '创建失败'];
    }

    /**
     * 批量移出指定推送标签下的用户
     * @param $name
     * @param $list 需要追加的用户列表
     */
    public function deletePushLabelUsers($name, $list)
    {
        //暂停Im推送
        return ['rs' => true];
        $tag = PushTagModel::detail(['name' => $name]);
        if(!$tag) {
            return ['rs' => false, 'message' => '标签已删除'];
        }
        $usernames = [];
        $data = [];
        foreach($list as $key => $item) {
            $imName = 'juzi_' . $item['user_id'];
            $tagUser = PushTagUserModel::detail(['name' => $imName]);
            if($tagUser) {
                $usernames[] = $imName;
                $data[] = [
                    'where'=> ['push_tag_user_id' => $tagUser['push_tag_user_id']],
                    'data' => ['is_delete' => 1],
                ];
            }
        }
        $res = $this->push->deletePushLabelUsers($name, $usernames);
        if(isset($res['error']) && $res['error']) {
            return ['rs' => false, 'message' => $res['error']];
        }
        $res = (new PushTagUserModel)->updateAll($data);
        log_write('批量移出指定推送标签下的用户-' . $name . '-'. json_encode($res));

        return $res ? ['rs' => true] : ['rs' => false, 'message' => '删除失败'];
    }

    /**
     * 以同步方式给某个用户发送推送通知
     * 先创建任务
     * 携带局ID
     * @param $user_id
     * @param $message 推送消息
     */
    public function pushsync($user_id, $message)
    {
        //暂停Im推送
        return ['rs' => true];
        //1、先创建推送任务
        //2、再执行推送
        $imName = 'juzi_' . $user_id;
        $task = new PushTaskModel;
        $task->add([
            'title' => $message['title'],
            'content' => $message['content'],
            'game_id' => isset($message['game_id']) && $message['game_id'] ? $message['game_id'] : 0,
            'usernames' => $user_id,
            'status' => 1,
        ]);
        $imUser = ImUserModel::detail($user_id);
        if(!$imUser) {
            (new PushLogModel)->add([
                'user' => $imName,
                'user_id' => $user_id,
                'push_task_id' => $task['push_task_id'],
                'error' => '推送用户不存在',
                'status' => 0
            ]);
            return ['rs' => false, 'message' => '推送用户不存在'];
        }

        $pushData = ['title' => $message['title'], 'content' => $message['content']];
        $res = $this->push->pushsync($imName, $pushData);
        log_write('给某个用户发送推送通知-'. json_encode($res));
        if(isset($res['error']) && $res['error']) {
            (new PushLogModel)->add([
                'user' => $imName,
                'user_id' => $user_id,
                'push_task_id' => $task['push_task_id'],
                'error' => $res['error'],
                'status' => 0
            ]);
            return ['rs' => false, 'message' => $res['error']];
        }

        if($res) {
            (new PushLogModel)->add([
                'user' => $imName,
                'user_id' => $user_id,
                'push_task_id' => $task['push_task_id'],
                'status' => 1
            ]);
            return ['rs' => true];
        }

        return ['rs' => false, 'message' => '推送保存失败'];
    }

    /**
     * 无需创建任务 直接给用户推送
     * 先创建任务
     * 携带局ID
     * @param $user_id
     * @param $message 推送消息
     */
    public function pushone($task)
    {
        //暂停Im推送
        return ['rs' => true];
        $imName = 'juzi_' . $task['usernames'];
        //1、先创建推送任务
        //2、再执行推送
        $pushData = ['title' => $task['title'], 'content' => $task['content']];
        $res = $this->push->pushsync($imName, $pushData);
        log_write('给某个用户发送推送通知-'. json_encode($res));
        if(isset($res['error']) && $res['error']) {
            (new PushLogModel)->add([
                'user' => $imName,
                'user_id' => $task['usernames'],
                'push_task_id' => $task['push_task_id'],
                'error' => $res['error'],
                'status' => 0
            ]);
            return ['rs' => false, 'message' => $res['error']];
        }
        $res = (new PushLogModel)->add([
            'user' => $imName,
            'user_id' => $task['usernames'],
            'push_task_id' => $task['push_task_id'],
            'status' => 1
        ]);

        return $res ? ['rs' => true] : ['rs' => false, 'message' => '推送保存失败'];
    }

    /**
     * 以异步方式给多个用户发送推送通知
     * 先创建任务
     * 携带局ID
     * @param $userList
     * @param $message 推送消息
     */
    public function pushmore($userList, $message)
    {
        //暂停Im推送
        return ['rs' => true];
        //1、先创建推送任务
        //2、再执行推送
        $usernames = [];
        $list = [];
        foreach($userList as $key => $user_id) {
            $imName = 'juzi_' . $user_id;
            $imUser = ImUserModel::detail($user_id);
            if($imUser) {
                $list[] = $user_id;
                $usernames[] = $imName;

            }
        }
        if(count($usernames) < 1) {
            return ['rs' => false, 'message' => '推送用户不存在'];
        }
        $task = new PushTaskModel;
        $task->add([
            'title' => $message['title'],
            'content' => $message['content'],
            'game_id' => isset($message['game_id']) && $message['game_id'] ? $message['game_id'] : 0,
            'usernames' => implode(',', $list),
            'status' => 1,
        ]);
        $logList = [];

        $pushData = ['title' => $message['title'], 'content' => $message['content']];
        $res = $this->push->pushsingle($usernames, $pushData);
        log_write('给某个用户发送推送通知-'. json_encode($res));
        if(isset($res['error']) && $res['error']) {
            foreach($list as $key => $user_id) {
                $imName = 'juzi_' . $user_id;
                $logList[] = [
                    'user' => $imName,
                    'user_id' => $user_id,
                    'push_task_id' => $task['push_task_id'],
                    'error' => $res['error'],
                    'status' => 0
                ];
            }
            (new PushLogModel)->saveAll($logList);

            return ['rs' => false, 'message' => $res['error']];
        }

        foreach($list as $key => $user_id) {
            $imName = 'juzi_' . $user_id;
            $logList[] = [
                'user' => $imName,
                'user_id' => $user_id,
                'push_task_id' => $task['push_task_id'],
                'status' => 1
            ];
        }
        $res = (new PushLogModel)->saveAll($logList);

        return $res ? ['rs' => true] : ['rs' => false, 'message' => '推送保存失败'];
    }

}