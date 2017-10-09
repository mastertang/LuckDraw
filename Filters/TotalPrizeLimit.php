<?php
namespace LuckDraw\Filters;

use LuckDraw\DrawKernel;
use LuckDraw\Exceptions\FilterParamsErrorException;
use LuckDraw\Exceptions\NotWinPrizeException;

class TotalPrizeLimit
{
    /**
     * 过滤器默认方法-检查每个人可中奖数量
     * $totalLimit => 10 用户可中奖总数为10个奖品
     * $countHandler => callable| 10 可以为callable参数返回当前用户拥有奖品数量,也可以直接是用户拥有奖品数量
     */
    public function filter(DrawKernel $drawKernel, $totalLimit, $countHandler)
    {
        if (is_int($totalLimit) || $totalLimit > 0) {
            if (is_int($countHandler) && $countHandler >= 0) {
                if ($countHandler >= $totalLimit) {
                    throw new NotWinPrizeException();
                }
            } elseif (is_callable($countHandler)) {
                $count = call_user_func_array($countHandler, []);
                if (!is_int($count) || $count < 0) {
                    throw new FilterParamsErrorException();
                }
                if ($count >= $totalLimit) {
                    throw new NotWinPrizeException();
                }
            } else {
                throw new FilterParamsErrorException();
            }
        }
    }
}