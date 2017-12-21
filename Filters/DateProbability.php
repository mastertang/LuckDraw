<?php
namespace LuckDraw\Filters;

use LuckDraw\DrawKernel;

class DateProbability
{
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
    public function filter(DrawKernel $drawKernel, $probabilityChange)
    {
        if (!empty($probabilityChange) && is_array($probabilityChange)) {
            $ymd = $drawKernel->getYmd();
            $nowStamp = $drawKernel->getNowStamp();
            $nowProbability = [];
            if (isset($probabilityChange[$ymd])) {
                $nowProbability = $probabilityChange[$ymd];
            } else {
                foreach ($probabilityChange as $time => $probabity) {
                    if (strpos($time, "|") !== false) {
                        $timeSection = explode("|", $time);
                        if (sizeof($timeSection) == 2) {
                            $startDate = strtotime($timeSection[0] . " 00:00:01");
                            $endDate = strtotime($timeSection[1] . " 23:59:59");
                            if ($startDate !== false && $endDate !== false && $endDate > $startDate) {
                                if ($nowStamp <= $endDate && $nowStamp >= $startDate) {
                                    $nowProbability = $probabity;
                                }
                            }
                        }
                    }
                }
            }
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
                    $end = strtotime($ymd . ' ' . $section[0][1]);
                    if ($nowStamp <= $end && $nowStamp >= $start) {
                        $drawKernel->setProbability($section[1]);
                        break;
                    }
                }
            }
        }
    }
}