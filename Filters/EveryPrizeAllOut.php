<?php

namespace LuckDraw\Filters;

use LuckDraw\DrawKernel;
use LuckDraw\Exceptions\FilterParamsErrorException;
use LuckDraw\Exceptions\NotWinPrizeException;

class EveryPrizeAllOut
{
    protected $persionLimit = 0;
    protected $countHandler = null;
    protected $getPrizesCount = null;

    public function __construct($persionLimit, $countHandler)
    {
        $this->persionLimit = is_numeric($persionLimit) && $persionLimit > 0 ? $persionLimit : 0;
        $this->countHandler = ($countHandler instanceof \Closure) || (is_int($countHandler) && $countHandler >= 0) ? $countHandler : null;
    }

    /*
     * 获取个人中奖数
     */
    public function getUserPrizesCount(){
        return $this->getPrizesCount;
    }

    /**
     * 过滤器默认方法-检查每个人可中奖数量
     * $limit => 奖品总数
     * $countHandler => callable| 10 可以为callable参数返回当前所有已中的奖品数量，或直接给总数int
     */
    public function filter(DrawKernel $drawKernel)
    {
        if (!is_null($this->countHandler)) {
            if ($this->countHandler instanceof \Closure) {
                $count = call_user_func_array($this->countHandler, []);
            } else {
                $count = $this->countHandler;
            }
            if (!is_int($count) || $count < 0) {
                throw new FilterParamsErrorException();
            }
            $this->getPrizesCount = $count;
            if ($count >= $this->persionLimit) {
                throw new NotWinPrizeException();
            }
        }
    }
}