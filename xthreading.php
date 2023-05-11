<?php

class xthreading
{
    private $dir_Xthreading = DIR_SYSTEM . 'library/';

    public $config;
    public $system;

    private $threads = [];
    private $workerObj;
    private $beacon = '';
    private $route;
    private $data;

    private $statusRun = false;
    private $timeStart;
    private $procces = [];
    private $history_pid = [];
    private $spare_time_execut = 3; //-Запасное время в сек.

    public function __construct()
    {
        $this->reload();
    }


    /******** Установить *********/
    public function setRoute($route)
    {
        if (!is_string($route)){
            error_log('Xthreadin- : Метод ->setRoute() принимает строковый тип, читайте документацию к библиотеке Xthreading ', 0);
        }else{
            $this->route = $route;
            return $this;
        }
      
    }
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
    public function setBeacon($beacon)
    {
        if (!is_string($beacon)){
            error_log('Xthreadin- : Метод ->setBeacon() принимает строковый тип, читайте документацию к библиотеке Xthreading ', 0);
        }else{
            $this->beacon = $beacon;
            return $this;
        }

    }

    /******** Добавить работника *********/
    public function add()
    {
        $limit_threads = (int)$this->config->get()['limit_threads'];
        if (empty($this->route)) {
            error_log('Xthreadin- : Перед ->add() установите метод $this->xthreading->setRouter(), читайте документацию к библиотеке Xthreading ', 0);
        } else {
            if ((int)(count($this->threads) + 1) <= $limit_threads) {
                //-Добавляем.
                $this->threads[] = new $this->workerObj;
                $index = count($this->threads) - 1;
                //-Маячок
                $this->beacon = empty($this->beacon) ? $index : $this->beacon;
                //-Устанавливаем работника.
                $this->threads[$index]->setConfig($this->config->get());
                $this->threads[$index]->setRoute($this->route);
                $this->threads[$index]->setData($this->data);
                $this->threads[$index]->setBeacon($this->beacon);
                $this->threads[$index]->setStatus('await_run');
                $this->reset();
                return $this;
            } else {
                error_log('Xthreadin- Достигли лимита процессов: ' . $limit_threads . ' , используйте метод ($this->xthreading->await()) что-бы освободить процессы или увеличить кол-во редактируйте (xconfig.php)', 0);
            }
        }
    }

    /******** Запуск работника *********/
    public function run()
    {
        $this->deleteFilesPid(); //-Очистим старые файлы-pid перед запуском.

        if (!empty($this->threads)) {
            $this->runAsync(); //-Запускаем процесс в Асинхронном режиме.
            return $this;
        } else {
            error_log('Xthreadin- Перед тем как запускать процессы добавьте их в список $this->xthreading->add()', 0);
        }
    }
    /******** Ожидаем работников *********/
    public function await()
    {
        $result = $this->awaitSync(); //-Ожидаем все асинхронные процессы.
        return $result;
    }



    /******** Запустить процессы ********/
    private function runAsync(): void
    {
        foreach ($this->threads as $thread) {
            $thread->run(); //-Запуск.         
            $this->history_pid[] = $thread->getPid(); //-Запоминаем что-бы в __destruct().

            $this->procces[] = [
                'status'      => 'work',
                'beacon'      => $thread->getBeacon(),
                'PID'         => $thread->getPid(),
                'execut_time' => ((int)$thread->getConfig()['max_execution_time'] + (int)$this->spare_time_execut)
            ];
        }
        $this->threads = [];
    }

    /******** Ожидать процессы ********/
    private function awaitSync()
    {
        $result = [];
        $this->timeStart = microtime(true); //-Запускаем счётчик(Время выполнения).
        //->Дожидаемся завершения процессов.
        $finishProcces = false;
        $total_procces = count($this->procces);
        while ($finishProcces == false) {
            $time_execut = round((microtime(true) - $this->timeStart), 0);

            $finish = true;
            for ($i = 0; $i < $total_procces; $i++) {

                //-Если процесс ещё в работе.
                if ($this->procces[$i]['status'] == 'work') {
                    $finish = false; // Продолжаем мониторить.
                    //-Проверяем время выполенния процесса(TIME).
                    if ((int)$time_execut <= (int)$this->procces[$i]['execut_time']) {

                        $respons_procc = $this->isThreads($this->procces[$i]['PID']);
                        if ($respons_procc !== false) {

                            $this->procces[$i]['status'] = 'ok';
                            $result[] = $respons_procc;
                        }
                    } else {
                        //-Если вышло время выполнения процесса.
                        $this->procces[$i]['status'] = 'error_execut_time';
                        $result[] = [
                            'beacon'   => $this->procces[$i]['beacon'],
                            'status'   => 'error_execut_time',
                            'message'  => 'Вышло время выполнения процесса, процесс не ответил',
                            'execut_in_time'  => $time_execut
                        ];
                    }
                }
            }
            if ($finish == true) break;
            usleep(10000);  //-Засыпаем на 10 мс
        }
        $this->procces = [];
        return $result;
    }

    private function isThreads($pid)
    {
        $path_file = $this->dir_Xthreading . 'xthreading/pid/' . $pid;
        if (file_exists($path_file)) {
            $data = file_get_contents($path_file);
            $this->deleteFileProcc($pid);
            return  unserialize($data);
        } else {
            return false;
        }
    }


    /******** Получить информацию текущего состояния *********/
    public function getInfo()
    {
        $info = [];
        $info['threads']['total'] = count($this->threads);
        foreach ($this->threads as $thread) {
            $info['threads']['list'][] = [
                'beacon'  => $thread->getBeacon(),
                'status'  => $thread->getStatus(),
                'config'  => $thread->getConfig(),
                'route'   => $thread->getRoute(),
            ];
        }
        return $info;
    }
    /******** Монитор-системы ОЗУ, ЦПУ *********/
    public function getSystem()
    {
        $this->loadInfoSystem();
        return  $this->system->getSystem();
    }

    /******** Сбрасываем конфигурацию *********/
    public function reset()
    {
        $this->data = [];
        $this->route = '';
        $this->beacon = '';
        $this->loadConfig();
    }


    /******** Системные и прочее *********/
    private function reload(): void
    {
        $this->data = [];
        $this->route = '';
        $this->beacon = '';
        $this->loadConfig();
        $this->loadWorker();
        $this->loadInfoSystem();
    }
    private function loadConfig(): void
    {
        $path = $this->dir_Xthreading . 'xthreading/xconfig.php';
        include_once($path);
        $this->config = new xconfig();
    }
    private function loadWorker(): void
    {
        $path = $this->dir_Xthreading . 'xthreading/worker.php';
        include_once($path);;
        $this->workerObj = new Worker($this->config->get());
    }
    private function loadInfoSystem(): void
    {
        $path = $this->dir_Xthreading . 'xthreading/infoSystem.php';
        include_once($path);
        $this->system = new InfoSystem($this->config->get());
    }

    private function deleteFileProcc($pid): void
    {
        $path_file = $this->dir_Xthreading . 'xthreading/pid/' . $pid;
        if (file_exists($path_file)) {
            unlink($path_file);
        }
    }
    private function deleteFilesPid(): void
    {
        date_default_timezone_set('Europe/Moscow');
        $path_file = $this->dir_Xthreading . 'xthreading/pid/';
        $files_pid = scandir($path_file);
        foreach ($files_pid as $file) {
            $path_file_pid = $path_file . $file;
            if (file_exists($path_file_pid) && $file != '.' && $file != '..') {
                $date_now = new DateTime(date("Y-m-d H:i:s"));
                $date_file = new DateTime(date("Y-m-d H:i:s", filemtime($path_file_pid)));
                $date_file->modify("+20 minutes"); //-Через 20 минут старые файл-PID удалятся.
                if ($date_file < $date_now) {
                    unlink($path_file_pid);
                }
            }
        }
    }

    //-Разобрать.
    public function __destruct()
    {
        //-Удаляем файлы процесса если остались.
        foreach ($this->history_pid as $pid) {
            $this->deleteFileProcc($pid);
        }
    }
}
