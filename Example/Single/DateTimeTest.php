<?php
/**
 *
 * 设置某天某个时间段是否可以中间
 *
 */
include "../../LuckDraw.php";
include "../../DrawKernel.php";
include "../../Exeptions/NotWinPrizeException.php";
include "../../Exeptions/TimeSectionDrawRefuseException.php";
include "../../Filters/DateTime.php";
include "../../Filters/WinIf.php";

$start = '2017-10-08 00:21:33';
$end = '2017-10-09 23:10:10';

$prize = (new \LuckDraw\LuckDraw())
    ->addFilter(\LuckDraw\Filters\DateTime::class,
        [
            '2017-10-09' =>
                [
                    ['09:10:02', '10:22:31'],
                    ['11:01:36', '11:06:40'],
                    ['11:30:10', '11:31:59'],
                    ['13:20:01', '14:55:30']
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
