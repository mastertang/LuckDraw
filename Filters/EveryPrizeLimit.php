<?php
namespace LuckDraw\Filters;

use LuckDraw\DrawKernel;
use LuckDraw\Exceptions\FilterParamsErrorException;
use LuckDraw\Exceptions\NotWinPrizeException;

class EveryPrizeLimit
{
    /**
     * 过滤器默认方法-检查每个奖品可中奖数量
     * $prizeLimit => 每个奖品的可中奖数量,例:[1=>1,2=>3,3=>1]
     * $countHandler => callable| 10 可以为callable参数返回当前用户拥有奖品数量,也可以直接是用户拥有奖品数量
     */
    public function filter(DrawKernel $drawKernel, $prizeLimit, $countHandler)
    {
        if (is_array($prizeLimit) && !empty($prizeLimit)) {
            $nowPrizeProbability = $drawKernel->getPrizeProbability();
            if (empty($nowPrizeProbability)) {
                throw new  NotWinPrizeException();
            }
            $count = NULL;
            if (is_array($countHandler) && !empty($countHandler)) {
                $count = $countHandler;
            } elseif (is_callable($countHandler)) {
                $prize = array_keys($nowPrizeProbability);
                $count = call_user_func_array($countHandler, [$prize]);
                if (!is_array($count) && empty($count)) {
                    throw new FilterParamsErrorException();
                }
            } else {
                throw new FilterParamsErrorException();
            }
            $this->checkCountTypeValue($count);
            $this->checkCountTypeValue($prizeLimit);
            foreach ($nowPrizeProbability as $prizeId => $probability) {
                if ($count[$prizeId] >= $prizeLimit[$prizeId]) {
                    unset($nowPrizeProbability[$prizeId]);
                }
            }
            if (empty($nowPrizeProbability)) {
                throw new  NotWinPrizeException();
            }
            $drawKernel->setPrizeProbability($nowPrizeProbability);
        }
    }

    public function checkCountTypeValue($data)
    {
        foreach ($data as $values) {
            if (!is_int($values) || $values < 0) {
                throw new FilterParamsErrorException();
            }
        }
    }
}