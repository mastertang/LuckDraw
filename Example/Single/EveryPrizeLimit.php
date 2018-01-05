<?php
/**
 *
 * 检查当前用户某个奖品所中的数量是否已经达到上限要求
 *
 */
include "../../LuckDraw.php";
include "../../DrawKernel.php";
include "../../Exeptions/NotWinPrizeException.php";
include "../../Exeptions/FilterParamsErrorException.php";
include "../../Filters/EveryPrizeLimit.php";
include "../../Filters/WinIf.php";

$start = '2017-10-08 00:21:33';
$end = '2017-10-09 23:10:10';

$prize = (new \LuckDraw\LuckDraw())
    ->addFilter(\LuckDraw\Filters\EveryPrizeLimit::class,
        [[1 => 2, 2 => 3, 3 => 2],
            [1 => 1, 2 => 1, 3 => 1]])
    ->addFilter(\LuckDraw\Filters\WinIf::class)
    ->lottery((new \LuckDraw\DrawKernel())
        ->setProbability(30)
        ->setPrizeProbability(
            [1 => 10, 2 => 30, 3 => 5]
        )
    );
var_dump($prize);
