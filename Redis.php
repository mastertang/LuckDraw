<?php

namespace LuckDraw;

use Predis\Client;

/**
 * Class Redis
 * @package LuckDraw
 */
class Redis
{
    /**
     * @var string 协议
     */
    public $scheme = "tcp";

    /**
     * @var string 地址
     */
    public $host = "127.0.0.1";

    /**
     * @var string 端口
     */
    public $port = "6379";

    /**
     * @var string 密码
     */
    public $password = "";

    /**
     * @var int 默认数据库
     */
    public $database = 0;

    /**
     * @var string 连接超时，0.1秒
     */
    public $connectionTimeOut = "0.1";

    /**
     * @var string 读写超时
     */
    public $rwTimeOut = "1";

    /**
     * @var string 错误信息
     */
    public $errorMessage = "";

    /**
     * 设置连接参数
     *
     * @param string $scheme
     * @param string $host
     * @param int $port
     * @return $this
     */
    public function setConnection($scheme = "tcp", $host = "127.0.0.1", $port = 6379)
    {
        $this->scheme = $scheme;
        $this->host   = $host;
        $this->port   = $port;
        return $this;
    }

    /**
     * 设置读写超时
     *
     * @param string $rwTimeOut
     * @return $this
     */
    public function setRWTimeOut($rwTimeOut = "0.1")
    {
        if (is_numeric($rwTimeOut) && $rwTimeOut > 0) {
            $this->rwTimeOut = $rwTimeOut;
        }
        return $this;
    }

    /**
     * 设置连接超时
     *
     * @param string $connectionTimeOut
     * @return $this
     */
    public function setConnectionTimeOut($connectionTimeOut = "0.1")
    {
        if (is_numeric($connectionTimeOut) && $connectionTimeOut > 0) {
            $this->connectionTimeOut = $connectionTimeOut;
        }
        return $this;
    }

    /**
     * 设置密码
     *
     * @param string $password
     * @return $this
     */
    public function setParams($password = "")
    {
        $this->password = $password;
        return $this;
    }

    /**
     * 设置数据库
     *
     * @param int $dataBase
     * @return $this
     */
    public function setDatabase($dataBase = 0)
    {
        $this->database = $dataBase;
        return $this;
    }

    /**
     * 创建Redis实例
     *
     * @return bool|Client
     */
    public function createRedisClient()
    {
        $connection = [
            "scheme"             => $this->scheme,
            "host"               => $this->host,
            "port"               => $this->port,
            "timeout"            => $this->connectionTimeOut,
            "read_write_timeout" => $this->rwTimeOut
        ];
        $option     = ["parameters" => []];
        if (!empty($this->password)) {
            $option["parameters"]["password"] = $this->password;
        }
        try {
            $client = new Client($connection, $option);
            $client->select($this->database);
            $client->set("test", 100);
            if ($client->get("test") != "100") {
                $this->errorMessage = "Connect failed";
                return false;
            }
            return $client;
        } catch (\Exception $exception) {
            $this->errorMessage = $exception->getMessage();
            return false;
        }
    }

    /**
     * 将数据推到列表左端
     *
     * @param $key
     * @param $value
     * @return bool
     */
    public function pushDataToLeftList($key, $value)
    {
        $redisInstance = $this->createRedisClient();
        if ($redisInstance instanceof Client) {
            $result = $redisInstance->lpush($key, $value);
            if ($result === false || $result === null) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取列表数据长度
     *
     * @param $key
     * @return bool
     */
    public function getListLen($key)
    {
        $redisInstance = $this->createRedisClient();
        if ($redisInstance instanceof Client) {
            $result = $redisInstance->llen($key);
            if ($result === false || $result === null) {
                return false;
            }
            return (int)$result;
        } else {
            return false;
        }
    }
}