<?php

namespace LuckDraw;

/**
 * Class LuckDraw
 * @package LuckDraw
 */
class LuckDraw
{
    /*
     * 错误信息
     */
    protected $message = '';

    /**
     * @var int 总抽奖概率
     */
    protected $totalProbability = 0;

    /**
     * @var DrawKernel|null 核心算法类
     */
    protected $drawKernel = null;

    /**
     * @var null 设置redis类
     */
    protected $redisInstance = null;

    /**
     * @var null 奖品池redis键
     */
    protected $prizePoolRedisKey = null;

    /**
     * @var null 活动日期限制
     */
    protected $useActivityTimeLimit = null;

    /**
     * @var null 中奖概率跟日期变化列表
     */
    protected $useDateProbabilityLimit = null;

    /**
     * @var null 中奖每天时间段列表
     */
    protected $useDateTimeLimit = null;

    /**
     * @var null 奖品总数查询函数
     */
    protected $useTotalPrizeLimitFunction = null;

    /**
     * @var null 奖品总数判断
     */
    protected $totalPrizeCount = null;

    /**
     * @var array 奖品类列表
     */
    protected $prizeInstanceList = [];

    /*
     * 构造函数
     */
    public function __construct()
    {
        $this->drawKernel = new DrawKernel();
    }

    /**
     * 设置奖品池redis键
     *
     * @param $prizePoolRedisKey
     * @return $this
     */
    public function setPrizePoolRedisKey($prizePoolRedisKey)
    {
        $this->prizePoolRedisKey = $prizePoolRedisKey;
        return $this;
    }

    /*
     * 获取处理信息
     */
    public function getResultMessage()
    {
        return $this->message;
    }

    /**
     * 设置redis类型
     *
     * @param $redisInstance
     * @return $this
     */
    public function setRedisInstance($redisInstance)
    {
        $this->redisInstance = $redisInstance;
        return $this;
    }

    /**
     * 设置奖品item实例
     *
     * @param Prize $prizeInstance
     * @return $this
     */
    public function setPrizeInstance(Prize $prizeInstance)
    {
        $this->prizeInstanceList[] = $prizeInstance;
        return $this;
    }

    /**
     * 设置全局概率
     *
     * @param $probability
     * @return $this
     */
    public function setTotalProbability($probability)
    {
        if ($probability <= 0 || !is_numeric($probability)) {
            $this->totalProbability = 0;
        } else {
            $this->totalProbability = (int)$probability;
        }
        return $this;
    }

    /*
     * 添加活动时间限制
     * [
     *   ['2019-09-20 00:00:00','2020-09-30 00:01:22'],
     *   ['2019-09-20','2020-09-30'],
     * ]
     */
    public function addActivityTimeLimit($activityTimeList)
    {
        if (!is_array($activityTimeList)) {
            $activityTimeList = [];
        }
        foreach ($activityTimeList as $key => $activityTime) {
            if (!is_array($activityTime)
                || sizeof($activityTime) != 2
                || !isset($activityTime[0], $activityTime[1])
                || empty($activityTime[0]) || empty($activityTime[1])) {
                unset($activityTimeList[$key]);
            } else {
                $startStamp = strtotime($activityTime[0]);
                $endStamp   = strtotime($activityTime[1]);
                if (empty($startStamp) || empty($endStamp) || $endStamp <= $startStamp) {
                    unset($activityTimeList[$key]);
                } else {
                    $activityTimeList[$key] = [
                        $startStamp,
                        $endStamp
                    ];
                }
            }
        }
        $activityTimeList           = array_values($activityTimeList);
        $this->useActivityTimeLimit = $activityTimeList;
        return $this;
    }

    /*
     * 添加每天日期时间段限制
     * [
     *    ['00:00:00','22:01:20'],
     *    ['12:00:00','22:01:20']
     * ]
     */
    public function addDateTimeLimit($dateTimeList)
    {
        if (!is_array($dateTimeList)) {
            $dateTimeList = [];
        }
        foreach ($dateTimeList as $key => $dateTime) {
            if (!is_array($dateTime)
                || sizeof($dateTime) != 2
                || !isset($dateTime[0], $dateTime[1])
                || empty($dateTime[0]) || empty($dateTime[1])) {
                unset($dateTimeList[$key]);
            } else {
                $startStamp = strtotime($dateTime[0]);
                $endStamp   = strtotime($dateTime[1]);
                if (empty($startStamp) || empty($endStamp) || $endStamp <= $startStamp) {
                    unset($dateTimeList[$key]);
                } else {
                    $dateTimeList[$key] = [
                        $startStamp,
                        $endStamp
                    ];
                }
            }
        }
        $this->useDateTimeLimit = $dateTimeList;
        return $this;
    }

    /*
     * 添加日期中奖概率设置
     */
    public function addDateProbabilityLimit($dateProbabilityList)
    {
        if (is_array($dateProbabilityList) && !empty($dateProbabilityList)) {
            foreach ($dateProbabilityList as $key => $dateProbability) {
                if (!is_array($dateProbability)
                    || sizeof($dateProbability) != 3
                    || !isset($dateProbability[0], $dateProbability[1], $dateProbability[2])
                    || empty($dateProbability[0]) || empty($dateProbability[1])
                    || !is_numeric($dateProbability[2])
                    || $dateProbability[2] < 0) {
                    unset($dateProbabilityList[$key]);
                } else {
                    $startStamp = strtotime($dateProbability[0]);
                    $endStamp   = strtotime($dateProbability[1]);
                    if (empty($startStamp) || empty($endStamp) || $endStamp <= $startStamp) {
                        unset($dateProbabilityList[$key]);
                    } else {
                        if ($dateProbability[2] > 100) {
                            $dateProbability[2] = 100;
                        }
                        $dateProbabilityList[$key] = [
                            $startStamp,
                            $endStamp,
                            (int)$dateProbability[2]
                        ];
                    }
                }
            }
        }
        $this->useDateProbabilityLimit = $dateProbabilityList;
        return $this;
    }

    /*
     * 添加奖品总数量限制
     */
    public function addTotalPrizeLimit($totalCountFunction, $totalPrizeCount = null)
    {
        if (is_callable($totalCountFunction)) {
            $this->useTotalPrizeLimitFunction = $totalCountFunction;
            $this->totalPrizeCount            = $totalPrizeCount;
        }
        return $this;
    }

    /**
     * 获取奖品总数
     * @throws \Exception
     */
    public function getPrizeTotalCount()
    {
        $count = 0;
        foreach ($this->prizeInstanceList as $prizeInstance) {
            if ($prizeInstance->prizeCount === null) {
                throw new \Exception("Not set prize {" . $prizeInstance->prizeType . "}'s count");
            } else {
                $count += $prizeInstance->prizeCount;
            }
        }
        return $count;
    }

    /**
     * 开始抽奖
     */
    public function normalStart()
    {
        try {
            if (empty($this->prizeInstanceList)) {
                throw new \Exception("Prizes Empty");
            }
            $dateProbability = $this->drawKernel->findDateProbability($this->useDateProbabilityLimit);
            if ($dateProbability !== false) {
                $this->totalProbability = $dateProbability;
            }
            if ($this->totalProbability === 0) {
                throw new \Exception("Lottery probability is 0");
            }
            $this->drawKernel->isInActivityDate($this->useActivityTimeLimit);
            $this->drawKernel->isInDateTime($this->useDateTimeLimit);
            if ($this->useTotalPrizeLimitFunction !== null) {
                $result = $this->drawKernel->isInAllPirzeCountLimit(
                    $this->getPrizeTotalCount(),
                    $this->useTotalPrizeLimitFunction
                );
                if ($result) {
                    throw new \Exception("Prize total count reach limit");
                }
            }

            $getPrize = $this->drawKernel->isInTotalProbability($this->totalProbability);
            if (!$getPrize) {
                throw new \Exception("UnLuck get no prize");
            }

            $prizeList = $this->drawKernel->prizesFilteStart($this->prizeInstanceList);
            if (empty($prizeList)) {
                throw new \Exception("Prizes Empty");
            }
            $prizeResult   = $this->drawKernel->startToGetAPrize(
                $prizeList,
                $this->redisInstance,
                $errorMessage
            );
            $this->message = $errorMessage;
            return $prizeResult;
        } catch (\Exception $exception) {
            $this->message = $exception->getMessage();
            return false;
        }
    }

    /**
     * 开始抽奖
     */
    public function prizePoolStart()
    {
        try {
            $dateProbability = $this->drawKernel->findDateProbability($this->useDateProbabilityLimit);
            if ($dateProbability !== false) {
                $this->totalProbability = $dateProbability;
            }
            if ($this->totalProbability === 0) {
                throw new \Exception("Lottery probability is 0");
            }
            $this->drawKernel->isInActivityDate($this->useActivityTimeLimit);
            $this->drawKernel->isInDateTime($this->useDateTimeLimit);
            if ($this->useTotalPrizeLimitFunction !== null) {
                $result = $this->drawKernel->isInAllPirzeCountLimit(
                    $this->totalPrizeCount,
                    $this->useTotalPrizeLimitFunction
                );
                if ($result) {
                    throw new \Exception("Prize total count reach limit");
                }
            }

            $getPrize = $this->drawKernel->isInTotalProbability($this->totalProbability);
            if (!$getPrize) {
                throw new \Exception("UnLuck get no prize");
            }

            $prizeUid = $this->drawKernel->pickAPrizeFromPool(
                $this->prizePoolRedisKey,
                $this->redisInstance,
                $errorMessage
            );
            if ($prizeUid === false) {
                $this->message = $errorMessage;
            }
            return $prizeUid;
        } catch (\Exception $exception) {
            $this->message = $exception->getMessage();
            return false;
        }
    }
}