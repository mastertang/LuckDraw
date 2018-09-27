<?php

namespace LuckDraw\Filters;

use LuckDraw\DrawKernel;
use LuckDraw\Exceptions\ActivityOutException;

class ActivityTime
{
    protected $startStamp    = '';
    protected $endStamp      = '';
    protected $configCorrect = false;

    public function __construct($startDate, $endDate)
    {
        if (!empty($startDate) && !empty($endDate)) {
            $startStamp = strtotime($startDate);
            $endStamp   = strtotime($endDate);
            if ($startStamp !== false && $endStamp !== $endStamp) {
                $this->startStamp    = $startStamp;
                $this->endStamp      = $endStamp;
                $this->configCorrect = true;
            }
        }
    }

    /**
     * 过滤器默认方法-设置活动抽奖日期
     * $start => '2017-02-19'
     * $end => '2017-069-20'
     */
    public function filter(DrawKernel $drawKernel)
    {
        if ($this->configCorrect) {
            if (
                $drawKernel->getNowStamp() < $this->startStamp ||
                $drawKernel->getNowStamp() > $this->endStamp
            ) {
                throw new ActivityOutException();
            }
        }
    }
}