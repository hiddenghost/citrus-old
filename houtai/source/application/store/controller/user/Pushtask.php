<?php

namespace app\store\controller\user;
use app\store\controller\Controller;
use app\store\model\push\Task as TaskModel;
use app\store\model\push\Log as LogModel;
use app\store\model\Game as GameModel;
use app\store\model\push\Tag as TagModel;
use app\store\model\push\TagUser as TagUserModel;
use app\store\model\User as UserModel;
use app\store\model\im\User as ImUserModel;
use app\common\service\Push as Push;

/**
 * 推送管理
 * Class Pushtask
 * @package app\store\controller\store
 */
class Pushtask extends Controller
{
    /**
     * 列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new TaskModel;
        $list = $model->getList();
        $total_page = ceil(count(UserModel::getAll()) / 10);
        $list->each(function ($item, $key) {
            if($item['push_tag_id'] > 0) {
                $userList = TagUserModel::getListByTag($item['push_tag_id']);
                $item['usernames'] = implode(',', array_column($userList->toArray(), 'user_id'));
            }
        });

        return $this->fetch('index', compact('list', 'total_page'));
    }

    /**
     * 添加门店
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function add()
    {
        $model = new TaskModel;
        if (!$this->request->isAjax()) {
            $category = (new GameModel)->getAll([]);
            $tagList = TagModel::getAllList();
            foreach($tagList as $key => &$item) {
                $item['total_nums'] = count(TagUserModel::getListByTag($item['push_tag_id']));
            }
            $totalImUser = (new ImUserModel)->getCount();

            return $this->fetch('add', compact( 'category', 'tagList', 'totalImUser'));
        }
        // 新增记录
        if ($model->addData($this->postData('post'))) {
            return $this->renderSuccess('添加成功', url('user.pushtask/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑门店
     * @param $push_task_id
     * @return array|bool|mixed
     * @throws \think\exception\DbException
     */
    public function edit($push_task_id)
    {
        // 详情
        $model = TaskModel::detail($push_task_id);
        if (!$this->request->isAjax()) {
            $category = (new GameModel)->getAll([]);
            $tagList = TagModel::getAllList();
            $totalImUser = (new ImUserModel)->getCount();

            return $this->fetch('edit', compact('model', 'category', 'tagList', 'totalImUser'));
        }
        // 新增记录
        if ($model->editData($this->postData('post'))) {
            return $this->renderSuccess('更新成功', url('user.pushtask/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除门店
     * @param $push_task_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($push_task_id)
    {
        // 门店详情
        $model = TaskModel::detail($push_task_id);
        if (!$model->save(['is_delete' => 1])) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 推送
     * @param $push_task_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function push($push_task_id)
    {
        // 门店详情
        $model = TaskModel::detail($push_task_id);
        $push = new Push;
        //多个用户推送
        if($model['type'] == 1) {
            $userList = explode(',', $model['usernames']);
        }
        //全量用户推送
        if($model['type'] == 2) {
            $list = UserModel::getAll();
            $userList = array_column($list, 'user_id');
        }
        //标签用户推送
        if($model['type'] == 3 && $model['push_tag_id'] > 0) {
            $list = TagUserModel::getListByTag($model['push_tag_id']);
            $userList = array_column($list->toArray(), 'user_id');
        }
        if(count($userList) > 0) {
            $res = $push->pushmore($userList, $model);
        }
        if($res['rs']) {
            $model->save(['status' => 1]);
            return $this->renderSuccess('推送成功');
        }else{
            return $this->renderError($res['message']);
        }
    }

    /**
     * 分页全用户推送
     * @param $push_task_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function pushall($push_task_id, $page = 1)
    {
        // 门店详情
        $model = TaskModel::detail($push_task_id);
        if($model['status'] == 1) {
            return $this->renderError('推送已结束');
        }
        $push = new Push;

        //全量用户推送
        $list = UserModel::getPage();
        $items = $list->items();
        $userList = [];
        foreach($items as $item) {
            $userList[] = $item->user_id;
        }
        if(count($userList) > 0) {
            $res = $push->pushmore($userList, $model);
            if($res['rs']) {
                if(count($userList) == 10) {
                    $model->save(['page' => $page + 1]);
                }else{
                    $model->save(['status' => 1]);
                }

                return $this->renderSuccess('推送成功', '', count($userList));
            }else{
                return $this->renderError($res['message']);
            }
        }else{
            return $this->renderSuccess('推送成功', '', 0);
        }
    }

}