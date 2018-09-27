<?php

namespace LuckDraw\Filters;

use LuckDraw\DrawKernel;
use LuckDraw\Exceptions\FilterParamsErrorException;
use LuckDraw\Exceptions\NotWinPrizeException;

class TotalPrizeLimit
{
    public $totalLimit   = 0;
    public $countHandler = null;
    public $totalCount   = 0;

    public function __construct($totalLimit, $countHandler)
    {
        $this->totalLimit   = is_int($totalLimit) && $totalLimit >= 0 ? $totalLimit : 0;
        $this->countHandler = (is_int($countHandler && $countHandler >= 0) || ($countHandler instanceof \Closure)) ? $countHandler : null;
    }

    /*
     * 获取现在中奖总数
     */
    public function getToltalCount()
    {
        return $this->totalCount;
    }

    /**
     * 过滤器默认方法-检查每个人可中奖数量
     * $totalLimit => 10 用户可中奖总数为10个奖品
     * $countHandler => callable| 10 可以为callable参数返回当前用户拥有奖品数量,也可以直接是用户拥有奖品数量
     */
    public function filter(DrawKernel $drawKernel)
    {
        if (!is_null($this->countHandler)) {
            if (is_int($this->countHandler)) {
                if ($this->countHandler >= $this->totalLimit) {
                    throw new NotWinPrizeException();
                }
                $this->totalCount = $this->countHandler;
            } elseif (is_callable($this->countHandler)) {
                $count = call_user_func_array($this->countHandler, []);
                if (!is_int($count) || $count < 0) {
                    throw new FilterParamsErrorException();
                }
                if ($count >= $this->totalLimit) {
                    throw new NotWinPrizeException();
                }
                $this->totalCount = $count;
            } else {
                throw new FilterParamsErrorException();
            }
        }
    }
}