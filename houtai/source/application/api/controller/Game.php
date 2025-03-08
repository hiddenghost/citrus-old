<?php

namespace app\api\controller;

use app\api\model\Game as GameModel;
use app\api\model\Keyword;
use app\api\model\Tags as TagsModel;
use app\api\model\GameCategory as GameCategoryModel;
use app\common\model\City as CityModel;
use app\api\model\Keyword as KeywordModel;
use app\api\model\GameKeyword as GameKeywordModel;
use app\api\model\GameApply as GameApplyModel;
use app\api\model\im\Relation as RelationModel;
use app\api\model\im\Group as GroupModel;
use app\api\model\GameUsers as GameUsersModel;
use app\api\service\Im as ImService;
use app\common\service\Push as Push;
use app\api\model\User as UserModel;
use app\api\model\user\PointsLog as PointsLogModel;
use app\api\model\user\Grade as GradeModel;
use app\api\model\user\GradeLog as GradeLogModel;

/**
 * 局控制器
 * Class Game
 * @package app\api\controller
 */
class Game extends Controller
{

    private $_imService;

    /**
     * 发布的初始化数据
     */
    public function init()
    {
        $tagsList = (new TagsModel)->getList();
        $categoryList = GameCategoryModel::getCacheAll();

        return $this->renderSuccess(compact('tagsList', 'categoryList'));
    }

    /**
     * 推送未开始的局
     */
    public function pushstart()
    {
        $model = new GameModel;
        $dayList = $model->getAllStartList();
        $hourList = $model->getAllStartHourList();
        $push = new Push;
        $data = [];
        if($dayList && count($dayList) > 0) {
            foreach($dayList as $key => $item) {
                $dayUserList = [];
                foreach($item['users'] as $userKey => $userItem) {
                    $dayUserList[] = $userItem['user_id'];
                }
                if(count($dayUserList) > 0) {
                    $content = '[' . $item['name'] . ']最近要开始啦，别忘了参加！';
                    $push->pushmore(
                        $dayUserList,
                        ['title' => 'Citrus', 'content' => $content, 'game_id' => $item['game_id']]
                    );
                }
                $data[] = ['data' => ['is_day_push' => 1], 'where' => ['game_id' => $item['game_id']]];
            }
        }
        if($hourList && count($hourList) > 0) {
            foreach($hourList as $key => $item) {
                $hourUserList = [];
                foreach($item['users'] as $userKey => $userItem) {
                    $hourUserList[] = $userItem['user_id'];
                }
                if(count($hourUserList) > 0) {
                    $sub = strtotime($item['open_date'] . ' ' . $item['start_time']) - time();
                    log_write($item['open_date'] . ' ' . $item['start_time'] . '~' . date('Y-m-d H:i:s', time()) . '~时差检测');
//                    $content = '[' . $item['name'] . ']还有'. date('H小时i分钟', $sub) .'就要开始了';
                    $content = '[' . $item['name'] . ']马上要开始啦，别忘了参加！';
                    $push->pushmore(
                        $hourUserList,
                        ['title' => 'Citrus', 'content' => $content, 'game_id' => $item['game_id']]
                    );
                }
                $data[] = ['data' => ['is_hour_push' => 1], 'where' => ['game_id' => $item['game_id']]];
            }
        }
        if(count($data) > 0) {
            $model->updateAll($data);
        }

        return $this->renderSuccess(['dayList' => $dayList, 'hourList' => $hourList]);
    }

    /**
     * 已结束的且未分配积分且参与人数大于1的局
     */
    public function endlist()
    {
        $model = new GameModel;
        $list = $model->getAllEndList();
        $result = [];
        foreach($list as $key => $item) {
            if(count($item['users']) > 1) {
                $result[] = $item;
            }
        }
        $gradeList = GradeModel::getUsableList(null, ['points' => 'asc']);
        $data = [];
        foreach($result as $key => $item) {
            foreach($item['users'] as $userKey => $userItem) {
                $this->_addUserPoints($userItem['user_id'], $userItem['is_creator'], $gradeList);
            }
            $data[] = ['data' => ['is_points' => 1], 'where' => ['game_id' => $item['game_id']]];
        }
        $model->updateAll($data);

        return $this->renderSuccess(compact('result'));
    }

    /**
     * 增加用户积分并升级用户等级
     * @param $user_id
     * @param $points
     */
    private function _addUserPoints($user_id, $is_creator, $gradeList)
    {
        $points = $is_creator > 0 ? 2 : 1; //自己发起的局为2积分 参加的局为1个积分
        $user = UserModel::detail($user_id);
        $userModel = new UserModel();
        //新增积分
        $old_points = $user['points'];
        $new_points = $user['points'] + $points;
        if($userModel->save(['points' => $new_points], ['user_id' => $user_id])) {
            PointsLogModel::add([
                'user_id' => $user_id,
                'value' => '+' . $points,
                'describe' => $is_creator > 0 ? '发布局新增积分' . $points . '个' : '参加局新增积分' . $points . '个',
            ]);
            //判断是否升级等级用户
            $new_grade_id = 0;
            foreach($gradeList as $key => $item) {
                if($new_points > $item['points']) {
                    $new_grade_id = $item['grade_id'];
                }
            }
            if($new_grade_id > 0 && $new_grade_id > $user['grade_id']) {
                $old_grade_id = $user['grade_id'];
                $userModel->save(['grade_id' => $new_grade_id], ['user_id' => $user_id]);
                GradeLogModel::add([
                    'user_id' => $user_id,
                    'old_grade_id' => $old_grade_id ? $old_grade_id : 0,
                    'new_grade_id' => $new_grade_id,
                    'change_type' => 20,
                ]);
            }
        }
    }



    /**
     * 局列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function lists()
    {
        $user = $this->getUser(true);   // 用户信息
        $o_city_id = $user['city'];
        // 整理请求的参数
        $param = array_merge($this->request->param(), [
            'status' => 10,
            'is_open' => 1,
            'is_start' => 1,
        ]);
        // 获取列表数据
        $model = new GameModel;
        $list = $model->getList($param);
        $list->each(function($item, $key) {
            $item['actual_people'] = count($item['users']);
        });
        if($param['city_id'] > 0) {
            $city = CityModel::detail($param['city_id']);
            $userData = ['city' => $city['id']];
            if($city['pid'] > 0) {
                $province = CityModel::get(['id' => $city['pid']]);
                if($province) {
                    $userData['province'] = $province['id'];
                    if($province['pid'] > 0) {
                        $country = CityModel::get(['id' => $province['pid']]);
                        if($country) {
                            $userData['country'] = $country['id'];
                        }
                    }
                }
            }

            $user->save($userData);
        }

        return $this->renderSuccess(compact('list'));
    }

    /**
     * 城市改变后 推送消息通知
     */
    public function citypush()
    {
        $user = $this->getUser(true);   // 用户信息
        $param = $this->request->param();
        $friendList = (new RelationModel)->getMyFriendList(['user_id' => $user['user_id']]);
        $userList = array_column($friendList, 'focus_user_id'); //好有列表
        $myCityFirendsList = (new UserModel)->getMyCityFirends($param['city_id'], $userList);
        $userList = array_column($myCityFirendsList, 'user_id'); //好有列表
        $push = new Push;
        $userName = $user['nickName'] ? $user['nickName'] : $user['name'];
        if($userName) {
            $content = '你的朋友' . $userName . '刚到你的城市了！赶快去瞧瞧~';
        }else{
            $content = '你的朋友刚到你的城市了，赶快去瞧瞧~';
        }

        $push->pushmore(
            $userList,
            ['title' => 'Citrus', 'content' => $content, 'game_id' => 0]
        );


        return $this->renderSuccess([]);
    }

    /**
     * 朋友的局列表
     */
    public function friend()
    {
        $user = $this->getUser(true);   // 用户信息
        $friendList = (new RelationModel)->getMyFriendList(['user_id' => $user['user_id']]);
        $userList = array_column($friendList, 'focus_user_id');
        $model = new GameModel;
        $joinList = (new GameApplyModel)->getJoinAll($userList);
        $list = $model->getFriendList($userList, array_column($joinList, 'game_id'));
        $list->each(function($item, $key) {
            $item['actual_people'] = count($item['users']);
        });

        return $this->renderSuccess(compact('list'));
    }

    /**
     * 来自微信免认证的详情
     * @param $game_id
     */
    public function detailforwx($game_id)
    {
        //验证微信端
        if(!$game_id) {
            return $this->renderError('编号不存在');
        }
        $model = new GameModel;
        $game = $model->getDetails($game_id, $this->getUser(false));
        unset($game['user']);

        foreach($game['users'] as $key => &$item) {
            $item['avatarUrl'] = $item['user']['avatarUrl'];
            unset($item['user']);
        }
        return $this->renderSuccess([
            'detail' => $game,
        ]);
    }

    /**
     * 获取局详情
     * @param $game_id
     * @param $type 进入的入口 1从首页列表进入 2我的创建 3我加入的局 4局想想
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function detail($game_id)
    {
        // 用户信息
        $user = $this->getUser(true);
        // 商品详情
        $model = new GameModel;
        $game = $model->getDetails($game_id, $this->getUser(false));
        if (!$game) {
            return $this->renderError($model->getError() ?: '局不存在');
        }
        $game['actual_people'] = count($game['users']);
        //判断type 1我创建的 2我已加入的局 3我想想的局 4代表未申请的局 5代表申请了
        $is_join = false;
        foreach($game['users'] as $key => $item) {
            if($item['user_id'] == $user['user_id'] && $item['is_creator'] < 1 && $item['is_delete'] < 1) {
                $is_join = true;
            }
        }
        $is_collect = false;
        foreach($game['collect'] as $key => $item) {
            if($item['user_id'] == $user['user_id'] && $item['is_delete'] < 1) {
                $is_collect = true;
            }
        }
        //与创建者之间的关系
        if($user['user_id'] == $game['user_id']) {
            $relation_status = -1;
        }else{
            $relation = RelationModel::detail(['user_id' => $user['user_id'], 'focus_user_id' => $game['user_id']]);
            if(!$relation) {
                $relation_status = 0;
            }else{
                $relation_status = $relation['status'];
            }
        }
        //判断当前用户与其他参与用户的关系
        $userList = $game['users'];
        foreach($userList as $key => $item) {
            $item['is_friend'] = 0;
            if($item['user_id'] == $user['user_id']) {
                $item['is_friend'] = 1;
            } else{
                if(RelationModel::detail(['user_id' => $user['user_id'], 'focus_user_id' => $item['user_id'], 'status' => 2])
                    ||
                    RelationModel::detail(['focus_user_id' => $user['user_id'], 'user_id' => $item['user_id'], 'status' => 2])
                ){
                    $item['is_friend'] = 1;
                }
            }
            $userList[$key] = $item;
        }
        $game['users'] = $userList;

        return $this->renderSuccess([
            // 详情
            'detail' => $game,
            'is_create' => $user['user_id'] == $game['user_id'] ? true : false,
            'is_join' => $is_join,
            'is_collect' => $is_collect,
            'relation_status' => $relation_status,
            'group' => GroupModel::detail(['game_id' => $game['game_id']])
        ]);
    }

    /**
     * 发布局
     */
    public function publish()
    {
        $model = new GameModel;
        $user = $this->getUser(true);
        $param = $this->getParam(['user_id' => $user['user_id'], 'sort' => 100]);
        //验证是否已经完成邮箱认证、实名认证
//        if(!$user['status']) {
//            return $this->renderError('用户未邮箱验证');
//        }
//        if(!$user['is_idcard']) {
//            return $this->renderError('用户未实名验证');
//        }
        //验证数据合法性
        //解决表情发送不了的问题
        if(isset($_POST['name']) && $_POST['name']) {
            $param['name'] = $_POST['name'];
        }
        if(isset($_GET['name']) && $_GET['name']) {
            $param['name'] = $_GET['name'];
        }
        if(isEmpty($param['name'])) {
            return $this->renderError('局标题不能为空');
        }
        $param['name'] = urldecode($param['name']);
        if(isset($_POST['desc']) && $_POST['desc']) {
            $param['desc'] = $_POST['desc'];
        }
        if(isset($_GET['desc']) && $_GET['desc']) {
            $param['desc'] = $_GET['desc'];
        }
        if(isEmpty($param['desc'])) {
            return $this->renderError('局详情不能为空');
        }
        $param['desc'] = urldecode($param['desc']);
        if(isEmpty($param['imageList'])) {
            return $this->renderError('局图片至少选一张');
        }
        if(isEmpty($param['open_date'])) {
            return $this->renderError('活动日期必选');
        }
        if(isEmpty($param['start_time'])) {
            return $this->renderError('活动开始时间必选');
        }
        if(isEmpty($param['end_time'])) {
            return $this->renderError('活动结束时间必选');
        }
        $subTime = strtotime($param['end_time']) - strtotime($param['start_time']);
        if($subTime < 0) {
            return $this->renderError('结束时间必须大于开始时间');
        }
        if(isEmpty($param['address'])) {
            return $this->renderError('活动地点不能为空');
        }
        if(isEmpty($param['is_open'])) {
            return $this->renderError('活动类型必选');
        }
        if(isEmpty($param['price_type'])) {
            return $this->renderError('活动费用类型必选');
        }
        if(isEmpty($param['category_id'])) {
            return $this->renderError('活动分类必选');
        }
        if(isEmpty($param['tagsList'])) {
            return $this->renderError('局标签至少选一个');
        }
        if($model->add($param)) {
            $friendList = (new RelationModel)->getMyFriendList(['user_id' => $user['user_id']]);
            $userList = array_column($friendList, 'focus_user_id');
            $push = new Push;
            $userName = $user['nickName'] ? $user['nickName'] : $user['name'];
            if($userName) {
                $content = '你的朋友' . $userName . '刚刚发起了一个局，赶快去瞧瞧~';
            }else{
                $content = '你的朋友刚刚发起了一个局，赶快去瞧瞧~';
            }
            /* 暂停Im推送 */
//            $push->pushmore(
//                $userList,
//                ['title' => 'Citrus', 'content' => $content, 'game_id' => $model['game_id']]
//            );

            return $this->renderSuccess([],'发布成功');
        }else{
            return $this->renderError('发布失败');
        }
    }

    /**
     * 修改局
     * 限制条件
     * 1、局离开始时间两个时间内就不允许修改
     * 1、人数，人数不能小于已报名的人数
     * 2、时间：小于2天就不能再修改
     * @param $game_id
     */
    public function edit($game_id)
    {
        if(!$game_id) {
            return $this->renderError('局ID不能为空');
        }
        $user = $this->getUser(true);
        $detail = GameModel::detail($game_id);
        if(!$detail) {
            return $this->renderError('局不存在');
        }
        if($detail['user_id'] != $user['user_id']) {
            return $this->renderError('局不属于您创建的');
        }
        $openTime = strtotime($detail['open_date'] . ' ' . $detail['start_time']);
        if($openTime < time()) {
            return $this->renderError('局已开始不可以再修改');
        }
        if(($openTime - time()) < 2*3600 && $openTime > time()) {
            return $this->renderError('两小时内开始的局不可以修改了哦');
        }
        $param = $this->getParam();
        //解决表情发送不了的问题
        if(isset($_POST['name']) && $_POST['name']) {
            $param['name'] = $_POST['name'];
        }
        if(isset($_GET['name']) && $_GET['name']) {
            $param['name'] = $_GET['name'];
        }
        if(isEmpty($param['name'])) {
            return $this->renderError('局标题不能为空');
        }
        $param['name'] = urldecode($param['name']);
        if(isset($_POST['desc']) && $_POST['desc']) {
            $param['desc'] = $_POST['desc'];
        }
        if(isset($_GET['desc']) && $_GET['desc']) {
            $param['desc'] = $_GET['desc'];
        }
        if(isEmpty($param['desc'])) {
            return $this->renderError('局详情不能为空');
        }
        $param['desc'] = urldecode($param['desc']);

        $total = (new GameApplyModel)->getCount($game_id, 1);
        if(($total > $param['people']) && $param['people'] > 0) {
            return $this->renderError('人数不能小于已报名的人数');
        }


        return $detail->edit($param, $detail) ? $this->renderSuccess([],'发布成功') : $this->renderError('发布失败');
    }

    /**
     * 订单结算提交的参数
     * @param array $define
     * @return array
     */
    private function getParam($define = [])
    {
        $param = $this->request->param();
        if(!is_array($param['imageList'])) {
            $param['imageList'] = explode(',', $param['imageList']);
        }
        if(!is_array($param['tagsList'])) {
            $param['tagsList'] = explode(',', $param['tagsList']);
        }
        if($param['city_id']) {
            $city = CityModel::detail(trim($param['city_id']));
            $param['city_id'] = $city ? $city['id'] : 0;
        }else{
            $param['city_id'] = 0;
        }

        return array_merge($define, $param);
    }

    /**
     * 搜索主页
     */
    public function hothome($city_id)
    {
        $hotList = (new KeywordModel)->getList();
        $param = [
            'city_id' => $city_id
        ];
        $game = new GameModel;
        if(count($hotList) > 0) {
            //根据关键词搜索出
            $ids = [];
            foreach($hotList as $key => $item) {
                $gameList = $game->getAll(['search' => $item['name'], 'city_id' => $city_id]);
                $ids = array_merge($ids, array_column($gameList, 'game_id'));
            }
            $param['ids'] = $ids;
        }
        $param['page'] = 1;
        $recommendList = $game->getList($param);

        return $this->renderSuccess([
            'hotList' => $hotList,
            'recommendList' => $recommendList
        ]);
    }

    /**
     * 搜索关键词列表
     */
    public function keyword($name, $city_id)
    {
        if(!$name) {
            return $this->renderError('关键词不能为空');
        }
        $user = $this->getUser(true);
        $keywordId = (new KeywordModel)->add($name);
        (new GameKeywordModel)->add($user['user_id'], $keywordId);
        $gameModel = new GameModel;
        $list = $gameModel->getList(
            ['search' => $name, 'city_id' => $city_id, 'status' => 10, 'is_open' => 1, 'is_start' => 1]
        );
        $list->each(function($item, $key) {
            $item['actual_people'] = count($item['users']);
        });

        return $this->renderSuccess([
            'list' => $list
        ]);
    }

    /**
     * 解散局
     * 限制条件
     * 1、局未开始可以解除
     * @param $game_id
     */
    public function delete($game_id)
    {
        if(!$game_id) {
            return $this->renderError('局ID不能为空');
        }
        $user = $this->getUser(true);
        $detail = GameModel::detail($game_id);
        if(!$detail) {
            return $this->renderError('局不存在');
        }
        if($detail['user_id'] != $user['user_id']) {
            return $this->renderError('局不属于您创建的');
        }
        $openTime = strtotime($detail['open_date'] . ' ' . $detail['start_time']);
        if($openTime < time()) {
            return $this->renderError('局已开始不可以再解散');
        }
        if($detail->save(['is_delete' => 1])) {
            $group = GroupModel::detail(['game_id' => $game_id]);
            $res = $group->save(['is_delete' => 1]);

            $gameUsersModel = new GameUsersModel;
            $list = $gameUsersModel->getAllList($game_id);
            $userList = array_column($list, 'user_id');
            $push = new Push;
            if(count($userList) > 0) {
                $content = '[' . $detail['name'] . ']已被解散，看看别的局吧~';
                $push->pushmore(
                    $userList,
                    ['title' => 'Citrus', 'content' => $content, 'game_id' => $game_id]
                );
            }

            return $res ? $this->renderSuccess([],'操作成功') : $this->renderError('操作失败');
        }else{
            return $this->renderError('操作失败');
        }
    }

    /**
     * 局成员列表
     * @param $game_id
     */
    public function members($im_group_id)
    {
        if(!$im_group_id) {
            return $this->renderError('局群ID不能为空');
        }
        $detail = GroupModel::detail($im_group_id);
        $gameUsersModel = new GameUsersModel;
        $list = $gameUsersModel->getList($detail['game_id']);
        $user = $this->getUser(true);   // 用户信息
        foreach($list as $key => &$item) {
            $item['is_friend'] = 0;
            if($user['user_id'] == $item['user_id']) {
                $item['is_friend'] = 1;
            }else{
                if(RelationModel::detail(['user_id' => $user['user_id'], 'focus_user_id' => $item['user_id'], 'status' => 2])
                    ||
                    RelationModel::detail(['focus_user_id' => $user['user_id'], 'user_id' => $item['user_id'], 'status' => 2])
                ){
                    $item['is_friend'] = 1;
                }
            }
        }

        return $this->renderSuccess([
            'list' => $list
        ]);
    }

    /**
     * 剔除人
     * @param $game_id
     * @param $user_id
     */
    public function exitmember($im_group_id, $user_id)
    {
        if(!$im_group_id) {
            return $this->renderError('局群ID不能为空');
        }
        $detail = GroupModel::detail($im_group_id);
        $game_id = $detail['game_id'];
        $user = $this->getUser(true);   // 用户信息
        $game = GameModel::detail($game_id);
        if($game['user_id'] != $user['user_id']) {
            return $this->renderError('此局属于您创建的不能踢人');
        }
        if($user_id == $user['user_id']) {
            return $this->renderError('不能踢自己');
        }
        $gameUser = GameUsersModel::detail($game_id, $user_id);
        if(!$gameUser) {
            return $this->renderError('此用户已不在局内');
        }
        $gameUser->setDelete();
        $group = GroupModel::detail(['game_id' => $game_id]);
        $this->_imService = new ImService;
        $this->_imService->deleteGroupMember($group['im_group_id'], $user_id);

        return $this->renderSuccess([], '操作成功');
    }

}