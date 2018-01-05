<?php
namespace LuckDraw\Service;

use LuckDraw\DrawKernel;
use LuckDraw\Exceptions\NotWinPrizeException;
use LuckDraw\Filters\ActivityTime;
use LuckDraw\Filters\DateCountLimit;
use LuckDraw\Filters\DatePrizeProbability;
use LuckDraw\Filters\DateProbability;
use LuckDraw\Filters\DateTime;
use LuckDraw\Filters\EveryPrizeCountReach;
use LuckDraw\Filters\EveryPrizeLimit;
use LuckDraw\Filters\PrizeAllOut;
use LuckDraw\Filters\TotalPrizeLimit;
use LuckDraw\Filters\WinIf;
use LuckDraw\LuckDraw;

class LuckDrawService
{
    /**
     * 过滤器名字ID
     * Filter's id.
     */
    const WIN_IF_FILTER = 0x01;
    const DATE_TIME_FILTER = 0x02;
    const ACTIVITY_TIME_FILTER = 0x03;
    const PRIZE_ALL_OUT_FILTER = 0x04;
    const DATE_PROBABILITY_FILTER = 0x05;
    const DATE_COUNT_LIMIT_FILTER = 0x06;
    const EVERY_PRIZE_LIMIT_FILTER = 0x07;
    const TOTAL_PRIZE_LIMIT_FILTER = 0x08;
    const DATE_PRIZE_PROBABILITY_FILTER = 0x09;
    const EVERY_PRIZE_COUNT_REACH_FILTER = 0x10;

    /**
     * process exception.
     * @var null
     */
    protected $exception = NULL;

    /**
     * Get the exception which has been throw by this process.
     * 获取程序运行时产生的异常
     * @return null
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Start this service.
     * 开始运行此服务
     * @param $config
     * @return int|null|string
     */
    public function startService($config)
    {

        $luckDraw = new LuckDraw();
        $filters = $config["filters"];
        try {
            foreach ($filters as $filter) {
                $this->addFilter($luckDraw, $filter, $config);
            }
            $prize = $luckDraw->lottery(
                (new DrawKernel())
                    ->setProbability($config["probability"])
                    ->setPrizeProbability($config["prizeProbability"])
            );
            return $prize;
        } catch (NotWinPrizeException $exception) {
            $this->exception = $exception;
            return NULL;
        } catch (\Exception $exception) {
            $this->exception = $exception;
            return NULL;
        }
    }

    /**
     * According to filter id,add the filter to filters pool.
     * 根据过滤id，添加对应的过滤器到过滤器池中
     * @param $luckDraw
     * @param $filter
     */
    protected function addFilter(LuckDraw $luckDraw, $filter, $config)
    {
        switch ($filter) {
            case self::WIN_IF_FILTER:
                $luckDraw->addFilter(WinIf::class);
                break;
            case self::DATE_TIME_FILTER:
                $luckDraw->addFilter(DateTime::class, $config["dateTime"], true);
                break;
            case self::ACTIVITY_TIME_FILTER:
                $luckDraw->addFilter(ActivityTime::class, $config["activityTime"]);
                break;
            case self::PRIZE_ALL_OUT_FILTER:
                $luckDraw->addFilter(
                    PrizeAllOut::class,
                    $config["prizeAll"]["limit"],
                    $config["prizeAll"]["nowCount"]
                );
                break;
            case self::DATE_PROBABILITY_FILTER:
                $luckDraw->addFilter(DateProbability::class, $config["dateProbability"], true);
                break;
            case self::EVERY_PRIZE_LIMIT_FILTER:
                $luckDraw->addFilter(
                    EveryPrizeLimit::class,
                    $config["everyPrize"]["limit"],
                    $config["everyPrize"]["nowCount"]
                );
                break;
            case self::TOTAL_PRIZE_LIMIT_FILTER:
                $luckDraw->addFilter(
                    TotalPrizeLimit::class,
                    $config["totalPrize"]["limit"],
                    $config["totalPrize"]["nowCount"]
                );
                break;
            case self::DATE_PRIZE_PROBABILITY_FILTER:
                $luckDraw->addFilter(
                    DatePrizeProbability::class,
                    $config["datePrizeProbability"]
                    , true
                );
                break;
            case self::EVERY_PRIZE_COUNT_REACH_FILTER:
                $luckDraw->addFilter(
                    EveryPrizeCountReach::class,
                    $config["prizeCount"]["limit"],
                    $config["prizeCount"]["nowCount"]
                );
                break;
            case self::DATE_COUNT_LIMIT_FILTER:
                $luckDraw->addFilter(
                    DateCountLimit::class,
                    $config["dateCount"]["limit"],
                    $config["dateCount"]["nowCount"]
                );
                break;
            default:
                break;
        }
    }
}