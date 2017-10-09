<?php
namespace LuckDraw\Filters;

use LuckDraw\DrawKernel;
use LuckDraw\Exceptions\FilterParamsErrorException;
use LuckDraw\Exceptions\NotWinPrizeException;
use LuckDraw\Exceptions\TimeSectionDrawRefuseException;

class WinIf
{
    /**
     * 过滤器默认方法-检查是否中奖
     * $start => '2017-02-19'
     * $end => '2017-069-20'
     */
    public function filter(DrawKernel $drawKernel)
    {
        if ($drawKernel->getProbability() == 0) {
            throw new NotWinPrizeException();
        } else {
            $rand = rand(1, $drawKernel->getMaxProbability());
            if ($rand > $drawKernel->getProbability()) {
                throw new NotWinPrizeException();
            }
        }
    }
}