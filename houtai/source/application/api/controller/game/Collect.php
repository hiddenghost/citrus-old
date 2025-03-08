<?php

namespace app\api\controller\game;

use app\api\controller\Controller;
use app\api\model\GameCollect as GameCollectModel;
use app\api\model\Game as GameModel;
use app\api\model\GameApply as GameApplyModel;

/**
 * 局收藏服务
 * Class Collect
 * @package app\api\controller\game
 */
class Collect extends Controller
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
     * 局列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function lists($keyword = '')
    {
        // 获取列表数据
        $model = new GameCollectModel;
        $gameIdList = [];
        if($keyword) {
            $gameIdList = (new GameModel)->getListByName($keyword);
            if(count($gameIdList) < 1) {
                $gameIdList = [0];
            }
        }
        $list = $model->getList($this->user['user_id'], $gameIdList)->toArray();
        $data = [];
        foreach($list['data'] as $key => $item) {
            $game = GameModel::detail($item['game_id']);
            $game['collect_id'] = $item['collect_id'];
            $game['user_id'] = $item['user_id'];
            $game['create_time'] = $item['create_time'];
            $apply = GameApplyModel::detail(['game_id' => $item['game_id'], 'user_id' => $this->user['user_id']]);
            $game['apply_status'] = $apply['apply_status'];
            if($apply && $apply['apply_status'] != 1) {
                $game['category']['color'] = '#898C6F';
            }
            //显示状态
            if(!$apply) {
                $game['apply_status_name'] = '未申请';
            }
            if($apply && $apply['apply_status'] < 1) {
                $game['apply_status_name'] = '待审核';
            }
            $game['game_apply_id'] = $apply['game_apply_id'];
            if($apply && $apply['apply_status'] == 1) {
                $openTime = strtotime($game['open_date'] . ' ' . $game['start_time']);
                $endTime = strtotime($game['open_date'] . ' ' . $game['end_time']);
                $curTime = time();
                if($curTime < $openTime) {
                    $game['apply_status_name'] = '待开始';
                }
                if($curTime > $endTime) {
                    $game['apply_status_name'] = '已结束';
                }
                if($openTime <= $curTime && $curTime <= $endTime) {
                    $game['apply_status_name'] = '进行中';
                }
            }
            if($apply && $apply['apply_status'] == 2) {
                $game['apply_status_name'] = '被拒绝';
            }
            if($apply && $apply['apply_status'] == 3) {
                $game['apply_status_name'] = '已退局';
            }

            $data[] = $game;
        }
        $list['data'] = $data;

        return $this->renderSuccess(compact('list'));
    }

    /**
     * 收藏局
     * @return array
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    public function apply()
    {
        $param = $this->request->post();
        if(!$param['game_id']) {
            return $this->renderError('局编号不能为空');
        }
        // 详情
        $detail = GameCollectModel::detail($param['game_id'], $this->user['user_id']);
        if($detail) {
            return $this->renderError('已想想，不能重复操作');
        }
        $model = new GameCollectModel;
        if($model->add($param['game_id'], $this->user['user_id'])) {
            return $this->renderSuccess([], '提交成功');
        }

        return $this->renderError('提交失败');
    }



    /**
     * 详情
     * @return array
     * @throws \think\exception\DbException
     */
    public function detail($game_id)
    {
        // 详情
        $detail = GameCollectModel::detail($game_id, $this->user['user_id']);

        return $this->renderSuccess(compact('detail'));
    }

    /**
     * 删除
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($game_id)
    {
        // 详情
        $detail = GameCollectModel::detail($game_id, $this->user['user_id']);
        if(!$detail) {
            return $this->renderError('记录不存在');
        }
        if (!$detail->setDelete()) {
            return $this->renderError($detail->getError() ?: '删除失败');
        }

        return $this->renderSuccess('删除成功');
    }

}