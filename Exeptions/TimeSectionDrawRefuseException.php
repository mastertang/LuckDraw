<?php
namespace LuckDraw\Exceptions;
class TimeSectionDrawRefuseException extends \Exception
{
    public function __construct($message = '', $code = 200, Exception $previous = NULL)
    {
        $message = '当天当前时间不能进行抽奖';
        parent::__construct($message, $code, $previous);
    }
}