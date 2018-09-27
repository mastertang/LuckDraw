<?php

namespace LuckDraw;

use LuckDraw\Exceptions\NotWinPrizeException;
use LuckDraw\Filters\ActivityTime;
use LuckDraw\Filters\DateCountLimit;
use LuckDraw\Filters\DatePrizeProbability;
use LuckDraw\Filters\DateProbability;
use LuckDraw\Filters\DateTime;
use LuckDraw\Filters\EveryPrizeCountReach;
use LuckDraw\Filters\EveryPrizeLimit;
use LuckDraw\Filters\EveryPrizeAllOut;
use LuckDraw\Filters\TotalPrizeLimit;
use LuckDraw\Filters\WinIf;

class LuckDraw
{
    /*
     * 错误信息
     */
    protected $errorMessage = '';

    /*
     * 抽奖核心类
     */
    protected $drawKernelInstance = null;

    protected $useActivityTimeLimit         = null;
    protected $useDateCountLimit            = null;
    protected $useDatePrizeProbabilityLimit = null;
    protected $useDateProbabilityLimit      = null;
    protected $useDateTimeLimit             = null;
    protected $useEveryPrizeCountReachLimit = null;
    protected $useEveryPrizeLimit           = null;
    protected $useEveryPrizeAllOutLimit     = null;
    protected $useTotalPrizeLimit           = null;

    /*
     * 构造函数
     */
    public function __construct(DrawKernel $drawKernel)
    {
        $this->drawKernelInstance = $drawKernel;
    }

    /*
     * 获取错误信息
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /*
     * 创建抽奖类对象
     */
    public static function makeLottery(DrawKernel $drawKernelInstance)
    {
        return new LuckDraw($drawKernelInstance);
    }

    /*
     * 添加活动时间限制
     */
    public function addActivityTimeLimit(ActivityTime $activityTime)
    {
        $this->useActivityTimeLimit = $activityTime;
        return $this;
    }

    /*
     * 添加日期中奖数量限制
     */
    public function addDateCountLimit(DateCountLimit $dateCountLimit)
    {
        $this->useDateCountLimit = $dateCountLimit;
        return $this;
    }

    /*
     * 添加日期奖品概率设置
     */
    public function addDatePrizeProbabilityLimit(DatePrizeProbability $datePrizeProbability)
    {
        $this->useDatePrizeProbabilityLimit = $datePrizeProbability;
        return $this;
    }

    /*
     * 添加日期中奖概率设置
     */
    public function addDateProbabilityLimit(DateProbability $dateProbability)
    {
        $this->useDateProbabilityLimit = $dateProbability;
        return $this;
    }

    /*
     * 添加日期中奖数量限制
     */
    public function addDateTimeLimit(DateTime $dateTime)
    {
        $this->useDateTimeLimit = $dateTime;
        return $this;
    }

    /*
     * 添加奖品数量限制
     */
    public function addEveryPrizeCountReach(EveryPrizeCountReach $everyPrizeCountReach)
    {
        $this->useEveryPrizeCountReachLimit = $everyPrizeCountReach;
        return $this;
    }

    /*
     * 添加日期中奖数量限制
     */
    public function addEveryPrizeLimit(EveryPrizeLimit $everyPrizeLimit)
    {
        $this->useEveryPrizeLimit = $everyPrizeLimit;
        return $this;
    }

    /*
     * 添加日期中奖数量限制
     */
    public function addEveryPrizeAllOutLimit(EveryPrizeAllOut $everyPrizeAllOut)
    {
        $this->useEveryPrizeAllOutLimit = $everyPrizeAllOut;
        return $this;
    }

    /*
     * 添加奖品总数量限制
     */
    public function addTotalPrizeLimit(TotalPrizeLimit $totalPrizeLimit)
    {
        $this->useTotalPrizeLimit = $totalPrizeLimit;
        return $this;
    }

    /**
     * 开始抽奖
     */
    public function lottery()
    {
        try {
            if ($this->useActivityTimeLimit instanceof ActivityTime) {
                call_user_func_array([$this->useActivityTimeLimit, 'filter'], [$this->drawKernelInstance]);
            }
            if ($this->useDateTimeLimit instanceof DateTime) {
                call_user_func_array([$this->useDateTimeLimit, 'filter'], [$this->drawKernelInstance]);
            }
            if ($this->useTotalPrizeLimit instanceof TotalPrizeLimit) {
                call_user_func_array([$this->useTotalPrizeLimit, 'filter'], [$this->drawKernelInstance]);
            }
            if ($this->useEveryPrizeAllOutLimit instanceof EveryPrizeAllOut) {
                call_user_func_array([$this->useEveryPrizeAllOutLimit, 'filter'], [$this->drawKernelInstance]);
            }
            if ($this->useDateProbabilityLimit instanceof DateProbability) {
                call_user_func_array([$this->useActivuseDateProbabilityLimitityTimeLimit, 'filter'], [$this->drawKernelInstance]);
            }

            (new WinIf())->filter($this->drawKernelInstance);

            if ($this->useDatePrizeProbabilityLimit instanceof DatePrizeProbability) {
                call_user_func_array([$this->useDatePrizeProbabilityLimit, 'filter'], [$this->drawKernelInstance]);
            }

            if ($this->useDateCountLimit instanceof DateCountLimit) {
                call_user_func_array([$this->useDateCountLimit, 'filter'], [$this->drawKernelInstance]);
            }

            if ($this->useEveryPrizeCountReachLimit instanceof EveryPrizeCountReach) {
                call_user_func_array([$this->useEveryPrizeCountReachLimit, 'filter'], [$this->drawKernelInstance]);
            }

            if ($this->useEveryPrizeLimit instanceof EveryPrizeLimit) {
                call_user_func_array([$this->useEveryPrizeLimit, 'filter'], [$this->drawKernelInstance]);
            }

            $prizeSection       = $this->drawKernelInstance->getPrizeProbabilitySection();
            $consolationPrizeId = $this->drawKernelInstance->getConsolationPrizeId();
            if (empty($prizeSection)) {
                return is_null($consolationPrizeId) ? false : $consolationPrizeId;
            }
            $maxNumber      = end($prizeSection);
            $rand           = rand(1, $maxNumber);
            $currentPrizeId = null;
            foreach ($prizeSection as $prizeId => $probility) {
                if ($rand <= $probility) {
                    $currentPrizeId = $prizeId;
                    break;
                }
            }
            if (is_null($currentPrizeId) || !is_int($currentPrizeId) || $currentPrizeId < 0) {
                return is_null($consolationPrizeId) ? false : $consolationPrizeId;
            }
            return $currentPrizeId;
        } catch (\Exception $exception) {
            $this->errorMessage = $exception->getMessage();
            return is_null($consolationPrizeId) ? false : $consolationPrizeId;
        }
    }
}