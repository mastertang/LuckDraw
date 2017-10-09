<?php
/**
 *
 * 检查是否所有奖品都已经送完
 *
 */
include "../../LuckDraw.php";
include "../../DrawKernel.php";
include "../../Exeptions/NotWinPrizeException.php";
include "../../Exeptions/FilterParamsErrorException.php";
include "../../Filters/PrizeAllOut.php";
include "../../Filters/WinIf.php";

$start = '2017-10-08 00:21:33';
$end = '2017-10-09 23:10:10';

$prize = (new \LuckDraw\LuckDraw())
    ->addFilter(\LuckDraw\Filters\PrizeAllOut::class, [10, function(){}])
    ->addFilter(\LuckDraw\Filters\WinIf::class)
    ->lottery((new \LuckDraw\DrawKernel())
        ->setProbability(30)
        ->setPrizeProbability(
            [1 => 10, 2 => 30, 3 => 5]
        )
    );
var_dump($prize);
