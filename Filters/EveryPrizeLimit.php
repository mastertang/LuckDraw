<?php

namespace LuckDraw\Filters;

use LuckDraw\DrawKernel;
use LuckDraw\Exceptions\FilterParamsErrorException;
use LuckDraw\Exceptions\NotWinPrizeException;

class EveryPrizeLimit
{

    public $prizeLimit    = [];
    public $countHandler  = null;
    public $userPrizeList = [];

    public function __construct($prizeLimit, $countHandler)
    {
        $this->prizeLimit   = is_array($prizeLimit) && !empty($prizeLimit) ? $prizeLimit : [];
        $this->countHandler = ($countHandler instanceof \Closure) || (is_array($countHandler) && !empty($countHandler)) ? $countHandler : null;
    }

    /*
     * 获取用户中奖数量列表
     */
    public function getUserPrizeList()
    {
        return $this->userPrizeList;
    }

    /**
     * 过滤器默认方法-检查每个奖品可中奖数量
     * $prizeLimit => 每个奖品的可中奖数量,例:[1=>1,2=>3,3=>1]
     * $countHandler => callable| 10 可以为callable参数返回当前用户拥有奖品数量,也可以直接是用户拥有奖品数量
     */
    public function filter(DrawKernel $drawKernel)
    {
        if (!empty($this->prizeLimit)) {
            $nowPrizeProbability = $drawKernel->getPrizeProbability();
            if (empty($nowPrizeProbability)) {
                throw new  NotWinPrizeException();
            }
            $count = [];
            if (is_array($this->countHandler) && !empty($this->countHandler)) {
                $count = $this->countHandler;
            } elseif (is_callable($this->countHandler)) {
                $prizeIds = array_keys($nowPrizeProbability);
                $count    = call_user_func_array($this->countHandler, [$prizeIds]);
                if (!is_array($count) && empty($count)) {
                    throw new FilterParamsErrorException();
                }
            } else {
                throw new FilterParamsErrorException();
            }
            if (isset($count[0])) {
                array_unshift($count, 'test');
                unset($count[0]);
            }
            $consolationPrizeId  = $drawKernel->getConsolationPrizeId();
            $this->userPrizeList = $count;
            foreach ($nowPrizeProbability as $prizeId => $probability) {
                if ($consolationPrizeId == $prizeId) {
                    continue;
                }
                if ($count[$prizeId] >= $this->prizeLimit[$prizeId]) {
                    unset($nowPrizeProbability[$prizeId]);
                }
            }
            if (empty($nowPrizeProbability)) {
                throw new  NotWinPrizeException();
            }
            $drawKernel->setPrizeProbability($nowPrizeProbability);
        }
    }
}