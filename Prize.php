<?php

namespace LuckDraw;

/**
 * Class Prize
 * @package LuckDraw
 */
class Prize
{
    /**
     * @var string 奖品类型，对应区别不同奖品种类
     */
    public $prizeType = "";

    /**
     * @var string 奖品uid，唯一标识
     */
    public $prizeUid = "";

    /**
     * @var string redis奖品列表键名
     */
    public $redisListKey = "";

    /**
     * @var string 奖品默认抽中概率
     */
    public $prizeDefaultProbability = 0;

    /**
     * @var null 每个用户抽奖内容
     */
    public $prizeUserCount = null;

    /**
     * @var null 获取用户奖品数量的函数
     */
    public $prizeUserCountFunction = null;

    /**
     * @var null 当前奖品总数
     */
    public $prizeCount = null;

    /**
     * @var null 获取当前奖品总数数量函数
     */
    public $prizeCountFunction = null;

    /*
     * 构造函数
     */
    public function __construct($prizeType, $defaultPrizeProbability, $redisListkey = "")
    {
        $this->prizeType               = $prizeType;
        $this->prizeDefaultProbability = $defaultPrizeProbability;
        $this->redisListKey            = $redisListkey;
    }

    /**
     * 设置奖品的唯一标识
     *
     * @param $prizeUid
     * @return $this
     */
    public function setPrizeUid($prizeUid)
    {
        $this->prizeUid = $prizeUid;
        return $this;
    }

    /**
     * 设置奖品日期概率列表
     *
     * @param $dateProbabilityList
     * @return $this
     */
    public function setPrizeDateProbability($dateProbabilityList)
    {
        if (is_array($dateProbabilityList) || !empty($dateProbabilityList)) {
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
                        if (!is_numeric($dateProbability[2]) || $dateProbability[2] < 0) {
                            $dateProbability[2] = 0;
                        }
                        $dateProbabilityList[$key] = [
                            $startStamp,
                            $endStamp,
                            (int)$dateProbability[2]
                        ];
                    }
                }
            }
            $inDateProbability = null;
            $timeSection       = 0;
            $nowStamp          = time();
            foreach ($dateProbabilityList as $dateProbability) {
                if ($nowStamp >= $dateProbability[0]
                    && $nowStamp <= $dateProbability[1]) {
                    $tempTimeSection = $dateProbability[1] - $dateProbability[0];
                    if ($timeSection === 0 || $tempTimeSection <= $timeSection) {
                        $timeSection       = $tempTimeSection;
                        $inDateProbability = $dateProbability;
                    }
                }
            }
            if ($inDateProbability !== null) {
                $this->prizeDefaultProbability = $inDateProbability[2];
            }
        }
        return $this;
    }

    /**
     * 设置每个用户可抽当前奖品数量
     *
     * @param null $defaultCount
     * @param null $countFunction
     * @param array $dateUserCountList
     * @return $this
     */
    public function setPrizeUserCount(
        $defaultCount = null,
        $countFunction = null,
        $dateUserCountList = []
    )
    {
        if ($defaultCount !== null) {
            if (!is_numeric($defaultCount) || $defaultCount < 0) {
                $defaultCount = null;
            }
        }
        if (!is_array($dateUserCountList)) {
            $dateUserCountList = [];
        }
        if (($defaultCount !== null || !empty($dateUserCountList)) && !is_callable($countFunction)) {
            return $this;
        }
        $this->prizeUserCount         = $defaultCount;
        $this->prizeUserCountFunction = $countFunction;
        if (!empty($dateUserCountList)) {
            foreach ($dateUserCountList as $key => $userCount) {
                if (!is_array($userCount)
                    || sizeof($userCount) != 3
                    || !isset($userCount[0], $userCount[1], $userCount[2])
                    || empty($userCount[0]) || empty($userCount[1])
                    || !is_numeric($userCount[2])
                    || $userCount[2] < 0) {
                    unset($dateUserCountList[$key]);
                } else {
                    $startStamp = strtotime($userCount[0]);
                    $endStamp   = strtotime($userCount[1]);
                    if (empty($startStamp) || empty($endStamp) || $endStamp <= $startStamp) {
                        unset($dateUserCountList[$key]);
                    } else {
                        if (!is_numeric($userCount[2]) || $userCount[2] < 0) {
                            $userCount[2] = 0;
                        }
                        $dateUserCountList[$key] = [
                            $startStamp,
                            $endStamp,
                            (int)$userCount[2]
                        ];
                    }
                }
            }
            $inDateProbability = null;
            $timeSection       = 0;
            $nowStamp          = time();
            foreach ($dateUserCountList as $userCount) {
                if ($nowStamp >= $userCount[0]
                    && $nowStamp <= $userCount[1]) {
                    $tempTimeSection = $userCount[1] - $userCount[0];
                    if ($timeSection === 0 || $tempTimeSection <= $timeSection) {
                        $timeSection       = $tempTimeSection;
                        $inDateProbability = $userCount;
                    }
                }
            }
            if ($inDateProbability !== null) {
                $this->prizeUserCount = $inDateProbability[2];
            }
        }
        return $this;
    }

    /**
     * 设置当前奖品总数量
     *
     * @param null $defaultCount
     * @param null $countFunction
     * @param array $dateCountList
     * @return $this
     */
    public function setPrizeCount(
        $defaultCount = null,
        $countFunction = null,
        $dateCountList = []
    )
    {
        if ($defaultCount !== null) {
            if (!is_numeric($defaultCount) || $defaultCount < 0) {
                $defaultCount = null;
            }
        }
        if (!is_array($dateCountList)) {
            $dateCountList = [];
        }
        if (($defaultCount !== null || !empty($dateCountList)) && !is_callable($countFunction)) {
            return $this;
        }
        $this->prizeCount         = $defaultCount;
        $this->prizeCountFunction = $countFunction;
        if (!empty($dateCountList)) {
            foreach ($dateCountList as $key => $userCount) {
                if (!is_array($userCount)
                    || sizeof($userCount) != 3
                    || !isset($userCount[0], $userCount[1], $userCount[2])
                    || empty($userCount[0]) || empty($userCount[1])
                    || !is_numeric($userCount[2])
                    || $userCount[2] < 0) {
                    unset($dateCountList[$key]);
                } else {
                    $startStamp = strtotime($userCount[0]);
                    $endStamp   = strtotime($userCount[1]);
                    if (empty($startStamp) || empty($endStamp) || $endStamp <= $startStamp) {
                        unset($dateCountList[$key]);
                    } else {
                        if (!is_numeric($userCount[2]) || $userCount[2] < 0) {
                            $userCount[2] = 0;
                        }
                        $dateCountList[$key] = [
                            $startStamp,
                            $endStamp,
                            (int)$userCount[2]
                        ];
                    }
                }
            }
            $inDateProbability = null;
            $timeSection       = 0;
            $nowStamp          = time();
            foreach ($dateCountList as $userCount) {
                if ($nowStamp >= $userCount[0]
                    && $nowStamp <= $userCount[1]) {
                    $tempTimeSection = $userCount[1] - $userCount[0];
                    if ($timeSection === 0 || $tempTimeSection <= $timeSection) {
                        $timeSection       = $tempTimeSection;
                        $inDateProbability = $userCount;
                    }
                }
            }
            if ($inDateProbability !== null) {
                $this->prizeCount = $inDateProbability[2];
            }
        }
        return $this;
    }
}