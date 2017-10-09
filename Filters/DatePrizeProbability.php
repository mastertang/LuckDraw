<?php
namespace LuckDraw\Filters;

use LuckDraw\DrawKernel;

class DatePrizeProbability
{
    /**
     * 过滤器默认方法-根据设定的配置要求动态设置当前日期某个时段每个奖品的中奖概
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