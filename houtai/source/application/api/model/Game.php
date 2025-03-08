<?php

namespace app\api\model;

use app\common\library\helper;
use app\common\model\Game as GameModel;
use app\api\model\GameImage as GameImageModel;
use app\api\model\GameTags as GameTagsModel;
use app\api\model\GameUsers as GameUsersModel;
use app\api\service\Im as ImService;

/**
 * 局模型
 * Class Goods
 * @package app\api\model
 */
class Game extends GameModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'is_delete',
        'wxapp_id',
        'create_time',
        'update_time'
    ];

    /**
     * 商品详情：HTML实体转换回普通字符
     * @param $value
     * @return string
     */
    public function getContentAttr($value)
    {
        return htmlspecialchars_decode($value);
    }

    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        $data['create_time'] = time();
        //私密则需要设置分享密码
        if($data['is_open'] == 2) {
            $data['code'] = getRandPassword(6);
        }
        return $this->transaction(function () use ($data) {
            $this->allowField(true)->save($data);
            if(isset($data['imageList']) && $data['imageList']) {
                (new GameImageModel)->addForGame($this['game_id'], $data['imageList']);
            }
            if(isset($data['tagsList']) && $data['tagsList']) {
                (new GameTagsModel)->addForGame($this['game_id'], $data['tagsList']);
            }
            //新增管理员用户
            (new GameUsersModel)->add([
               'game_id' => $this['game_id'],
                'order_id' => 0,
                'user_id' => $this['user_id'],
                'is_creator' => 1,
            ]);
            //创建群组
            /* 暂停Im推送 */
//            (new ImService)->createGroup($this['game_id']);

            return true;
        });

        return true;
    }

    /**
     * 修改新记录
     * @param $data
     */
    public function edit($data, $detail)
    {
        //私密则需要设置分享密码
        if($data['is_open'] == 2) {
            $data['code'] = getRandPassword(6);
        }else{
            $data['code'] = '';
        }
        return $this->transaction(function () use ($data, $detail) {
            $this->allowField(true)->save($data);
            if(isset($data['imageList']) && $data['imageList']) {
                (new GameImageModel)->editForGame($detail->toArray(), $data['imageList']);
            }
            if(isset($data['tagsList']) && $data['tagsList']) {
                (new GameTagsModel)->editForGame($detail->toArray(), $data['tagsList']);
            }

            return true;
        });

        return true;
    }

    /**
     * 获取局详情页面
     * @param int $goodsId 商品id
     * @param array|bool $userInfo 用户信息
     * @return array|bool|false|mixed|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDetails($gameId, $userInfo = false)
    {
        $detail = self::detail($gameId);
        if(!$detail) {
            return false;
        }
        return $detail;
    }
}
