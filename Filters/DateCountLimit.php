<?php

namespace LuckDraw\Filters;

use LuckDraw\DrawKernel;
use LuckDraw\Exceptions\NotWinPrizeException;

class DateCountLimit
{
    public $countLimit = [];
    public $handler    = null;
    public $nowPrizeCount = [];

    public function __construct($countLimit, $handler)
    {
        $this->countLimit = is_array($countLimit) && !empty($countLimit) ? $countLimit : [];
        ksort($this->countLimit);
        $this->handler = (is_array($countLimit) && !empty($countLimit)) || ($handler instanceof \Closure) ? $handler : null;
    }

    /*
     * 获取当前已中奖奖品数量
     */
    public function getNowPrizeCount(){
        return $this->nowPrizeCount;
    }

    /**
     * 过滤器默认方法-根据设定的配置要求动态设置当前日期某个时段每个奖品的中奖概
     * $probability = [
     *     '2017-07-06' => [1=>20,2=>30]
     *     '2017-08-11|2017-10-20' => [1=>10,2=>15]
     *    ......
     * ]
     */
    public function filter(DrawKernel $drawKernel)
    {
        if (!empty($this->countLimit)) {
            $ymd            = $drawKernel->getYmd();
            $nowStamp       = $drawKernel->getNowStamp();
            $limit          = [];
            $minSection     = 0;
            $minKey         = null;
            $needStartStamp = 0;
            $needEndStamp   = 0;
            foreach ($this->countLimit as $date => $limitCount) {
                $date = trim($date, " ");
                if (strpos($date, '|') !== false) {
                    list($start, $end) = explode('|', $date);
                    $start      .= ' 00:00:00';
                    $end        .= ' 24:00:00';
                    $startStamp = strtotime($start);
                    $endStamp   = strtotime($end);
                    if ($startStamp !== false && $endStamp !== false &&
                        (($nowStamp <= $endStamp && $nowStamp >= $startStamp) || ($nowStamp >= $endStamp)) &&
                        ($minSection === 0 || ($endStamp - $startStamp) < $minSection)) {
                        $minKey         = $date;
                        $needStartStamp = $startStamp;
                        $needEndStamp   = $endStamp;
                    }
                } else if (strtotime($date) == strtotime($ymd) && strtotime($date) !== false && strtotime($ymd) !== false) {
                    $minKey         = $date;
                    $needStartStamp = strtotime($date . ' 00:00:00');
                    $needEndStamp   = strtotime($date . ' 24:00:00');
                }
            }
            if (!is_null($minKey) && is_string($date)) {
                $limit = $this->countLimit[$minKey];
            }
            if (!empty($limit)) {
                $newPrizeProbability = $drawKernel->getPrizeProbability();
                if (isset($limit[0])) {
                    array_unshift($limit, 'test');
                    unset($limit[0]);
                }
                $consolationPrizeId = $drawKernel->getConsolationPrizeId();
                foreach ($limit as $prizeId => $count) {
                    if ($prizeId == $consolationPrizeId) {
                        continue;
                    }
                    if (is_array($this->handler)) {
                        $nowCount = $this->handler[$prizeId];
                    } else {
                        $nowCount = call_user_func_array($this->handler, [$prizeId, $needStartStamp, $needEndStamp]);
                    }
                    $this->nowPrizeCount[$prizeId] = $nowCount;
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