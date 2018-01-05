#抽奖模块 v1.2.0

#### 使用说明
* 所有的例子在 Example文件夹里，也有用途说明
#### 重点
* 重点在于 filter 过滤的使用，用户可以自由添加自己的过滤器或直接使用包里已经编写好的过滤器。
  对于特殊的要求则需要用户自己添加过滤器。过滤器必须是callable的，或是类名，如果是类名，其必
  须定义了规定好的方法，程序会自动调用其方法。当然你也可以直接或调用函数setDefaultFilterMethod()
  修改默认的方法名。

#### 服务
1) 更新1.1.0版本后，新添加服务类，用户只需调用此类的 "startService()" 方法并传入配置参数就可返还抽奖结果。
2) 程序运行所产生的所有异常都会被捕捉并保存,可以调用方发 "getException()" 获取异常实例。
3) 程序以数字 1 ~ 无穷 作为奖品等级标识，不中奖返回 NULL。
4) 已定义的过滤器说明 :  
```
   1) 过滤器ID常量
      WIN_IF_FILTER  //是否只能中一次奖
      DATE_TIME_FILTER //某个时间时间段可以进行抽奖
      ACTIVITY_TIME_FILTER //活动周期
      PRIZE_ALL_OUT_FILTER //检查所有奖品是否都送完
      DATE_PROBABILITY_FILTER //设置每天不同时间段的中奖概率
      DATE_COUNT_LIMIT_FILTER //检查每天奖品送出数量限制
      EVERY_PRIZE_LIMIT_FILTER //检查每个用户某个奖品是否已达可中数量
      TOTAL_PRIZE_LIMIT_FILTER //检查每个用户总中奖数量是否已达限制
      DATE_PRIZE_PROBABILITY_FILTER //设置每天不同时间段的每个奖品的中奖概率
      EVERY_PRIZE_COUNT_REACH_FILTER //检查每个奖品已中数量是否已达限制
    
   2) 配置参数说明   
      1.必须参数
        {
            //中奖概率
            "probability" => 0~100
            //每个奖品的中奖概率，总和100%,key从 1 开始
            "prizeProbability" => [
                1 => 10,
                2 => 20,
                3 => 70
            ]
        }
      2.选项参数
        {
            //保存要使用的filter的id
            "filters"=>[
                DATE_TIME_FILTER,
                DATE_PROBABILITY_FILTER,
                TOTAL_PRIZE_LIMIT_FILTER
            ]
        }
      3.过滤器参数
        {
            //DATE_TIME_FILTER
            "dateTime" => [
                "日期" => [[时间段1开始时间,时间段2结束时间],[时间段2开始时间,时间段2结束时间]......]   
                "2017-10-09" =>[["09:10:02", "10:22:31"],["11:01:36", "11:06:40"]]
              ],  
            
            //ACTIVITY_TIME_FILTER
            "activityTime" => ["2017-02-10 10:20:30","2017-10-22 09:23:10"] // [开始日期,结束日期],
            
            //PRIZE_ALL_OUT_FILTER
            "prizeAll" => [
                "limit" => 10,//总奖品数有10个
                "nowCount" => 现在中奖的数量,可以是数值或匿名函数,匿名函数必须返回数值
            ],
            
            //DATE_PROBABILITY_FILTER
            "dateProbability" => [
               "2017-10-09" =>
                   [
                       [[时间段],中奖概率],
                       [["09:10:02", "10:22:31"],10],
                       [["11:01:36", "11:06:40"],20]
                   ],
               "2017-10-10" => [......],
               ......
            ],
            
            //EVERY_PRIZE_LIMIT_FILTER
            "everyPrize" => [
                "limit" => [1 => 2, 2 => 3, 3 => 2],//限制中奖数量,[1等奖=>2个,2等奖=>3个,3等奖=>2个]
                "nowCount" => [1 => 1, 2 => 1, 3 => 1]//限制各等奖的中奖数量,可以是数组或匿名函数,匿名函数返回的数组格式必须与例子相同
            ],
            
            //TOTAL_PRIZE_LIMIT_FILTER
            "totalPrize" => [
                "limit" => 10,//每个用户只能中10个奖
                "nowCount" => 9,//现在当前用户中奖总数,可以是数值或匿名函数,匿名函数返回的必须是数值
            ],
            
            //DATE_PRIZE_PROBABILITY_FILTER
            "datePrizeProbability"=>[
                "2017-10-19" =>
                    [
                        [[时间段],[1等奖=>5中奖概率,2等奖=>10中奖概率,3等奖=>40中奖概率]]
                        [["09:10:02", "10:22:31"], [1 => 5, 2 => 10, 3 => 40]],
                        [["11:01:36", "11:06:40"], [1 => 5, 2 => 10, 3 => 40]]
                    ],
               "2017-10-20" =>
                   [
                       [["09:10:02", "10:22:31"], [1 => 5, 2 => 10, 3 => 40]],
                       ......
                   ] ,
               ......    
            ],
            
            //EVERY_PRIZE_COUNT_REACH_FILTER
            "prizeCount" => [
                
                "limit" => [1 => 3, 2 => 5, 3 => 2],//[1等奖=>1等奖总数量,2等奖=>2等奖总数量,3等奖=>3等奖总数量]
                "nowCount" => [1 => 3, 2 => 5, 3 => 2]//可以是数组或匿名函数,匿名函数返回的数组格式必须与例子相同
            ],
            
            //DATE_COUNT_LIMIT_FILTER
            "dateCount" => [
                "limit"=>[
                    "日期" => [1等奖=>20个上限,2等奖=>30个上限],
                    "日期开始时间|日期结束时间" => [1等奖=>10个上限,2等奖=>15个上限]
                    "2017-07-06" => [1=>20,2=>30]
                    "2017-08-11|2017-10-20" => [1=>10,2=>15]
                    ......
                ],
                "nowCount"=> 10 //只能是匿名函数,匿名函数每次调用接收一个奖品id，返回这个奖品id现在所中的数量
            ]
        }
```       