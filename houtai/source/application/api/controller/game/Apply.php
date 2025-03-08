<?php

namespace app\api\controller\game;

use app\api\controller\Controller;
use app\api\model\GameApply as GameApplyModel;
use app\api\model\Game as GameModel;
use app\api\service\Im as ImService;
use app\api\model\GameUsers as GameUsersModel;
use app\api\model\im\Group as GroupModel;
use app\common\service\Push as Push;


/**
 * 局申请服务
 * Class Collect
 * @package app\api\controller\game
 */
class Apply extends Controller
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
     * 我创建的局列表
     */
    public function mycreate($keyword = '')
    {
        $model = new GameModel;
        //过滤掉已经进行中并且人数只有一个人的
        $runList = $model->getRunList($this->user['user_id']);
        foreach($runList as $key => $item) {
            if(count($item['users']) <= 1) {
                $model->save(['is_delete' => 1], ['game_id' => $item['game_id']]);
            }
        }
        $list = $model->getCreateList($this->user['user_id'], $keyword);
        $list->each(function($item, $key) {
            //需要审核的局数字mycreate
            $item['total_checks'] = (new GameApplyModel)->getCount($item['game_id'], 0);
            $openTime = strtotime($item['open_date'] . ' ' . $item['start_time']);
            $endTime = strtotime($item['open_date'] . ' ' . $item['end_time']);
            $curTime = time();
            if($curTime < $openTime) {
                $item['apply_status_name'] = '待开始';
            }
            if($curTime > $endTime) {
                $item['apply_status_name'] = '已结束';
                if(count($item['users']) == 1) {
//                    return false;
                }
            }
            if($openTime <= $curTime && $curTime <= $endTime) {
                $item['apply_status_name'] = '进行中';
            }
        });

        return $this->renderSuccess(compact('list'));
    }

    /**
     * 局需要审核的总数
     */
    public function totalchecks()
    {
        $model = new GameModel;
        $list = $model->getAllCreateList($this->user['user_id']);
        $total_checks = 0;
        foreach($list as $key => $item) {
            //需要审核的局数字
            $total_checks += (new GameApplyModel)->getCount($item['game_id'], 0);
        }

        return $this->renderSuccess(compact('total_checks'));
    }

    /**
     * 朋友创建的局列表
     * @param $user_id
     */
    public function friendcreate($user_id)
    {
        if(!$user_id) {
            return $this->renderError('用户ID不能为空');
        }
        $model = new GameModel;
        $list = $model->getCreateList($user_id);
        $list->each(function($item, $key) {
            $item['is_join'] = false;
            $item['is_collect'] = false;
            foreach($item['users'] as $joinUser) {
                if($joinUser['user_id'] == $this->user['user_id']) {
                    $item['is_join'] = true;
                }
            }
            foreach($item['collect'] as $collectItem) {
                if($collectItem['user_id'] == $this->user['user_id']) {
                    $item['is_collect'] = true;
                }
            }
        });

        return $this->renderSuccess(compact('list'));
    }

    /**
     * 我参加的局列表
     */
    public function myjoin($keyword = '')
    {
        $gameApply = new GameApplyModel;
        $gameIdList = [];
        if($keyword) {
            $gameIdList = (new GameModel)->getListByName($keyword);
            if(count($gameIdList) < 1) {
                $gameIdList = [0];
            }
        }
        $list = $gameApply->getJoinList($this->user['user_id'], $gameIdList)->toArray();
        $data = [];
        foreach($list['data'] as $key => $item) {
            $game = GameModel::detail($item['game_id']);
            $game['apply_status'] = $item['apply_status'];
            //卡片背景颜色 颜色为灰色的情况：待审核、已拒绝、已退出
            if($item['apply_status'] != 1) {
                $game['category']['color'] = '#898C6F';
            }
            //显示状态
            if($item['apply_status'] < 1) {
                $game['apply_status_name'] = '待审核';
            }
            $game['game_apply_id'] = $item['game_apply_id'];
            if($item['apply_status'] == 1) {
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
            if($item['apply_status'] == 2) {
                $game['apply_status_name'] = '被拒绝';
            }
            if($item['apply_status'] == 3) {
                $game['apply_status_name'] = '已退局';
            }

            $data[] = $game;
        }
        $list['data'] = $data;

        return $this->renderSuccess(compact('list'));
    }

    /**
     * 局审核用户列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function lists($game_id)
    {
        $game = GameModel::detail($game_id);
        if($game['user_id'] != $this->user['user_id']) {
            return $this->renderError('此局不属于您创建的');
        }
        // 获取列表数据
        $model = new GameApplyModel;
        $list = $model->getList($game_id);

        return $this->renderSuccess(compact('list'));
    }

    /**
     * 审核局成员申请
     * @param $game_id
     * @param $user_id
     */
    public function docheck($game_id, $game_apply_id, $apply_status)
    {
        if(!$game_id) {
            return $this->renderError('局编号不能为空');
        }
        if(!$game_apply_id) {
            return $this->renderError('申请信息ID不能为空');
        }
        if(!in_array($apply_status, [1,2])) {
            return $this->renderError('操作状态错误');
        }
        $game = GameModel::detail($game_id);
        if($game['user_id'] != $this->user['user_id']) {
            return $this->renderError('此局不属于您创建的');
        }
        $detail = GameApplyModel::detail($game_apply_id);
        if(!$detail) {
            return $this->renderError('记录不存在');
        }
        if($detail['apply_status'] > 0) {
            return $this->renderError('已操作过');
        }
        $push = new Push;
        if($detail->save(['apply_status' => $apply_status])) {
            //审核员工加入
            if($apply_status == 1) {
                $push->pushsync($detail['user_id'], ['title' => 'Citrus', 'content' => '你申请的局[' . $game['name'] . ']已通过啦', 'game_id' => $game['game_id']]);
                //新增局用户
                (new GameUsersModel)->add([
                    'game_id' => $game['game_id'],
                    'order_id' => 0,
                    'user_id' => $detail['user_id'],
                    'is_creator' => 0,
                ]);
                //添加到群组中
                $group = GroupModel::detail(['game_id' => $game['game_id']]);
                $this->_imService->addGroupMember($group['im_group_id'], $detail['user_id']);
            }else{
                //审核员工拒绝
                $push->pushsync($detail['user_id'], ['title' => 'Citrus', 'content' => '你申请的局[' . $game['name'] . ']被拒绝了', 'game_id' => $game['game_id']]);
            }

            return $this->renderSuccess([], '提交成功');
        }

        return $this->renderError('操作失败');
    }

    /**
     * 退出局
     * 条件限制：离局开始还差2个小时就不允许退出了
     * @param $game_id
     */
    public function exitgame($game_id)
    {
        if(!$game_id) {
            return $this->renderError('局编号不能为空');
        }
        $game = GameModel::detail($game_id);
        if($game['user_id'] == $this->user['user_id']) {
            return $this->renderError('此局属于您创建的不能退出');
        }
        $detail = GameApplyModel::detail(['user_id' => $this->user['user_id'], 'game_id' => $game_id]);
        $apply_status = $detail['apply_status'];
        if(!$detail) {
            return $this->renderError('未报名没需退出');
        }
        if($apply_status == 3) {
            return $this->renderError('已退出没需重复退出');
        }
        if($detail['user_id'] != $this->user['user_id']) {
            return $this->renderError('该行程不属于您的操作权限');
        }
        $openTime = strtotime($game['open_date'] . ' ' . $game['start_time']);
        $endTime = strtotime($game['open_date'] . ' ' . $game['end_time']);
        if($endTime < time()) {
            return $this->renderError('局已结束不允许退出');
        }
        if(($openTime - time()) < 7200) {
            return $this->renderError('局即将开始不允许退出');
        }
        $res = $detail->save(['apply_status' => 3]);
        if($apply_status == 1 && $res) {
            //可能需要执行的操作
            //1、微信退款
            //2、退群
            $gameUser = GameUsersModel::detail($game_id, $this->user['user_id']);
            $gameUser->setDelete();
            //添加到群组中
            $group = GroupModel::detail(['game_id' => $game['game_id']]);
            $this->_imService->deleteGroupMember($group['im_group_id'], $this->user['user_id']);

            return $this->renderSuccess([], '操作成功');
        }

        return $this->renderSuccess([], '操作成功');
    }

    /**
     * 用户申请加入局
     * @return array
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    public function submit()
    {
        $param = $this->request->post();
        //验证是否已经完成邮箱认证、实名认证
//        if(!$this->user['status']) {
//            return $this->renderError('用户未邮箱验证');
//        }
//        if(!$this->user['is_idcard']) {
//            return $this->renderError('用户未实名验证');
//        }
        if(!$param['game_id']) {
            return $this->renderError('局编号不能为空');
        }
        $game = GameModel::detail($param['game_id']);
        if(!$game) {
            return $this->renderError('局不存在');
        }
        if($game['user_id'] == $this->user['user_id']) {
            return $this->renderError('自己不能参加自己创建的局');
        }
        //局进行了就不能再加入
        $openTime = $game['open_date'] . ' ' . $game['start_time'];
        $openTime = strtotime($openTime);
        if(time() > $openTime) {
            return $this->renderError('局已经开始不能再加入');
        }
        // 详情
        $detail = GameApplyModel::detail(['game_id' => $param['game_id'], 'user_id' => $this->user['user_id']]);
        if($detail && $detail['apply_status'] < 2) {
            return $this->renderError('已申请，不能重复操作');
        }
        $model = new GameApplyModel;
        $push = new Push;
        if($detail && $detail['apply_status'] > 1) {
            if($detail->save(['apply_status' => 0, 'user_name' => $param['user_name'], 'phone' => $param['phone']])) {
                $push->pushsync($game['user_id'], ['title' => 'Citrus', 'content' => $param['user_name'] . '申请加入你的局[' . $game['name'] . ']', 'game_id' => $game['game_id']]);

                return $this->renderSuccess([], '提交成功');
            }
        }else{
            $data = [
                'user_name' => $param['user_name'],
                'phone' => $param['phone'],
                'total_price' => 0,
                'order_price' => 0,
                'price_type' => $game['price_type'],
                'game_id' => $param['game_id'],
                'apply_status' => 0,
                'user_id' => $this->user['user_id'],
            ];
            if($model->add($data)) {
                $push->pushsync($game['user_id'], ['title' => 'Citrus', 'content' => $param['user_name'] . '申请加入你的局[' . $game['name'] . ']', 'game_id' => $game['game_id']]);

                return $this->renderSuccess([], '提交成功');
            }
        }

        return $this->renderError('提交失败');
    }

    /**
     * 删除报名申请
     * @param $game_apply_id
     */
    public function delete($game_apply_id)
    {
        if(!$game_apply_id) {
            return $this->renderError('申请行程ID不能为空');
        }
        $apply = GameApplyModel::detail($game_apply_id);
        if(!$apply) {
            return $this->renderError('申请行程不存在');
        }
        if($apply['user_id'] != $this->user['user_id']) {
            return $this->renderError('该行程不属于您的操作权限');
        }
        if($apply['apply_status'] != 2) {
            return $this->renderError('此局未被拒绝不允许删除');
        }
        $res = $apply->save(['is_delete' => 1]);

        return $res ? $this->renderSuccess([], '操作成功') : $this->renderError('操作失败');
    }

}