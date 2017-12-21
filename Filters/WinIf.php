<?php
namespace LuckDraw\Filters;

use LuckDraw\DrawKernel;
use LuckDraw\Exceptions\NotWinPrizeException;

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
        } elseif ($drawKernel->getProbability() != 100) {
            $rand = rand(1, $drawKernel->getMaxProbability());
            if ($rand > $drawKernel->getProbability()) {
                throw new NotWinPrizeException();
            }
        }
    }
}