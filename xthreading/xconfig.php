<?php
class xconfig
{
    // Конфигурации библиотеки Xthread.
    private $configX = [

        /** Лимит потоков **/
        'limit_threads' => 5,

        /** Имя пользователя от которого запускается поток (Пользователь на хостинге) **/
        'user' => 'mitivil',

        /**
         * Укажите драйвер запускающего файла, прослойку между (Xthread) и сайтом 
         * !Драйвера находятся в папке (xthreading)
         */
        'driver_file' => 'driverOpenCart',

        /** Укажите путь к файлу для записи логов запускаемого драйвера **/
        'dir_log' => DIR_SYSTEM . 'logs/error.log',

        /** Укажите папку с текущим приложением(Проектом) **/
        'dir_application' => DIR_APPLICATION,

        /** Укажите папку в которой находится запускающий файл библиотеки(Xthreading) **/
        'dir_Xthreading' => DIR_SYSTEM . 'library/',

        /** Максимальное время выполнения скрипта
         *  Указать в секундах. 
         */
        'max_execution_time' => 3,

        /** Максимальная Память для скрипта
         * Указать в мегабайтах
         */
        'memory_limit' => 5,

        /** Временная зона */
        'timezone' => 'Europe/Moscow'
    ];




    /** Получить **/
    public function get()
    {
        return $this->configX;
    }

    /** Установить **/
    public function setUser($user)
    {
        $this->configX['user'] = $user;
        return $this;
    }
    public function setDriver($driver)
    {
        $this->configX['driver_file'] = $driver;
        return $this;
    }
    public function setDirApplication($dir_application)
    {
        $this->configX['dir_application'] = $dir_application;
        return $this;
    }
    public function setMaxTime($max_execution_time)
    {
        $this->configX['max_execution_time'] = $max_execution_time;
        return $this;
    }
    public function setMemoryLimit($memory_limit)
    {
        $this->configX['memory_limit'] = $memory_limit;
        return $this;
    }
}
