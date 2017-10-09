<?php
/**
 *
 * 检查某些奖品是否已经被送完从而改变当前用户可中奖奖品
 *
 */
include "../../LuckDraw.php";
include "../../DrawKernel.php";
include "../../Exeptions/NotWinPrizeException.php";
include "../../Exeptions/FilterParamsErrorException.php";
include "../../Filters/EveryPrizeCountReach.php";
include "../../Filters/WinIf.php";

$start = '2017-10-08 00:21:33';
$end = '2017-10-09 23:10:10';

$prize = (new \LuckDraw\LuckDraw())
    ->addFilter(\LuckDraw\Filters\EveryPrizeCountReach::class,
        [
            [1 => 3, 2 => 5, 3 => 2],
            function () {
                return [1 => 2, 2 => 1, 3 => 3];
            }
        ])
    ->addFilter(\LuckDraw\Filters\WinIf::class)
    ->lottery((new \LuckDraw\DrawKernel())
        ->setProbability(100)
        ->setPrizeProbability(
            [1 => 10, 2 => 30, 3 => 5]
        )
    );
var_dump($prize);
