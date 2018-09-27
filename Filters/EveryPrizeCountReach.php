<?php

namespace LuckDraw\Filters;

use LuckDraw\DrawKernel;
use LuckDraw\Exceptions\FilterParamsErrorException;
use LuckDraw\Exceptions\NotWinPrizeException;

class EveryPrizeCountReach
{
    protected $prizeCount   = [];
    protected $countHandler = null;
    protected $nowCount     = [];

    public function __construct($prizeCount, $countHandler)
    {
        if (is_array($prizeCount) && !empty($prizeCount)) {
            if (isset($prizeCount[0])) {
                array_unshift($prizeCount, 'test');
                unset($prizeCount[0]);
            }
            $this->prizeCount = $prizeCount;
        }
        $this->countHandler = ($countHandler instanceof \Closure) || (is_array($countHandler) && !empty($countHandler)) ? $countHandler : null;
    }

    /*
     * 获取每个奖品的可中奖数量
     */
    public function getNowPrizeCount()
    {
        return $this->nowCount;
    }

    /**
     * 过滤器默认方法-检查每个奖品可中奖数量
     * $prizeCount => 每个奖品的可中奖数量,例:[1=>10,2=>10,3=>10]
     */
    public function filter(DrawKernel $drawKernel)
    {
        if (!empty($this->prizeCount)) {
            $count               = [];
            $newPrizeProbability = $drawKernel->getPrizeProbability();
            if (empty($newPrizeProbability)) {
                throw new NotWinPrizeException();
            }
            if (is_array($this->countHandler)) {
                $count = $this->countHandler;
            } else {
                $prizeIds = array_keys($newPrizeProbability);
                $count    = call_user_func_array($this->countHandler, [$prizeIds]);
            }
            if (!is_array($count) || empty($count)) {
                throw new FilterParamsErrorException();
            }
            $this->nowCount = $count;
            foreach ($newPrizeProbability as $prizeId => $probability) {
                if ($count[$prizeId] >= $this->prizeCount[$prizeId]) {
                    unset($newPrizeProbability[$prizeId]);
                }
            }
            if (empty($newPrizeProbability)) {
                throw new NotWinPrizeException();
            }
            $drawKernel->setPrizeProbability($newPrizeProbability);
        }
    }
}