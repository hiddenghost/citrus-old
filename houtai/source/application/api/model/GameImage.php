<?php

namespace app\api\model;

use app\common\model\GameImage as GameImageModel;

/**
 * 局图片模型
 * Class GoodsImage
 * @package app\api\model
 */
class GameImage extends GameImageModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'create_time',
    ];

    /**
     * 添加局图片
     * @param Order $order
     * @param \think\Collection|OrderGoods $goodsList
     * @param $formJsonData
     * @return boolean
     * @throws \Exception
     */
    public function addForGame($gameId, $imageList)
    {
        return $this->transaction(function () use ($gameId, $imageList) {
            $data = [];
            foreach($imageList as $key => $item) {
                $imageInfo = getimagesize($item);
                $extension = strtolower(pathinfo($item, PATHINFO_EXTENSION));
                if(function_exists('exif_read_data') && in_array($extension, ["jpg","jpeg"])) {
                    $exif = exif_read_data($item); //用来判断ios是否横着拍摄
                }else{
                    $exif = ['Orientation' => 1];
                }

                //Orientation = 1（不翻转）
                //Orientation = 2 （左右翻转）
                //Orientation = 3 （180度翻转）
                //Orientation = 4 （上下翻转）
                //Orientation = 5 （顺时针翻转90度后，左右翻转）
                //Orientation = 6 （顺时针翻转90度）
                //Orientation = 7 （逆时针翻转90度后，左右翻转）
                //Orientation = 8 （逆时针翻转90度）
                $width = $imageInfo[0];
                $height = $imageInfo[1];
                if(isset($exif['Orientation']) && ($exif['Orientation'] == 6 || $exif['Orientation'] == 8)) {
                    $width = $imageInfo[1];
                    $height = $imageInfo[0];
                }
                log_write(json_encode($exif) . '-图片角度');
                $data[] = [
                    'game_id' => $gameId,
                    'image_id' => $item,
                    'width' => $width,
                    'height'=> $height,
                    'wxapp_id' =>self::$wxapp_id,
                    'create_time'=> time()
                ];
            }
            $this->isUpdate(false)->saveAll($data);

            return true;
        });
    }

    /**
     * 修改图片信息
     * @param $game
     * @param $imageList
     */
    public function editForGame($game, $imageList)
    {
        return $this->transaction(function () use ($game, $imageList) {
            $data = [];

            $delImageList = [];
            foreach($game['image'] as $key => $item) {
                //如果没在新的图片里面则删除
                if(!in_array($item['image_id'], $imageList)) {
                    $delImageList[] = ['data' => ['is_delete' => 1], 'where' => ['id' => $item['id']]];
                }
            }
            $this->updateAll($delImageList);
            $oldImageList = array_column($game['image'], 'image_id');
            foreach($imageList as $key => $item) {
                if(!in_array($item, $oldImageList)) {
                    $imageInfo = getimagesize($item);
                    $data[] = [
                        'game_id' => $game['game_id'],
                        'image_id' => $item,
                        'width' => $imageInfo[0],
                        'height'=> $imageInfo[1],
                        'wxapp_id' =>self::$wxapp_id,
                        'create_time'=> time()
                    ];
                }
            }
            $this->isUpdate(false)->saveAll($data);

            return true;
        });
    }

}
