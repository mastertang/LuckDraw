<?php
/**
 *
 * 定期改变奖品中间概率
 *
 */
include "../../LuckDraw.php";
include "../../DrawKernel.php";
include "../../Exeptions/NotWinPrizeException.php";
include "../../Exeptions/TimeSectionDrawRefuseException.php";
include "../../Filters/DatePrizeProbability.php";
include "../../Filters/WinIf.php";

$start = '2017-10-08 00:21:33';
$end = '2017-10-09 23:10:10';

$prize = (new \LuckDraw\LuckDraw())
    ->addFilter(\LuckDraw\Filters\DatePrizeProbability::class,
        [
            '2017-10-19' =>
                [
                    [['09:10:02', '10:22:31'], [1 => 5, 2 => 10, 3 => 40]],
                    [['11:01:36', '11:06:40'], [1 => 5, 2 => 10, 3 => 40]],
                    [['11:30:10', '11:31:59'], [1 => 5, 2 => 10, 3 => 40]],
                    [['12:20:01', '14:55:30'], [1 => 5, 2 => 10, 3 => 90]]
                ]
        ], true)
    ->addFilter(\LuckDraw\Filters\WinIf::class)
    ->lottery((new \LuckDraw\DrawKernel())
        ->setProbability(30)
        ->setPrizeProbability(
            [1 => 10, 2 => 30, 3 => 5]
        )
    );
var_dump($prize);
