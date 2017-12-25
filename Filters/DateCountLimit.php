<?php
namespace LuckDraw\Filters;

use LuckDraw\DrawKernel;
use LuckDraw\Exceptions\NotWinPrizeException;

class DateCountLimit
{
    /**
     * 过滤器默认方法-根据设定的配置要求动态设置当前日期某个时段每个奖品的中奖概
     * $probability = [
     *     '2017-07-06' => [1=>20,2=>30]
     *     '2017-08-11|2017-10-20' => [1=>10,2=>15]
     *    ......
     * ]
     * $ymd = $drawKernel->getYmd();
     * $limit = [];
     * if (isset($countLimit[$ymd])) {
     *    $limit = $countLimit[$ymd];
     * } else {
     *    $nowStamp = $drawKernel->getNowStamp();
     *    foreach ($countLimit as $key => $newLimit) {
     *       if (strpos($key, '|') !== false) {
     *           list($start, $end) = explode('|', $key);
     *           $start .= ' 00:00:01';
     *           $end .= ' 23:59:59';
     *           $startStamp = strtotime($start);
     *           $endStamp = strtotime($end);
     *           if ($nowStamp <= $endStamp && $nowStamp >= $startStamp) {
     *               $limit = $newLimit;
     *               break;
     *           }
     *        }
     *    }
     * }
     */
    public function filter(DrawKernel $drawKernel, $countLimit, $handler)
    {
        if (!empty($countLimit) && is_array($countLimit)) {
            $ymd = $drawKernel->getYmd();
            $nowStamp = $drawKernel->getNowStamp();
            $limit = [];
            foreach ($countLimit as $date => $limitCount) {
                $tempLimit = [];
                if (strpos($date, '|') !== false) {
                    list($start, $end) = explode('|', $date);
                    $start .= ' 00:00:01';
                    $end .= ' 23:59:59';
                    $startStamp = strtotime($start);
                    $endStamp = strtotime($end);
                    if ($startStamp !== false && $endStamp !== false) {
                        if (($nowStamp <= $endStamp && $nowStamp >= $startStamp) ||
                            ($nowStamp >= $endStamp)
                        ) {
                            $tempLimit = $limitCount;
                        }
                    }
                } else {
                    if (trim($date, " ") == $ymd) {
                        $tempLimit = $limitCount;
                    } else {
                        $endStamp = strtotime($date . " 23:59:59");
                        if ($nowStamp >= $endStamp && $endStamp !== false) {
                            $tempLimit = $limitCount;
                        }
                    }
                }
                if (!empty($tempLimit)) {
                    foreach ($tempLimit as $prizeId => $count) {
                        if (isset($limit[$prizeId])) {
                            $limit[$prizeId] = $limit[$prizeId] + $count;
                        } else {
                            $limit[$prizeId] = $count;
                        }
                    }
                }
            }
            if (!empty($limit)) {
                $newPrizeProbability = $drawKernel->getPrizeProbability();
                foreach ($limit as $prizeId => $count) {
                    $nowCount = call_user_func_array($handler, [$prizeId + 1]);
                    if ($nowCount >= $count) {
                        unset($newPrizeProbability[$prizeId]);
                    }
                }
                $drawKernel->setPrizeProbability($newPrizeProbability);
            } else {
                throw new NotWinPrizeException();
            }
        }
    }
}