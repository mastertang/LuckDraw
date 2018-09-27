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

    /*
     * 奖品最高概率
     */
    protected $prizeMaxProbaility = 0;

    /*
     * 安慰奖id
     */
    protected $consolationPrizeId = null;

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

    /*
     * 设置安慰奖id
     */
    public function setConsolationPrizeId($prizeId)
    {
        if (is_numeric($prizeId)) {
            $prizeId = (int)$prizeId;
            if ($prizeId >= 0) {
                $this->consolationPrizeId = $prizeId;
            }
        }
        return $this;
    }

    /*
     * 获取安慰奖id
     */
    public function getConsolationPrizeId()
    {
        return $this->consolationPrizeId;
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
        if (is_numeric($probability)) {
            $this->probability = $probability <= 0 ? 0 : ($probability >= 100 ? 100 : $probability);
            $this->handleProbability();
        } else {
            $this->probability = 0;
        }
        return $this;
    }

    /**
     * 处理Raw 中奖概率
     */
    private function handleProbability()
    {
        if ($this->probability == 0) {
            $this->maxProbability = $this->probability;
        } else {
            $numberArray = explode('.', $this->probability);
            $integer     = isset($numberArray[0]) ? $numberArray[0] : 0;
            $decimal     = isset($numberArray[1]) ? $numberArray[1] : 0;
            if ((int)$decimal == 0) {
                $this->probability    = (int)$integer;
                $this->maxProbability = 100;
            } else {
                $decimalLength        = strlen($decimal);
                $this->probability    = (int)($this->probability * pow(10, $decimalLength));
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
            ksort($prizeProbability);
            if (isset($prizeProbability[0])) {
                $tempProbability = [];
                foreach ($prizeProbability as $prizeId => $probability) {
                    $tempProbability[$prizeId + 1] = $probability;
                }
                $prizeProbability = $tempProbability;
                unset($tempProbability);
            }
            $prizeProbability = array_combine(range(1, sizeof($prizeProbability)), array_values($prizeProbability));

            $longerDecimalLength = 0;
            foreach ($prizeProbability as $probability) {
                $numberArray = explode('.', $probability);
                $decimal     = (int)(isset($numberArray[1]) ? $numberArray[1] : 0);
                if ($decimal != 0 && strlen($decimal) > $longerDecimalLength) {
                    $longerDecimalLength = strlen($decimal);
                }
            }
            $product = pow(10, $longerDecimalLength);
            foreach ($prizeProbability as $prizeId => $probability) {
                $prizeProbability[$prizeId] = (int)($probability * $product);
            }
            $this->prizeProbability = $prizeProbability;
            $this->createPrizeProbabilitySection();
        } else {
            $this->prizeProbability = [];
            $this->createPrizeProbabilitySection();
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
            $section = [];
            foreach ($this->prizeProbability as $prizeId => $probabiltiy) {
                $section[$prizeId] = $probabiltiy + $this->prizeProbability[$prizeId - 1];
            }
            $this->prizeProbbilitySection = $section;
            $this->prizeMaxProbaility = end($section);
        }
        return $this;
    }
}