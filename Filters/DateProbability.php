<?php

namespace LuckDraw\Filters;

use LuckDraw\DrawKernel;

class DateProbability
{
    public $probabilityChange = [];

    public function __construct($probaility)
    {
        if (is_array($probaility) && !empty($probaility)) {
            $this->probabilityChange = $probaility;
        }
    }

    /**
     * 过滤器默认方法-根据设定的日期某个时段中奖概率的改变设置不同的中奖概率
     * $probability = [
     *     '2017-07-06'=>[
     *          [['09:22:00','10:55:50'],20],
     *          [['15:56:23','18:22:10'],30]
     *         ]
     *    ......
     * ]
     */
    public function filter(DrawKernel $drawKernel)
    {
        if (!empty($this->probabilityChange)) {
            $ymd            = $drawKernel->getYmd();
            $nowStamp       = $drawKernel->getNowStamp();
            $nowProbability = [];
            $minSection     = 0;
            $minKey         = null;
            foreach ($this->probabilityChange as $time => $probabity) {
                $time = trim($time, " ");
                if (strpos($time, "|") !== false) {
                    $timeSection = explode("|", $time);
                    if (sizeof($timeSection) == 2) {
                        $startDate = strtotime($timeSection[0] . " 00:00:00");
                        $endDate   = strtotime($timeSection[1] . " 24:00:00");
                        if ($startDate !== false && $endDate !== false && $endDate > $startDate &&
                            $nowStamp <= $endDate && $nowStamp >= $startDate &&
                            ($minSection === 0 || ($endDate - $startDate) > $minSection)) {
                            $minKey = $time;
                        }
                    }
                } else {
                    if (strtotime($time) == strtotime($ymd) && strtotime($time) !== false && strtotime($ymd) !== false) {
                        $minKey = $time;
                    }
                }
            }
            if (!is_null($minKey) && is_string($minKey)) {
                $nowProbability = $this->probabilityChange[$minKey];
                foreach ($nowProbability as $section) {
                    if (is_array($section) &&
                        isset($section[0]) &&
                        isset($section[1]) &&
                        isset($section[0][0]) &&
                        isset($section[0][1]) &&
                        !is_string($section[1]) &&
                        is_numeric($section[1])
                    ) {
                        $start = strtotime($ymd . ' ' . $section[0][0]);
                        $end   = strtotime($ymd . ' ' . $section[0][1]);
                        if ($nowStamp <= $end && $nowStamp >= $start && $end >= $start && is_numeric($section[1])) {
                            $drawKernel->setProbability($section[1]);
                            break;
                        }
                    }
                }
            }
        }
    }
}