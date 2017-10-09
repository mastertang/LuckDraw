<?php
namespace LuckDraw\Filters;

use LuckDraw\DrawKernel;
use LuckDraw\Exceptions\FilterParamsErrorException;
use LuckDraw\Exceptions\TimeSectionDrawRefuseException;

class DateTime
{
    /**
     * 过滤器默认方法-检查当前是否处于某个日期不可抽奖时间
     * $start => '2017-02-19'
     * $end => '2017-069-20'
     */
    public function filter(DrawKernel $drawKernel, $dateLimit)
    {
        if (!empty($dateLimit)) {
            if (!is_array($dateLimit)) {
                throw new FilterParamsErrorException();
            }
            $nowStamp = $drawKernel->getNowStamp();
            $ymd = $drawKernel->getYmd();
            if (isset($dateLimit[$ymd])) {
                $dateInfo = $dateLimit[$ymd];
                $draw = false;
                foreach ($dateInfo as $section) {
                    $tempStart = strtotime($ymd . ' ' . $section[0]);
                    $tempEnd = strtotime($ymd . ' ' . $section[1]);
                    if ($nowStamp <= $tempEnd && $nowStamp >= $tempStart) {
                        $draw = true;
                        break;
                    }
                }
                if(!$draw){
                    throw new TimeSectionDrawRefuseException();
                }
            }
        }
    }
}