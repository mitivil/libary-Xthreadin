<?php

/** Монитор-системы ОЗУ, ЦПУ **/
class InfoSystem
{
    private $config = [];

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getSystem()
    {
        return[
            'user'     => $this->config['user'],
            'memory'   => $this->getMem(),
            'cpu'   => $this->getCpu(),
        ];
    }
    public function getCpu()
    {
        $cpu = sys_getloadavg();
        $execstring = 'ps -f -u ' . $this->config['user'] . ' 2>&1';
        $process_list = "";
        exec($execstring, $process_list);
        return [
            'cpu_%'              => $cpu[0],
            'php_list_processes' => $process_list
        ];
    }

    private function getMem()
    {
        $free = shell_exec('free');
        $free = (string)trim($free);
        $free_arr = explode("\n", $free);
        $mem = explode(" ", $free_arr[1]);
        $mem = array_filter($mem);
        $mem = array_merge($mem);
        return [
            'used_in_mb' => ($mem[2] / 1000) + 100,
            'used_in_%'  => round($mem[2] / $mem[1] * 100, 0)
        ];
    }

}
