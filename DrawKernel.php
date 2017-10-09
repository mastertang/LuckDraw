<?php
namespace LuckDraw;
class DrawKernel
{
    /**
     * 中奖概率
     */
    protected $probability = 0;

    /**
     * 中奖概率最高值
     */
    protected $maxProbability = 0;

    /**
     * 奖品概率
     */
    protected $prizeProbability = [];

    /**
     * 奖品概率区间
     */
    protected $prizeProbbilitySection = [];

    /**
     * 当前时间戳
     */
    protected $nowStamp = 0;

    /**
     * 日期的年月日
     */
    protected $ymd = '';

    public function __construct()
    {
        $this->nowStamp = time();
        $this->ymd = date('Y-m-d', $this->nowStamp);
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
     * 设置中奖概率
     */
    public function setProbability($probability)
    {
        if (is_int($probability) || is_float($probability) || is_double($probability) && $probability <= 100) {
            $this->probability = $probability;
            $this->handleProbability();
        }
        return $this;
    }

    /**
     * 处理Raw 中奖概率
     */
    private function handleProbability()
    {
        if ($this->probability == 100) {
            $this->maxProbability = 100;
        } elseif ($this->probability == 0) {
            $this->maxProbability = 0;
        } else {
            $numberString = explode('.', $this->probability);
            if (sizeof($numberString) == 1) {
                $this->maxProbability = pow(10, strlen($numberString[0]));
            } else {
                $this->probability = (int)($this->probability * pow(10, strlen($numberString[1])));
                $this->maxProbability = pow(10, strlen($numberString[0]) + strlen($numberString[1]));
            }
        }
    }

    /**
     * 获取最高中奖概率
     */
    public function getMaxProbability()
    {
        return $this->maxProbability;
    }

    /**
     * 获取中奖概率
     */
    public function getProbability()
    {
        return $this->probability;
    }

    /**
     * 设置奖品概率
     */
    public function setPrizeProbability($prizeProbability)
    {
        if (is_array($prizeProbability) && !empty($prizeProbability)) {
            $this->prizeProbability = $prizeProbability;
            $this->createPrizeProbabilitySection();
        }
        return $this;
    }

    /**
     * 根据奖品概率设置
     */
    private function createPrizeProbabilitySection()
    {
        $maxLength = 0;
        foreach ($this->prizeProbability as $prize => $probability) {
            $tempExplore = explode('.', $probability);
            $tempLength = isset($tempExplore[1]) ? strlen($tempExplore[1]) : 0;
            if ($tempLength >= $maxLength) {
                $maxLength = $tempLength;
            }
        }
        $number = pow(10, $maxLength);
        foreach ($this->prizeProbability as $prize => $probability) {
            $this->prizeProbability[$prize] = $probability * $number;
        }
        $start = 0;
        $this->prizeProbbilitySection = [];
        foreach ($this->prizeProbability as $prize => $probability) {
            if ($start == 1) {
                $this->prizeProbbilitySection[$prize] = [$start + 1, $probability];
                $start = $probability;
            } else {
                $this->prizeProbbilitySection[$prize] = [$start + 1, $probability + $start];
                $start += $probability;
            }
        }
    }

    /**
     * 获取奖品概率
     */
    public function getPrizeProbability()
    {
        return $this->prizeProbability;
    }

    /**
     * 获取当前奖品概率区间
     */
    public function getPrizeProbabilitySection()
    {
        return $this->prizeProbbilitySection;
    }
}