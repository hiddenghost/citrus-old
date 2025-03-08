<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\User as UserModel;
use app\api\model\Order as OrderModel;
use app\api\model\Setting as SettingModel;
use app\api\model\im\Relation as RelationModel;

/**
 * 个人中心主页
 * Class Index
 * @package app\api\controller\user
 */
class Index extends Controller
{
    /**
     * 获取当前用户信息
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function detail()
    {
        // 当前用户信息
        $user = $this->getUser(false);
        // 订单总数
        $model = new OrderModel;
        return $this->renderSuccess([
            'userInfo' => $user,
            'orderCount' => [
                'payment' => $model->getCount($user, 'payment'),
                'received' => $model->getCount($user, 'received'),
                'comment' => $model->getCount($user, 'comment'),
            ],
            'setting' => [
                'points_name' => SettingModel::getPointsName(),
            ],
            'menus' => (new UserModel)->getMenus()   // 个人中心菜单列表
        ]);
    }

    /**
     * 搜索用户列表
     * @param $keyword
     */
    public function search($keyword)
    {
        if(!$keyword) {
            return $this->renderError('搜索关键词不能为空');
        }
        $this->_user = $this->getUser(true);
        $list = (new UserModel)->getList($keyword, $this->_user['user_id']);
        $list->each(function ($item, $key) {
            $relationList = (new RelationModel)->getSamllFriendList($this->_user['user_id'], $item['user_id']);
            $item['relationList']= $relationList;
            $item['label'] = '';
            if(count($relationList->items()) > 0) {
                $labelList = [];
                foreach($relationList->items() as $relation) {
                    $labelList[] = $relation['nickName'];
                }
                $item['label'] = '被' . implode(',', $labelList) . '等' . $relationList->total() . '位朋友关注';
            }

        });

        return $this->renderSuccess([
            'list' => $list
        ]);
    }

    private $_user;

}
