<?php
namespace LuckDraw\Filters;

use LuckDraw\DrawKernel;

class DatePrizeProbability
{
    /**
     * 过滤器默认方法-根据设定的配置要求动态设置当前日期某个时段每个奖品的中奖概
     * $probability = [
     *     '2017-07-06'=>[
     *          [['09:22:00','10:55:50'],[1=>50,2=>50]],
     *          [['15:56:23','18:22:10'],[1=>30,2=>70]]
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
                    is_array($section[1])
                ) {
                    $start = strtotime($ymd . ' ' . $section[0][0]);
                    $end = strtotime($ymd . ' ' . $section[0][1]);
                    if ($nowStamp <= $end && $nowStamp >= $start) {
                        $drawKernel->setPrizeProbability($section[1]);
                        break;
                    }
                }
            }
        }
    }
}