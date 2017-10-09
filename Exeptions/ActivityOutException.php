<?php
namespace LuckDraw\Exceptions;
class ActivityOutException extends \Exception
{
    public function __construct($message = '', $code = 200, Exception $previous = NULL)
    {
        $message = '活动已结束';
        parent::__construct($message, $code, $previous);
    }
}