<?php

namespace app\api\controller\game;

use app\api\controller\Controller;
use app\api\model\GameComment as GameCommentModel;
use app\api\model\Game as GameModel;

/**
 * 局举报服务
 * Class Collect
 * @package app\api\controller\game
 */
class Comment extends Controller
{
    /* @var \app\api\model\User $user */
    private $user;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->user = $this->getUser(true);   // 用户信息
    }

    /**
     * 投诉列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function lists($keyword = '')
    {
        // 获取列表数据
        $model = new GameCommentModel;
        $gameIdList = [];
        if($keyword) {
            $gameIdList = (new GameModel)->getListByName($keyword);
            if(count($gameIdList) < 1) {
                $gameIdList = [0];
            }
        }
        $list = $model->getList($this->user['user_id'], $gameIdList)->toArray();

        return $this->renderSuccess(compact('list'));
    }

    /**
     * 投诉局
     * @return array
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    public function submit()
    {
        $param = $this->request->post();
        if(!$param['game_id']) {
            return $this->renderError('局编号不能为空');
        }
        if(!$param['content']) {
            return $this->renderError('投诉内容不能为空');
        }
        if(!$param['image_id']) {
            return $this->renderError('投诉图片不能为空');
        }
        // 详情
        $detail = GameCommentModel::detail(['game_id' => $param['game_id'], 'user_id' => $this->user['user_id']]);
        if($detail && $detail['status'] < 1) {
            return $this->renderError('已投诉，请耐心等待处理');
        }
        if($detail && $detail['status'] == 1) {
            return $this->renderError('您的投诉已处理');
        }
        $model = new GameCommentModel;
        if($model->add($param['game_id'], $this->user['user_id'], $param['content'], $param['image_id'])) {
            return $this->renderSuccess([], '提交成功');
        }

        return $this->renderError('提交失败');
    }



    /**
     * 详情
     * @return array
     * @throws \think\exception\DbException
     */
    public function detail($game_comment_id)
    {
        // 详情
        $detail = GameCommentModel::detail($game_comment_id);
        if($detail['user_id'] != $this->user['user_id']) {
            return $this->renderError('此投诉不属于您');
        }

        return $this->renderSuccess(compact('detail'));
    }

    /**
     * 删除
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($game_comment_id)
    {
        // 详情
        $detail = GameCommentModel::detail($game_comment_id);
        if(!$detail || $detail['is_delete'] == 1) {
            return $this->renderError('记录不存在');
        }
        if($detail['user_id'] != $this->user['user_id']) {
            return $this->renderError('此投诉不属于您');
        }
        if (!$detail->setDelete()) {
            return $this->renderError($detail->getError() ?: '删除失败');
        }

        return $this->renderSuccess('删除成功');
    }

}