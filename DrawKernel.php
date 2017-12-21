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
     * 设置中奖概率
     */
    public function setProbability($probability)
    {
        if (!is_string($probability) && is_numeric($probability)) {
            if ($probability >= 100) {
                $this->probability = 100;
            } elseif ($probability <= 0) {
                $this->probability = 0;
            } else {
                $this->probability = $probability;
            }
            $this->handleProbability();
        }
        return $this;
    }

    /**
     * 处理Raw 中奖概率
     */
    private function handleProbability()
    {
        if (in_array($this->probability, [100, 0])) {
            $this->maxProbability = $this->probability;
        } else {
            $numberArray = explode('.', $this->probability);
            $integer = $numberArray[0];
            $decimal = $numberArray[1];
            if ((int)$decimal == 0) {
                $this->probability = $this->maxProbability = (int)$integer;
            } else {
                $decimalLength = strlen($decimal);
                $this->probability = (int)($this->probability * pow(10, $decimalLength));
                $this->maxProbability = pow(10, strlen($integer) + $decimalLength);
            }
        }
    }

    /**
     * 设置奖品概率
     */
    public function setPrizeProbability($prizeProbability)
    {
        if (is_array($prizeProbability) && !empty($prizeProbability)) {
            if (array_sum($prizeProbability) == 100) {
                $keys = array_keys($prizeProbability);
                $keysSize = sizeof($keys);
                $correct = true;
                for ($i = 0; $i < $keysSize; $i++) {
                    if (is_string($keys[$i]) || !is_int($keys[$i]) || $keys[$i] < 0) {
                        $correct = false;
                        break;
                    }
                    if (($keys[$i] + 1) != $keys[$i + 1]) {
                        $correct = false;
                    }
                }
                if ($correct) {
                    if ($keys[0] == 0) {
                        $newProbability = [];
                        foreach ($prizeProbability as $key => $probability) {
                            $newProbability[$key + 1] = $probability;
                        }
                        $prizeProbability = $newProbability;
                    }
                    $this->prizeProbability = $prizeProbability;
                    $this->createPrizeProbabilitySection();
                }
            }
        }
        return $this;
    }

    /**
     * 根据奖品概率设置
     */
    private function createPrizeProbabilitySection()
    {
        if (empty($this->prizeProbability)) {
            $this->prizeProbbilitySection = [];
        } else {
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
                if($probability != 0){
                    if ($start == 0) {
                        $this->prizeProbbilitySection[$prize] = [$start + 1, $probability];
                        $start = $probability;
                    } else {
                        $this->prizeProbbilitySection[$prize] = [$start + 1, $probability + $start];
                        $start += $probability;
                    }
                }
            }
        }
    }
}