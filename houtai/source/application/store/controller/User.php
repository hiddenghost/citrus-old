<?php

namespace app\store\controller;

use app\store\model\User as UserModel;
use app\store\model\user\Grade as GradeModel;
use app\api\service\Im as ImService;

/**
 * 用户管理
 * Class User
 * @package app\store\controller
 */
class User extends Controller
{
    /**
     * 用户列表
     * @param string $nickName 昵称
     * @param int $gender 性别
     * @param int $grade 会员等级
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index($nickName = '', $gender = null, $grade = null)
    {
        $model = new UserModel;
        $list = $model->getList($nickName, $gender, $grade);
        // 会员等级列表
        $gradeList = GradeModel::getUsableList();
        return $this->fetch('index', compact('list', 'gradeList'));
    }

    /**
     * 删除用户
     * @param $user_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($user_id)
    {
        // 用户详情
        $model = UserModel::detail($user_id);
        if ($model->setDelete()) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?: '删除失败');
    }

    /**
     * 用户充值
     * @param $user_id
     * @param int $source 充值类型
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function recharge($user_id, $source)
    {
        // 用户详情
        $model = UserModel::detail($user_id);
        if ($model->recharge($this->store['user']['user_name'], $source, $this->postData('recharge'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 修改会员等级
     * @param $user_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function grade($user_id)
    {
        // 用户详情
        $model = UserModel::detail($user_id);
        if ($model->updateGrade($this->postData('grade'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 同步Im用户
     * @param $user_id
     */
    public function im($user_id = 0)
    {
        $userList = UserModel::getAll();
        $im = new ImService;
        foreach($userList as $key => $item) {
            $im->createUser($item['user_id']);
        }

        return $this->renderSuccess('操作成功');
    }

    /**
     * 用户详情
     * @param $user_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function detail($user_id)
    {
        // 用户详情
        $detail = UserModel::detail($user_id);
        if (!$detail) {
            return $this->renderSuccess('用户不存在');
        }

        return $this->fetch('detail', compact('detail'));
    }


}
