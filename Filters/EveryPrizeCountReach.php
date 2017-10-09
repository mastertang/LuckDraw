<?php
namespace LuckDraw\Filters;

use LuckDraw\DrawKernel;
use LuckDraw\Exceptions\FilterParamsErrorException;
use LuckDraw\Exceptions\NotWinPrizeException;

class EveryPrizeCountReach
{
    /**
     * 过滤器默认方法-检查每个奖品可中奖数量
     * $prizeCount => 每个奖品的可中奖数量,例:[1=>10,2=>10,3=>10]
     */
    public function filter(DrawKernel $drawKernel, $prizeCount,$countHandler)
    {
        if (is_array($prizeCount) && !empty($prizeCount)) {
            $count = [];
            $newPrizeProbability = $drawKernel->getPrizeProbability();
            if (empty($newPrizeProbability)) {
                throw new NotWinPrizeException();
            }
            if (is_array($countHandler) && !empty($countHandler)) {
                $count = $countHandler;
            } else {
                $prizeId = array_keys($drawKernel->getPrizeProbability());
                $count = call_user_func_array($countHandler, [$prizeId]);
                if (!is_array($count) || empty($count)) {
                    throw new FilterParamsErrorException();
                }
            }
            $this->checkCountTypeValue($count);
            $this->checkCountTypeValue($prizeCount);
            foreach ($newPrizeProbability as $prizeId => $probability) {
                if ($count[$prizeId] >= $prizeCount[$prizeId]) {
                    unset($newPrizeProbability[$prizeId]);
                }
            }
            if (empty($newPrizeProbability)) {
                throw new NotWinPrizeException();
            }
            $drawKernel->setPrizeProbability($newPrizeProbability);
        }
    }

    public function checkCountTypeValue($data){
        foreach($data as $values){
            if(!is_int($values) || $values < 0){
                throw new FilterParamsErrorException();
            }
        }
    }
}