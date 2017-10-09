<?php
namespace LuckDraw;

use LuckDraw\Exceptions\NotWinPrizeException;

class LuckDraw
{
    /**
     * 默认过滤器方法名
     */
    protected $defaultFilterMethod = 'filter';

    /**
     * 过滤器集合
     */
    protected $filters = [];

    /**
     * 设置默认过滤器方法名
     */
    public function setDefaultFilterMethod($method)
    {
        if(is_string($method)){
            $this->defaultFilterMethod = $method;
        }
        return $this;
    }

    /**
     * 添加过滤器
     */
    public function addFilter($filter, $params = [], $package = false)
    {
        if ($package) {
            $params = [$params];
        } else {
            is_array($params) or $params = [$params];
        }
        if (is_callable($filter)) {
            $this->filters[] = ['c' => $filter, 'p' => $params];
        } elseif (is_string($filter) && !empty($filter)) {
            $callable = [new $filter(), $this->defaultFilterMethod];
            if (is_callable($callable)) {
                $this->filters[] = ['c' => [new $filter(), $this->defaultFilterMethod], 'p' => $params];
            }
        }
        return $this;
    }

    /**
     * 清除所有过滤器
     */
    public function clearFilters()
    {
        $this->filters = [];
    }

    /**
     * 开始抽奖
     */
    public function lottery(DrawKernel $luckDraw)
    {
        foreach ($this->filters as $filter) {
            $callAble = $filter['c'];
            $params = $filter['p'];
            array_unshift($params, $luckDraw);
            call_user_func_array($callAble, $params);
        }
        $prizeSection = $luckDraw->getPrizeProbabilitySection();
        $maxNumber = end($prizeSection);
        $maxNumber = $maxNumber[1];
        $rand = rand(1, $maxNumber);
        $prize = 0;
        foreach ($prizeSection as $prizeId => $section) {
            if ($rand >= $section[0] && $rand <= $section[1]) {
                $prize = $prizeId;
                break;
            }
        }
        if ($prize == 0 || !is_int($prize) || $prize < 0) {
            throw new NotWinPrizeException();
        }
        return $prize;
    }
}