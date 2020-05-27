<?php

namespace LuckDraw;

/**
 * Class DrawKernel
 * @package LuckDraw
 */
class DrawKernel
{
    /**
     * 当前时间戳
     */
    protected $nowStamp = 0;

    /**
     * 日期的年月日
     */
    protected $ymd = '';

    /*
     * 构造函数
     */
    public function __construct()
    {
        $this->nowStamp = time();
        $this->ymd      = date('Y-m-d', $this->nowStamp);
    }

    /**
     * 获取当前时间戳
     */
    public function getNowStamp()
    {
        return $this->nowStamp;
    }

    /**
     * 获取当前日期的年月日
     */
    public function getYmd()
    {
        return $this->ymd;
    }

    /**
     * 是否在活动出奖日期内
     *
     * @param $activityDateList
     * @return bool
     * @throws \Exception
     */
    public function isInActivityDate($activityDateList)
    {
        if (empty($activityDateList)) {
            return true;
        }
        foreach ($activityDateList as $activityDate) {
            if ($this->nowStamp >= $activityDate[0] && $this->nowStamp <= $activityDate[1]) {
                return true;
            }
        }
        throw new \Exception("Not in ActivityDate");
    }

    /**
     * 是否在每日活动时间段
     *
     * @param $dateTimeList
     * @return bool
     * @throws \Exception
     */
    public function isInDateTime($dateTimeList)
    {
        if (empty($dateTimeList)) {
            return true;
        }
        foreach ($dateTimeList as $dateTime) {
            if ($this->nowStamp >= $dateTime[0] && $this->nowStamp <= $dateTime[1]) {
                return true;
            }
        }
        throw new \Exception("Not in dateTime");
    }

    /**
     * 查找日期设定的统一概率
     *
     * @param $dateProbabilityList
     * @return bool|null
     */
    public function findDateProbability($dateProbabilityList)
    {
        if (empty($dateProbabilityList)) {
            return false;
        }
        $inDateProbability = null;
        $timeSection       = 0;
        foreach ($dateProbabilityList as $dateProbability) {
            if ($this->nowStamp >= $dateProbability[0]
                && $this->nowStamp <= $dateProbability[1]) {
                $tempTimeSection = $dateProbability[1] - $dateProbability[0];
                if ($timeSection === 0 || $tempTimeSection <= $timeSection) {
                    $timeSection       = $tempTimeSection;
                    $inDateProbability = $dateProbability;
                }
            }
        }
        if ($inDateProbability === null) {
            return false;
        }
        return $inDateProbability[2];
    }

    /**
     * 是否超过总奖品数量限制
     *
     * @param $totalCount
     * @param $totalPrizeCountFunction
     * @return bool
     */
    public function isInAllPirzeCountLimit($totalCount, $totalPrizeCountFunction)
    {
        if (is_callable($totalPrizeCountFunction) && is_numeric($totalCount) && $totalCount >= 0) {
            $nowCount = call_user_func_array($totalPrizeCountFunction, []);
            if (!is_numeric($nowCount)) {
                return false;
            } else {
                return $totalCount > $nowCount ? false : true;
            }
        }
        return true;
    }

    /**
     * 是否中奖
     *
     * @param $totalProbability
     * @return bool
     */
    public function isInTotalProbability($totalProbability)
    {
        $rand = rand(1, 100);
        if ($rand <= $totalProbability) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 开始奖品过滤
     *
     * @param $prizeList
     * @return array
     */
    public function prizesFilteStart($prizeList)
    {
        if (empty($prizeList)) {
            return [];
        }
        $prizeTypeList = [];
        foreach ($prizeList as $key => $prize) {
            if ($prize instanceof Prize) {
                if (empty($prize->prizeType)
                    || $prize->prizeDefaultProbability === 0
                    || $prize->prizeCount === 0
                    || $prize->prizeUserCount === 0) {
                    unset($prizeList[$key]);
                    continue;
                }
                if ($prize->prizeCount !== null && $prize->prizeCountFunction !== null) {
                    $tempCount = call_user_func_array($prize->prizeCountFunction, [$prize->prizeType]);
                    if (!is_numeric($tempCount) || $tempCount < 0 || $tempCount >= $prize->prizeCount) {
                        unset($prizeList[$key]);
                        continue;
                    }
                }
                if ($prize->prizeUserCount !== null && $prize->prizeUserCount !== null) {
                    $tempCount = call_user_func_array($prize->prizeUserCountFunction, [$prize->prizeUserCount]);
                    if (!is_numeric($tempCount) || $tempCount < 0 || $tempCount >= $prize->prizeUserCount) {
                        unset($prizeList[$key]);
                        continue;
                    }
                }
                $prizeTypeList[$prize->prizeType][] = $key;
            } else {
                unset($prizeList[$key]);
            }
        }
        $index = 0;
        foreach ($prizeTypeList as $prizeTypeArray) {
            if (sizeof($prizeTypeArray) > 1) {
                $index = 0;
                foreach ($prizeTypeArray as $prizeKey) {
                    if ($index > 0) {
                        unset($prizeList[$prizeKey]);
                    }
                    $index++;
                }
            }
        }
        $prizeList = array_values($prizeList);
        return $prizeList;
    }

    /**
     * 开始抽奖
     *
     * @param $prizeInstanceList
     * @param $redisInstance
     * @param string $errorMessage
     * @return bool|null
     */
    public function startToGetAPrize($prizeInstanceList, $redisInstance, &$errorMessage = "")
    {
        if ($redisInstance instanceof Redis) {
            foreach ($prizeInstanceList as $prize) {
                if (empty($prize->redisListKey)) {
                    $errorMessage = "Prize {" . $prize->prizeType . "}'s redis key is empty";
                    return false;
                }
            }
        }
        $probabilitySection = [];
        $lastProbability    = 1;
        $maxProbability     = 0;
        foreach ($prizeInstanceList as $prizeInstance) {
            $maxProbability       = $prizeInstance->prizeDefaultProbability + $lastProbability - 1;
            $probabilitySection[] = [
                $lastProbability,
                $maxProbability,
                $prizeInstance
            ];
            $lastProbability      += $prizeInstance->prizeDefaultProbability;
        }

        unset($prizeInstanceList);
        $prize = false;
        if ($redisInstance instanceof Redis) {
            while (true) {
                try {
                    $prizeInstance = null;
                    $prizeKey      = null;
                    $tempRand      = rand(1, $maxProbability);

                    foreach ($probabilitySection as $key => $section) {
                        if ($tempRand >= $section[0] && $tempRand <= $section[1]) {
                            $prizeKey      = $key;
                            $prizeInstance = $section[2];
                            break;
                        }
                    }

                    if ($prizeInstance !== null && $prizeKey !== null) {
                        $redisClient = $redisInstance->createRedisClient();
                        if ($redisClient === false) {
                            $errorMessage = $redisInstance->errorMessage;
                            break;
                        }
                        $tempPrizeUid = $redisClient->rpop($prizeInstance->redisListKey);
                        $redisClient->quit();
                        if ($tempPrizeUid === false || is_null($tempPrizeUid)) {
                            unset($probabilitySection[$prizeKey]);
                            $probabilitySection = array_values($probabilitySection);
                        } else {
                            $prizeInstance->prizeUid = $tempPrizeUid;
                            $prize                   = $prizeInstance;
                            break;
                        }
                    }
                    if (empty($probabilitySection)) {
                        $errorMessage = "Prize Empty";
                        break;
                    }
                    $lastProbability = 1;
                    foreach ($probabilitySection as $key => $section) {
                        $maxProbability              = $section[2]->prizeDefaultProbability + $lastProbability - 1;
                        $probabilitySection[$key][0] = $lastProbability;
                        $probabilitySection[$key][1] = $maxProbability;
                        $lastProbability             += $section[2]->prizeDefaultProbability;
                    }
                } catch (\Exception $exception) {
                    $errorMessage = $exception->getMessage();
                    break;
                }
            }
        } else {
            $rand = rand(1, $maxProbability);
            foreach ($probabilitySection as $prizeType => $section) {
                if ($rand >= $section[0] && $rand <= $section[1]) {
                    $prize = $section[2];
                    break;
                }
            }
        }
        return $prize;
    }

    /**
     *
     * @param $prizePoolRedisKey
     * @param $redisInstance
     * @return bool
     */
    public function pickAPrizeFromPool($prizePoolRedisKey, $redisInstance, &$errorMessage)
    {
        $luaString = '
            local llen = redis.call("llen",KEYS[1]);
            if llen <= 0
            then
                return false;
            end
            math.randomseed(ARGV[1])
            local index = math.random(llen) - 1;
            local value = redis.call("lindex",KEYS[1],index);
            if value ~= nil
            then
              redis.call("lrem",KEYS[1],index,value);
            end
            return value;  
        ';
        $prizeUid  = false;
        if ($redisInstance instanceof Redis) {
            $redisClient = $redisInstance->createRedisClient();
            if ($redisClient === false) {
                $errorMessage = $redisInstance->errorMessage;
            } else {
                $result = $redisClient->eval($luaString, 1, $prizePoolRedisKey, rand(0, 99999999));
                if ($result === null) {
                    $errorMessage = "Prize pool empty";
                } else {
                    $prizeUid = $result;
                }
            }
        }
        return $prizeUid;
    }
}