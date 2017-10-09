<?php
/**
 *
 * 抽奖活动日期限制
 *
 */
include "../../LuckDraw.php";
include "../../DrawKernel.php";
include "../../Exeptions/NotWinPrizeException.php";
include "../../Exeptions/ActivityOutException.php";
include "../../Filters/ActivityTime.php";
include "../../Filters/WinIf.php";

$start = '2017-10-08 00:21:33';
$end = '2017-10-09 23:10:10';

$prize = (new \LuckDraw\LuckDraw())
    ->addFilter(\LuckDraw\Filters\ActivityTime::class, [$start, $end])
    ->addFilter(\LuckDraw\Filters\WinIf::class)
    ->lottery((new \LuckDraw\DrawKernel())
        ->setProbability(90)
        ->setPrizeProbability(
            [1 => 10, 2 => 30, 3 => 5]
        )
    );
var_dump($prize);
