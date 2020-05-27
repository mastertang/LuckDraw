<?php
ini_set('date.timezone', 'PRC');
include "../LuckDraw.php";
include "../DrawKernel.php";
include "../Prize.php";
include "../Redis.php";
include "../vendor/autoload.php";

/**
 * 前言
 * 如果抽奖要求有设置其他的特殊条件。例如需要判断库存数量等内容且不使用redis时，必须面临并发的导致奖品重复问题。这种时候应该在抽奖后在进行一次奖品数量判断，但是还是不能100%保证避免上述问题。
 * 所以一般来说还是推荐使用redis帮忙处理redis。
 * 另外，redis奖品的键值请仔细设置，避免修改已有项目的redis数据
 */

/**
 * 如若使用redis请创建redis实例，设置对应参数，所有参数几乎有默认值，请按实际环境配置
 */
$redisInstance = (new \LuckDraw\Redis())
    ->setConnection( // 设置连接参数(可选)
        "tcp",     // 协议
        "127.0.0.1", //  redis地址
        6379         //  redis端口
    )->setParams("")// 连接密码，无密码为空字符串(可选)
    ->setDatabase(0)// 设置数据的数据库 0～15(可选)
    ->setConnectionTimeOut("0.1")// 设置连接的超时时间，支持浮点数，0.1表示 0.1秒(可选)
    ->setRWTimeOut("0.1"); // 设置读写的超时时间，支持浮点数，0.1表示 0.1秒(可选)

$prize1 = (new \LuckDraw\Prize(
    "1",        // 例子 有两个奖品，奖品1的类型为"1"，奖品2的类型为"2"，所有奖品的类型都应该唯一，主要是方便判断是哪个奖品
    60,             // 当前奖品的默认中奖概率，>= 0 的正整数，如果没有设置其他概率条件，都以默认概率进行抽奖运算。例: 有两个奖品，奖品1概率为10，奖品2概率为20，那么在1～30中抽随机数，那么在区间1～10中则中奖奖品1，以此类推
    "prize1test"))// 当使用redis的时候必须设置，默认不使用redis(可选)
->setPrizeCount(                        // 设置当前奖品的数量限制(可选)
    20,                     // 默认总数，若无其他条件则使用当前总数
    function ($prizeType) {             // 获取当前奖品已送总数的可回调函数，程序会将当前奖品类型在参数$prizeType返回。若此参数格式错误或为空，这不判断当前条件
        return 10;
    },
    [                               // 特定日期总数变化条件列表。这里声明一下，若时间段有重叠，则以最一个符合要求的条件进行计算，下面是一个例子说明
        [
            "2020-04-10 00:00:00",  // 日期的起开始日期，若格式为 2020-09-10 则时间默认是 00:00:00
            "2020-09-30 12:00:00",  // 日期的起结束日期，若格式为 2020-09-10 则时间默认是 00:00:00
            5                       // 在上述时间段内，总数为5，而不是默认的20
        ],
        ["2020-05-10 00:00:00", "2020-06-30 12:00:00", 30]
    ]
)->setPrizeDateProbability(         // 设置当前奖品的概率变化条件列表(可选)
    [
        [
            "2020-04-10 00:00:00",  // 日期的起开始日期，若格式为 2020-09-10 则时间默认是 00:00:00
            "2020-09-30 12:00:00",  // 日期的起结束日期，若格式为 2020-09-10 则时间默认是 00:00:00
            20                      // 在上述时间段内，当前奖品的概率为20，而不是默认的60
        ],
        ["2020-05-10 00:00:00", "2020-06-30 12:00:00", 10]
    ]
)->setPrizeUserCount(                // 设置当前奖品每个用户所能抽取的数量(可选)
    10,
    function ($prizeType) {          // 获取当前奖品用户所获取数量的可回调函数，程序会将当前奖品类型在参数$prizeType返回。若此参数格式错误或为空，这不判断当前条件
        return 10;
    },
    [                               // 特定日期用户所有最大数量变化条件列表。这里声明一下，若时间段有重叠，则以最一个符合要求的条件进行计算，下面是一个例子说明
        [
            "2020-04-10 00:00:00",  // 日期的起开始日期，若格式为 2020-09-10 则时间默认是 00:00:00
            "2020-09-30 12:00:00",  // 日期的起结束日期，若格式为 2020-09-10 则时间默认是 00:00:00
            5                       // 在上述时间段内，总数为5，而不是默认的1
        ],
        ["2020-05-10 00:00:00", "2020-06-30 12:00:00", 20]
    ]
);

$prize2 = (new \LuckDraw\Prize("2", 20, "prize2test"))
    ->setPrizeCount(
        20,
        function ($prizeType) {
            return 10;
        }
    )->setPrizeUserCount(10);

$luckDraw = new \LuckDraw\LuckDraw();
$luckDraw->setPrizeInstance($prize1);
$luckDraw->setPrizeInstance($prize2);

$result = $luckDraw->setTotalProbability(50)// 设置总抽奖概率，0～100
->setRedisInstance($redisInstance)// 设置redis实例(可选)
->addActivityTimeLimit(              // 设置允许抽奖时间段,只要符合其中一个条件就能进行抽奖(可选)
    [
        [
            "2020-04-10 00:00:00",   // 日期的起开始日期，若格式为 2020-09-10 则时间默认是 00:00:00
            "2020-10-11 00:00:00"    // 日期的起结束日期，若格式为 2020-09-10 则时间默认是 00:00:00
        ]
    ]
)
    ->addDateTimeLimit(                  // 设置每天可抽奖时间段,只要符合其中一个条件就能进行抽奖(可选)
        [
            [
                "00:00:00",              // 开始时间段
                "24:00:00"               // 结束时间段
            ]
        ]
    )
    ->addDateProbabilityLimit(           // 设置总抽奖概率列表,这里声明一下，若时间段有重叠，则以最一个符合要求的条件进iz行计算(可选)
        [
            [
                "2020-04-10 00:00:00",  // 日期的起开始日期，若格式为 2020-09-10 则时间默认是 00:00:00
                "2020-09-30 12:00:00",  // 日期的起结束日期，若格式为 2020-09-10 则时间默认是 00:00:00
                100                     // 在上述时间段内，概率为20，而不是默认设置的总抽奖概率
            ]
        ]
    )
    ->addTotalPrizeLimit(               // 设置所有奖品总数限制条件
        function () {                   // 查询现已抽奖的奖品总数，设定的奖品总数是统计奖品实例里设置的总量
            return 20;
        }
    )
    ->normalStart();


if ($result === false) { // 未中奖，返回值为false
    var_dump($luckDraw->getResultMessage()); // 打印处理结果信息
} else if ($result instanceof \LuckDraw\Prize) { // 中奖会返回相应的奖品类实例
    var_dump("奖品UI:" . $result->prizeUid);  // 若设置redis，会在此参数返回奖品id
    var_dump("奖品类型:" . $result->prizeType); // 其次通过奖品类型判断奖品
}


