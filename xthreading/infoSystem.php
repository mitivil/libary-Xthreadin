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
        return [
            'user'     => $this->config['user'],
            'memory'   => $this->getMem(true),
            'cpu'   => $this->getCpu(true),
        ];
    }
    public function getCpu($internal_call = false)
    {
        $result = [];
        try {
            $cpu = sys_getloadavg();
            $execstring = 'ps -f -u ' . $this->config['user'] . ' 2 >&1 ';
            $process_list = "";
            exec($execstring, $process_list);
            if ($internal_call == false) $result['user'] = $this->config['user'];
            $result['status'] = 'ok';
            $result['cpu_%'] = $cpu[0];
            $result['php_list_processes'] = $process_list;
        } catch (Error $er) {
            $result = [
                'status'  => 'error',
                'message' => 'Не удалось получить доступ к диспетчеру задач: ' . $er->getMessage()
            ];
        } finally {
            return $result;
        }
    }

    private function getMem($internal_call = false)
    {
        $result = [];
        try {
            $free = shell_exec('free');
            $free = (string)trim($free);
            $free_arr = explode("\n", $free);
            $mem = explode(" ", $free_arr[1]);
            $mem = array_filter($mem);
            $mem = array_merge($mem);
            if ($internal_call == false) $result['user'] = $this->config['user'];
            $result['status'] = 'ok';
            $result['used_in_mb'] = ($mem[2] / 1000) + 100;
            $result['used_in_%'] = round($mem[2] / $mem[1] * 100, 0);
        } catch (Error $er) {
            $result = [
                'status'  => 'error',
                'message' => 'Не удалось получить доступ к диспетчеру задач: ' . $er->getMessage()
            ];
        } finally {
            return $result;
        }
    }
}
