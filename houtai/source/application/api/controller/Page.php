<?php

namespace app\api\controller;

use app\api\model\WxappPage;
use app\common\model\City as CityModel;
use app\api\model\user\City as UserCityModel;
use app\api\model\Game as GameModel;
use app\api\model\GameCategory as GameCategoryModel;

/**
 * 页面控制器
 * Class Index
 * @package app\api\controller
 */
class Page extends Controller
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
    }

    /**
     * 初始化首页数据
     */
    public function home($city)
    {
        // 用户信息
        $this->user = $this->getUser(true);
        $curCity = CityModel::detail($city);
        if($curCity) {
            $userCity = UserCityModel::detail(['user_id' => $this->user['user_id'], 'city_id' => $curCity['id']]);
            if($userCity) {
                $userCity->edit(['current_time' => time()]);
            }else{
                (new UserCityModel)->add(['user_id' => $this->user['user_id'], 'city_id' => $curCity['id'], 'current_time' => time()]);
            }
        }
        $userCityList = (new UserCityModel)->getList($this->user['user_id']);
        $userCityIds = [];
        foreach($userCityList as $key => $item) {
            $userCity = CityModel::detail($item['city_id']);
            if($userCity && $key < 3) {
                $userCityIds[] = $userCity;
            }
        }
        $categoryList = GameCategoryModel::getCacheAll();
        $data = [
            'curCity' => $curCity,
            'userCityList' => $userCityIds,
            'openCityList' => (new CityModel)->getListByLevel(3),
            'categoryList' => $categoryList,
        ];
        return $this->renderSuccess($data);
    }

    /**
     * 未登录局列表
     */
    public function game()
    {
        // 整理请求的参数
        $param = array_merge($this->request->param(), [
            'status' => 10,
            'is_open' => 1,
            'page' => 1,
        ]);
        // 获取列表数据
        $model = new GameModel;
        $list = $model->getList($param);
        return $this->renderSuccess(compact('list'));
    }

    /**
     * app分享图片
     */
    public function shareimg()
    {
        $detail = [
            'share_title' => '欢迎下载Citrus橘子APP',
            'share_image' => 'https://share.citrusjuzi.cn/web/uploads/app.png'
        ];
        return $this->renderSuccess(compact('detail'));
    }

    /**
     * app分享局聊
     */
    public function sharegame($game_id)
    {
        //验证微信端
        if(!$game_id) {
            return $this->renderError('编号不存在');
        }
        $model = new GameModel;
        $game = $model->getDetails($game_id, $this->getUser(false));
        $share_image = 'https://share.citrusjuzi.cn/web/uploads/app.png';
        if(count($game['image']) > 0) {
            $share_image = $game['image'][0]['image_id'];
        }
        $share_image = str_replace('http://', 'https://', $share_image);
        $detail = [
            'name' => $game['name'],
            'game_id' => $game['game_id'],
            'desc' => $game['open_date'] . ' ' . $game['start_time'] . '~' . $game['end_time'] . ' ' . $game['address'],
            'share_image' => $share_image,
            'url' => 'https://share.citrusjuzi.cn/h5/index.html?game_id=' . $game['game_id']
        ];
        return $this->renderSuccess(compact('detail'));
    }

}
