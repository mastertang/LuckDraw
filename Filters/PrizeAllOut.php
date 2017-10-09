<?php
namespace LuckDraw\Filters;

use LuckDraw\DrawKernel;
use LuckDraw\Exceptions\FilterParamsErrorException;
use LuckDraw\Exceptions\NotWinPrizeException;

class PrizeAllOut
{
    /**
     * 过滤器默认方法-检查每个人可中奖数量
     * $limit => 奖品总数
     * $countHandler => callable| 10 可以为callable参数返回当前所有已中的键盘数量，或直接给总数int
     */
    public function filter(DrawKernel $drawKernel, $limit, $countHandler)
    {
        if (is_int($limit) && $limit >= 0) {
            if ($limit == 0) {
                throw new NotWinPrizeException();
            }
            $count = 0;
            if (is_int($countHandler) && $countHandler >= 0) {
                $count = $countHandler;
            } elseif (is_callable($countHandler)) {
                $count = call_user_func_array($countHandler, []);
                if (!is_int($count) || $count < 0) {
                    throw new FilterParamsErrorException();
                }
            }
            var_dump($count);
            var_dump($limit);
            if ($count >= $limit) {
                throw new NotWinPrizeException();
            }
        }
    }
}