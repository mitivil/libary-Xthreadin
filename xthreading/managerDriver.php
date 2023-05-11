<?php

class ManagerDriver
{
    private $PID = '';
    private $beacon = '';
    private $data = [];
    private $router = [];
    private $configX = [];
    private $respons = [];
    private $error = [];
    private $time = '';

    //-Запускаем!
    public function runProcces($transferOutData): void
    {
        $start = microtime(true);

        try {
            if (file_exists($transferOutData['action']['file'])) {;
                include_once($transferOutData['action']['file']);

                $classXthreading;
                if (!empty($transferOutData['construct_data'])) {
                    $classXthreading = new $transferOutData['action']['class']($transferOutData['construct_data']);
                } else {
                    $classXthreading = new $transferOutData['action']['class']();
                }

                $this->respons = (empty($this->data)) ? $classXthreading->{$transferOutData['action']['method']}() : $classXthreading->{$transferOutData['action']['method']}($this->data);
            } else {

                $this->setError('error xthreading (runProcces)', 'Не найден исполняемый файл для запуска (' . $transferOutData['action']['file'] . ')');
            }
        } catch (Error $e) {

            //-Ошибка запуска процесса.
            $this->setError('error xthreading (runProcces)', $e->getMessage());
        } finally {
            $this->time = round((microtime(true) - $start), 0);
        }
    }

    //-Инициализация конфигураций и всех надстроек текущего (Движка, проекта, сайта).
    public function runEngine()
    {
        try {
            $transferOutData = [];
            $action = [];
            $routerX = $this->router;        //-Входная-Глобальная переменная для Драйвера(Не удалять).
            $configX = $this->getConfigX(); //-Входная-Глобальная переменная для Драйвера(Не удалять).
            $path_file_driver = $this->configX['dir_Xthreading'] . 'xthreading/driverRun/' . $this->configX['driver_file'] . '.php';
            if (file_exists($path_file_driver)) {
                include_once($path_file_driver); //-Подключаем драйвер.
                return $transferOutData; //-Action приходит из подключаемого драйвера.
            } else {

                $this->setError('error xthreading (runEngine)', 'Не найден файл драйвера  (' . $path_file_driver . ')');
            }
        } catch (Error $e) {
            //-Ошибка конфигураций движка.
            $this->setError('error xthreading (runEngine)', $e->getMessage());
        }
    }

    //-Завершаем процесс.
    public function finishProcces(): void
    {
        $result = [];
        if (empty($this->error)) {
            $result = [
                'beacon'            => $this->beacon,
                'status'            => 'ok',
                'completed_in_time' => $this->time,
                'respons'           => $this->respons
            ];
        } else {
            $result = [
                'status'              => 'error',
                'execut_in_time'      => $this->time,
                'error_message'       => $this->error,
            ];
        }
        file_put_contents(__DIR__ . '/pid/' . $this->PID, serialize($result));
        echo '';
        exit;
    }


    //-Установки.
    public function setData($argv_data = ''): void
    {
        $this->data = !empty($argv_data) ? unserialize($argv_data) : [];
    }
    public function setBeacon($beacon): void
    {
        $this->beacon = unserialize($beacon);
    }
    public function setRouter($argv_route = ''): void
    {
        $this->router = unserialize($argv_route);
    }
    public function setConfigX($argv_configx = ''): void
    {
        $this->configX = !empty($argv_configx) ? unserialize($argv_configx) : [];
    }
    public function setPid($argv_pid = ''): void
    {
        $this->PID = unserialize($argv_pid);
    }
    public function setError($status, $message, $debbug = []): void
    {
        $this->error = [
            'status'  => $status,
            'message' => $message,
        ];
        if (!empty($debbug)) $this->error['debbug'] = $debbug;
        $this->finishProcces();
    }

    //-Получить.
    public function getConfigX()
    {
        return $this->configX;
    }
}


$transferOutData;
try {
    /*******************
     * Запускаем драйвер.
     */
    $action = [];
    $driver = new ManagerDriver();
    //-Устанавливаем роутер.
    $driver->setRouter($argv[1]);
    //-Устанавливаем PID-номер процесса.
    $driver->setPid($argv[2]);
    //-Устанавливаем входные данные.
    $driver->setData($argv[3]);
    //-Устанавливаем маячок.

    $driver->setBeacon($argv[4]);
    //-Устанавливаем Конфигурации.
    $driver->setConfigX($argv[5]);


    //-Настраиваем PHP.ini ------------------->
    $configs = $driver->getConfigX();
    //-Временная зона.
    date_default_timezone_set($configs['timezone']);
    ini_set('max_execution_time', $configs['max_execution_time']);
    ini_set('memory_limit', $configs['memory_limit'] . 'M');
    //-Логи.
    ini_set("log_errors", 1);
    ini_set("error_log", $configs['dir_log']);

    //-Подключаем движок проекта(Сайта).
    $transferOutData = $driver->runEngine();
    //-Проверка выходных данных драйвера.
    if (!isset($transferOutData))  $driver->setError('error xthreading (Драйвер "' . $driver->getConfigX()['driver_file'] . '" не вернул $transferOutData)', 'Настройте драйвер для вашего проекта(Сайта, движка) правильно и прочитайте документацию ещё раз.', $driver->getConfigX());
    if (empty($transferOutData))   $driver->setError('error xthreading (Драйвер "' . $driver->getConfigX()['driver_file'] . '" вернул пустой $transferOutData)', 'Настройте драйвер для вашего проекта(Сайта, движка) правильно и прочитайте документацию ещё раз.', $driver->getConfigX());
    if (!isset($transferOutData['action']['file']))  $driver->setError('error xthreading (Драйвер "' . $driver->getConfigX()['driver_file'] . '" не вернул  $transferOutData["action"]["file"])', 'Настройте драйвер для вашего проекта(Сайта, движка) правильно и прочитайте документацию ещё раз.');
    if (!isset($transferOutData['action']['class']))  $driver->setError('error xthreading (Драйвер "' . $driver->getConfigX()['driver_file'] . '" не вернул  $transferOutData["action"]["class"])', 'Настройте драйвер для вашего проекта(Сайта, движка) правильно и прочитайте документацию ещё раз.');
    if (!isset($transferOutData['action']['method']))  $driver->setError('error xthreading (Драйвер "' . $driver->getConfigX()['driver_file'] . '" не вернул  $transferOutData["action"]["method"])', 'Настройте драйвер для вашего проекта(Сайта, движка) правильно и прочитайте документацию ещё раз.');
    if (empty($transferOutData['action']['file']))  $driver->setError('error xthreading (Драйвер "' . $driver->getConfigX()['driver_file'] . '" вернул пустой $transferOutData["action"]["file"])', 'Настройте драйвер для вашего проекта(Сайта, движка) правильно и прочитайте документацию ещё раз.');
    if (empty($transferOutData['action']['class']))  $driver->setError('error xthreading (Драйвер "' . $driver->getConfigX()['driver_file'] . '" вернул пустой $transferOutData["action"]["class"])', 'Настройте драйвер для вашего проекта(Сайта, движка) правильно и прочитайте документацию ещё раз.');
    if (empty($transferOutData['action']['method']))  $driver->setError('error xthreading (Драйвер "' . $driver->getConfigX()['driver_file'] . '" вернул пустой $transferOutData["action"]["method"])', 'Настройте драйвер для вашего проекта(Сайта, движка) правильно и прочитайте документацию ещё раз.');

    //-Запускаем процесс.
    $driver->runProcces($transferOutData);
} catch (Error $e) {

    //-ERROR.
    $driver->setError('error xthreading (Не запустился менеджер драйвера)', $e->getMessage());
} finally {

    //-Завешаем процесс.
    $driver->finishProcces();
}
