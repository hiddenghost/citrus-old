<?php

namespace app\common\model;

use app\common\library\helper;

/**
 * 局
 * Class Game
 * @package app\common\model
 */
class Game extends BaseModel
{
    protected $name = 'game';

    /**
     * 追加字段
     * @var array
     */
    protected $append = [
        'surplus_people',
        'open_year',
    ];

    /**
     * 获取器：拼单状态
     * @param $value
     * @return array
     */
    public function getStatusAttr($value)
    {
        $state = [
            10 => '上架',
            20 => '下架',
        ];
        return ['text' => $state[$value], 'value' => $value];
    }

    /**
     * 获取器：剩余拼团人数
     * @param $value
     * @return array
     */
    public function getSurplusPeopleAttr($value, $data)
    {
        return $data['people'] - $data['actual_people'];
    }

    /**
     * 优惠券颜色
     * @param $value
     * @return mixed
     */
    public function getIsOpenAttr($value)
    {
        $status = [1 => '公开局', 2 => '私人局'];
        return ['text' => $status[$value], 'value' => $value];
    }

    /**
     * 活动费用类型
     * @param $value
     * @return mixed
     */
    public function getPriceTypeAttr($value)
    {
        $status = [1 => '请客', 2 => 'AA制'];
        return ['text' => $status[$value], 'value' => $value];
    }

    /**
     * 有效期
     * @param $value
     * @return mixed
     */
    public function getOpenDateAttr($value)
    {
        return date('m/d', strtotime($value));
    }

    /**
     * 有效期
     * @param $value
     * @return mixed
     */
    public function getOpenYearAttr($value, $data)
    {
        return date('Y', strtotime($data['open_date']));
    }

    /**
     * 有效期
     * @param $value
     * @return mixed
     */
    public function getOpenTimeAttr($value)
    {
        return ['text' => date('m-d', strtotime($value)), 'value' => $value];
    }

    /**
     * 关联商品分类表
     * @return \think\model\relation\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('GameCategory', 'category_id', 'category_id');
    }

    /**
     * 关联商品图片表
     * @return \think\model\relation\HasMany
     */
    public function image()
    {
        return $this->hasMany('GameImage')->where('is_delete', 0)->order(['id' => 'asc']);
    }

    /**
     * 关联收藏表
     * @return \think\model\relation\HasMany
     */
    public function collect()
    {
        return $this->hasMany('GameCollect')->where('is_delete', 0)->order(['collect_id' => 'asc']);
    }

    /**
     * 关联商品规格表
     * @return \think\model\relation\HasMany
     */
    public function tags()
    {
        return $this->hasMany('GameTags')->where('is_delete', 0)->order(['game_tag_id' => 'asc']);
    }

    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\User");
    }

    /**
     * 关联成员表
     * @return \think\model\relation\HasMany
     */
    public function users()
    {
        return $this->hasMany('GameUsers', 'game_id')
            ->where('is_delete', 0)
            ->order(['is_creator' => 'desc', 'create_time' => 'asc']);
    }

    /**
     * 获取商品列表
     * @param $param
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function getList($param)
    {
        // 商品列表获取条件
        $params = array_merge([
            'city_id' => 0,         //城市
            'is_open' => 1,         //公开局
            'status' => 10,         // 状态
            'category_id' => 0,     // 分类id
            'search' => '',         // 搜索关键词
            'sortType' => 'all',    // 排序类型
            'sortPrice' => false,   // 价格排序 高低
            'listRows' => 10,       // 每页数量
            'ids' => [],
            'is_start' => 0, //去掉已开始和已结束的局
        ], $param);
        // 筛选条件
        $filter = [];
        $params['category_id'] > 0 && $filter['category_id'] = ['IN', Category::getSubCategoryId($params['category_id'])];
        $params['status'] > 0 && $filter['status'] = $params['status'];
        !empty($params['search']) && $filter['name'] = ['like', '%' . trim($params['search']) . '%'];
        $params['city_id'] > 0 && $filter['city_id'] = $params['city_id'];
        $params['is_open'] > 0 && $filter['is_open'] = $params['is_open'];
        if(count($params['ids']) > 0) {
            $filter['game_id'] = ['IN', $params['ids']];
        }
        //去掉已开始的局和已过期的局
        if($params['is_start'] > 0) {
            $curDay = date('Y-m-d', time());
            $curTime = date('H:i', time());
            $this->where(function ($query) use ($curDay, $curTime) {
                $query->whereOr(function ($query2) use ($curDay, $curTime) {
                    $query2->where('open_date', '=', $curDay)
                        ->where('start_time', '>', $curTime);
                });
                $query->whereOr(function ($query2) use ($curDay, $curTime) {
                    $query2->where('open_date', '>', $curDay);
                });
            });
        }

        // 排序规则
        $sort = [];
        if ($params['sortType'] === 'all') {
            $sort = ['sort' => 'asc', 'game_id' => 'desc'];
        } elseif ($params['sortType'] === 'price') {
            $sort = $params['sortPrice'] ? ['price' => 'desc'] : ['price' => 'asc'];
        }

        // 执行查询
        $list = $this->with(['category', 'image', 'tags' => ['tags'], 'users' => ['user'], 'user', 'collect'])
            ->where('is_delete', '=', 0)
            ->where($filter)
            ->order($sort)
            ->paginate($params['listRows'], false, [
                'query' => \request()->request()
            ]);

        return $list;
    }

    /**
     * 根据关键词获取列表数据
     * @param string $keyword
     */
    public function getListByName($keyword)
    {
        $list = $this->where('is_delete', '=', 0)
            ->where('name', 'like', '%' . $keyword . '%')
            ->column('game_id');

        return $list;
    }

    /**
     * 得到所有正在进行的局列表
     * @param $user_id
     */
    public function getRunList($user_id)
    {
        $curDay = date('Y-m-d', time());
        $curTime = date('H:i', time());
        $list = $this->with(['users'])
            ->where('is_delete', '=', 0)
            ->where('user_id', '=', $user_id)
            ->where('open_date', '=', $curDay)
            ->where('end_time', '>', $curTime)
            ->where('start_time', '<', $curTime)
            ->select();

        return $list;
    }

    /**
     * 得到我创建的局列表
     * @param $user_id
     */
    public function getCreateList($user_id, $keyword = '')
    {
        // 执行查询
        $keyword && $this->where('name', 'like', '%' . $keyword . '%');
        //去掉已过期的局
        $curDay = date('Y-m-d', time());
        $curTime = date('H:i', time());
        $this->where(function ($query) use ($curDay, $curTime) {
            $query->whereOr(function ($query2) use ($curDay, $curTime) {
                $query2->where('open_date', '=', $curDay)
                    ->where('end_time', '>', $curTime);
            });
            $query->whereOr(function ($query2) use ($curDay, $curTime) {
                $query2->where('open_date', '>', $curDay);
            });
        });
        $list = $this->with(['category', 'image', 'tags' => ['tags'], 'users' => ['user'], 'user', 'collect'])
            ->where('is_delete', '=', 0)
            ->where('user_id', '=', $user_id)
            ->order(['open_date' => 'asc', 'start_time' => 'asc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);

        return $list;
    }

    /**
     * 得到我所有创建的局列表
     * @param $user_id
     */
    public function getAllCreateList($user_id)
    {
        // 执行查询
        //去掉已过期的局
        $curDay = date('Y-m-d', time());
        $curTime = date('H:i', time());
        $this->where(function ($query) use ($curDay, $curTime) {
            $query->whereOr(function ($query2) use ($curDay, $curTime) {
                $query2->where('open_date', '=', $curDay)
                    ->where('end_time', '>', $curTime);
            });
            $query->whereOr(function ($query2) use ($curDay, $curTime) {
                $query2->where('open_date', '>', $curDay);
            });
        });
        $list = $this->where('is_delete', '=', 0)
            ->where('user_id', '=', $user_id)
            ->select();

        return $list;
    }

    /**
     * 得到所有已结束未分配积分的局
     */
    public function getAllEndList()
    {
        $curDay = date('Y-m-d', time());
        $curTime = date('H:i', time());
        $this->where(function ($query) use ($curDay, $curTime) {
            $query->whereOr(function ($query2) use ($curDay, $curTime) {
                $query2->where('open_date', '=', $curDay)
                    ->where('end_time', '<', $curTime);
            });
            $query->whereOr(function ($query2) use ($curDay, $curTime) {
                $query2->where('open_date', '<', $curDay);
            });
        });
        $list = $this->with(['users'])
            ->where('is_delete', '=', 0)
            ->where('is_points', '=', 0)
            ->select();

        return $list;
    }

    /**
     * 得到所有即将开始 开始前一天到两小时间的局 以及未推送的局
     */
    public function getAllStartList()
    {
        $curDay = date('Y-m-d', time() + 24*3600);
        $curTime = date('H:i', time());
        $this->where(function ($query) use ($curDay, $curTime) {
            $query->whereOr(function ($query2) use ($curDay, $curTime) {
                $query2->where('open_date', '=', $curDay)
                    ->where('start_time', '<', date('H:i', time() + 24*3600))
                    ->where('start_time', '>', $curTime + 2*3600);
            });
        });
        $list = $this->with(['users'])
            ->where('is_delete', '=', 0)
            ->where('is_day_push', '=', 0)
            ->select();

        return $list;
    }

    /**
     * 得到所有即将开始 前两小时间的局 以及未推送的局
     */
    public function getAllStartHourList()
    {
        $curDay = date('Y-m-d', time());
        $curTime = date('H:i', time());
        $this->where(function ($query) use ($curDay, $curTime) {
            $query->whereOr(function ($query2) use ($curDay, $curTime) {
                $query2->where('open_date', '=', $curDay)
                    ->where('start_time', '<', date('H:i', time() + 2*3600))
                    ->where('start_time', '>', $curTime);
            });
            $query->whereOr(function ($query2) use ($curDay, $curTime) {
                $query2->where('open_date', '=', date('Y-m-d', time() + 3600*24))
                    ->where('start_time', '<', date('H:i', time() + 2*3600));
            });
        });
        $list = $this->with(['users'])
            ->where('is_delete', '=', 0)
            ->where('is_hour_push', '=', 0)
            ->select();

        return $list;
    }

    /**
     * 得到我报名参加的局列表
     * @param $user_id
     */
    public function getJoinList($user_id)
    {

    }

    /**
     * 查看朋友的局
     * @param $createList
     * @param $joinGameList
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getFriendList($createList, $joinGameList)
    {
        $this->where(function ($query) use ($createList, $joinGameList) {
            $query->whereOr('user_id', 'IN', $createList);
            $query->whereOr('game_id', 'IN', $joinGameList);
        });

        $curDay = date('Y-m-d', time());
        $curTime = date('H:i', time());
        $this->where(function ($query) use ($curDay, $curTime) {
            $query->whereOr(function ($query2) use ($curDay, $curTime) {
                $query2->where('open_date', '=', $curDay)
                    ->where('start_time', '>', $curTime);
            });
            $query->whereOr(function ($query2) use ($curDay, $curTime) {
                $query2->where('open_date', '>', $curDay);
            });
        });

        // 执行查询
        $list = $this->with(['category', 'image', 'tags' => ['tags'], 'users' => ['user'], 'user', 'collect'])
            ->where('is_delete', '=', 0)
            ->order(['game_id' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);

        return $list;
    }


    /**
     * 获取所有列表
     * @param $param
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function getAll($param)
    {
        // 商品列表获取条件
        $params = array_merge([
            'city_id' => 0,         //城市
            'is_open' => 1,         //公开局
            'status' => 10,         // 状态
            'category_id' => 0,     // 分类id
            'search' => '',         // 搜索关键词
        ], $param);
        // 筛选条件
        $filter = [];
        $params['category_id'] > 0 && $filter['category_id'] = ['IN', Category::getSubCategoryId($params['category_id'])];
        $params['status'] > 0 && $filter['status'] = $params['status'];
        !empty($params['search']) && $filter['name'] = ['like', '%' . trim($params['search']) . '%'];
        $params['city_id'] > 0 && $filter['city_id'] = $params['city_id'];
        $params['is_open'] > 0 && $filter['is_open'] = $params['is_open'];

        // 执行查询
        $list = $this->where('is_delete', '=', 0)
            ->where($filter)
            ->select()->toArray();

        return $list;
    }

    /**
     * 详情
     * @param $game_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($game_id)
    {
        return self::get($game_id, ['category', 'image', 'tags' => ['tags'], 'users' => ['user'], 'user', 'collect']);
    }

}
