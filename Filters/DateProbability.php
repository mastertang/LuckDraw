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
            if (isset($probabilityChange[$ymd])) {
                $nowProbability = $probabilityChange[$ymd];
                foreach ($nowProbability as $section) {
                    if (is_array($section) &&
                        isset($section[0]) &&
                        isset($section[1]) &&
                        isset($section[0][0]) &&
                        isset($section[0][1]) &&
                        (is_int($section[1]) || is_float($section[1]) || is_double($section[1]))
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
}