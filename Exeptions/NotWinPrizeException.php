<?php
namespace LuckDraw\Exceptions;
class NotWinPrizeException extends \Exception
{
    public function __construct($message = '', $code = 200, Exception $previous = NULL)
    {
        $message = '没有中奖';
        parent::__construct($message, $code, $previous);
    }
}