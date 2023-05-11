<?php

class Worker
{
    private $managerDriver = '';
    private $PID = '';
    private $status = '';
    private $configX = [];
    private $route = [];
    private $data = [];
    private $beacon = '';

    public function __construct()
    {
        $this->managerDriver = __DIR__ . '/managerDriver.php';
        $this->status = 'await_instal';
    }

    //-Запуск работника.
    public function run(): void
    {
        $this->PID = $this->getUid(35); //-Уникальный номер процесса. 
        $out = [];
        $res = [];
        exec($this->getCommand(false), $out, $res);
    }

    private function getCommand($isTest): string
    {
        $command = 'php ' . $this->managerDriver . ' ' . escapeshellarg(serialize($this->route)) . ' ' . escapeshellarg(serialize($this->PID)) . ' ' . escapeshellarg(serialize($this->data)) . ' ' . escapeshellarg(serialize($this->beacon)) . ' ' . escapeshellarg(serialize($this->configX));
        if (PHP_OS == 'WINNT' || PHP_OS == 'WIN32' || PHP_OS == 'Windows') {
            // Windows
            $command = 'start "" ' . $command;
        } else {
            // Linux/UNIX
            $command = $command . ' > /dev/null 2>&1 & echo $';
        }
        return $command;
    }




    //-Установки.
    public function setConfig($configX)
    {
        $this->configX = $configX;
    }
    public function setRoute($method)
    {
        $this->route = $method;
    }
    public function setData($data)
    {
        $this->data = $data;
    }
    public function setStatus($status)
    {
        $this->status = $status;
    }
    public function setBeacon($beacon)
    {
        $this->beacon = $beacon;
    }

    //-Получить.
    public function getPid()
    {
        return $this->PID;
    }
    public function getData()
    {
        return $this->data;
    }
    public function getRoute()
    {
        return $this->route;
    }
    public function getConfig()
    {
        return $this->configX;
    }
    public function getStatus()
    {
        return $this->status;
    }
    public function getBeacon()
    {
        return $this->beacon;
    }

    //-Системные
    function getUid($l = 10)
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, $l);
    }
}
