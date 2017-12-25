<?php
/**
 *
 * 奖品送出数量每天限制
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
    ->addFilter(\LuckDraw\Filters\WinIf::class)
    ->addFilter(\LuckDraw\Filters\DateCountLimit::class, [
        [
            "2017-12-20|2017-12-24" => [1 => 2, 2 => 10],
            "2017-12-25" => [1 => 4, 2 => 10]
        ],
        function ($prizeId) {
            return 10;
        }
    ])
    ->lottery((new \LuckDraw\DrawKernel())
        ->setProbability(90)
        ->setPrizeProbability(
            [1 => 10, 2 => 30, 3 => 5]
        )
    );
var_dump($prize);
