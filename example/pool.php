<?php
ini_set('date.timezone', 'PRC');
include "../LuckDraw.php";
include "../DrawKernel.php";
include "../Prize.php";
include "../Redis.php";
include "../vendor/autoload.php";

/**
 * 前言
 * 奖品池随机抽取方式应该是最好的奖品抽取方式，但是当前方式不能设置一些奖品的附加条件，当前方式必须使用redis。
 * 你只需要把对应奖品uid唯一标识存入redis列表中作为奖品池，奖品不需要提前对应进行随机排序，因为当前功能会随机抽取一份奖品并返回其奖品uid
 */

$redisInstance = (new \LuckDraw\Redis())
    ->setConnection( // 设置连接参数(可选)
        "tcp",     // 协议
        "127.0.0.1", //  redis地址
        6379         //  redis端口
    )->setParams("")// 设置连接密码(可选)
    ->setDatabase(0);// 设置数据的数据库 0～15(可选)

$luckDraw = new \LuckDraw\LuckDraw();

$result = $luckDraw->setTotalProbability(20)// 设置总抽奖概率，0～100
->setRedisInstance($redisInstance)// 设置redis实例(必填)
->setPrizePoolRedisKey("testList")
    ->addActivityTimeLimit( // 设置活动可抽奖时间段列表，若时间格式或参数格式不符合要求会被自动过滤，若列表为空则跳过当前条件判断，如下(可选)
        [
            ["2019-05-10 00:00:00", "2020-01-10 00:00:00"],
            ["2019-05-11", "2020-01-22 14:00:22"],
            ["2020-01-10", "2020-08-27 14:34:00"]
        ]
    )->addDateTimeLimit( // 设置每天可抽奖时间段列表，若时间格式或参数格式不符合要求会被自动过滤，若列表为空则跳过当前条件判断，如下(可选)
        [
            ["00:00:00", "06:00:00"],
            ["05:00:00", "12:00:00"],
            ["13:00:00", "24:00:00"],
        ]
    )->addTotalPrizeLimit( // 设置奖品总量限制，若时间格式或参数格式不符合要求会被自动过滤，若列表为空则跳过当前条件判断，需设置总数和查询当前已抽奖品总数(可选)
        function () {
            return 19;
        },
        20
    )->addDateProbabilityLimit( // 设置中奖概率时间段变化列表，若时间格式或参数格式不符合要求会被自动过滤，若列表为空则跳过当前条件判断，如下(可选)
        [
            ["2019-05-10 00:00:00", "2020-01-10 00:00:00", 21],
            ["2019-05-11", "2020-01-22 14:00:22", 30],
            ["2020-01-10", "2020-08-27 14:34:00", 5],
            ["2020-05-10", "2020-09-10 14:34:00", 100],
            ["2020-09-10", "2020-09-20", 60],
        ])
    ->prizePoolStart();


if ($result === false) { // 未中奖，返回值为false
    var_dump($luckDraw->getResultMessage()); // 打印处理结果信息
} else {
    var_dump($result);  // 会在此参数返回奖品uid
}


