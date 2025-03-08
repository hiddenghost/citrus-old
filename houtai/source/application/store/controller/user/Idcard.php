<?php

namespace app\store\controller\user;

use app\store\controller\Controller;
use app\store\model\user\Idcard as IdcardModel;
use app\store\model\User as UserModel;

/**
 * 学历认证
 * Class Idcard
 * @package app\store\controller\user
 */
class Idcard extends Controller
{
    /**
     * 会员等级列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new IdcardModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 编辑会员等级
     * @param $grade_id
     * @return array|bool|mixed
     * @throws \think\exception\DbException
     */
    public function check($idcard_id)
    {
        // 会员等级详情
        $model = IdcardModel::detail(['idcard_id' => $idcard_id]);
        // 更新记录
        $data = $this->postData('apply');
        $data['check_time'] = date('Y-m-d H:i:s', time());

        // 新增记录
        if ($model->edit($data)) {
            //认证成功
            if($data['status'] == 1) {
                $user = UserModel::detail($model['user_id']);
                $user->save(['is_idcard' => 1]);
            }
            return $this->renderSuccess('更新成功', url('user.idcard/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除
     * @param $idcard_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($idcard_id)
    {
        // 详情
        $model = IdcardModel::detail($idcard_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        $user = UserModel::detail($model['user_id']);
        $user->save(['is_idcard' => 0]);

        return $this->renderSuccess('删除成功');
    }

}