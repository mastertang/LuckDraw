<?php
namespace LuckDraw\Filters;

use LuckDraw\DrawKernel;
use LuckDraw\Exceptions\ActivityOutException;

class ActivityTime
{
    /**
     * 过滤器默认方法-设置活动抽奖日期
     * $start => '2017-02-19'
     * $end => '2017-069-20'
     */
    public function filter(DrawKernel $drawKernel, $start, $end)
    {
        $startStamp = strtotime($start);
        $endStamp = strtotime($end);
        if (!empty($startStamp) && !empty($endStamp)) {
            if (
                $drawKernel->getNowStamp() < $startStamp ||
                $drawKernel->getNowStamp() > $endStamp
            ) {
                throw new ActivityOutException();
            }
        }
    }
}