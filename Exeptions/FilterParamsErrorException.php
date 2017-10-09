<?php
namespace LuckDraw\Exceptions;
class FilterParamsErrorException extends \Exception
{
    public function __construct($message = '', $code = 200, Exception $previous = NULL)
    {
        $message = '过滤器参数错误';
        parent::__construct($message, $code, $previous);
    }
}