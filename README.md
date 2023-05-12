# Libary-Xthreading
Самостоятельная библиотека **Xthreading** - Запускает методы(функции) в отдельном процессе и возвращает ответ, имеет гибкие настройки и менеджер системы Linux. 
Библиотека Xthreading работает на любых движках-проектах, сайтов работающие на языке php, для этого необходимо настроить драйвер взаимодействия Xthreading  с текущим проектом(Ни чего сложного).
##### !Избегайте замыкания запускаемых методов(функций)!
##### Используйте библиотеку **Xthreading** только в едином экземпляре ибо не избежать ошибок  в работе.

<br><br>

##### Требования
- PHP 7.2 >
- Apache, nginx


##### Меню
<ul dir="auto">
<li><a href="#kitStart">Установка</a></li>
<li><a href="#rout">Интерфейсы управления(Как это работает?)</a></li>
<li><a href="#configApacheNginx">Дополнительные интерфейсы</a></li>
<li><a href="#logicController">Как настроить драйвер</a></li>
<li><a href="#filesStructure">Файловая структура</a></li>
</ul>






<a id="kitStart"></a>
##### Установка библиотеки
 - Скачать все файлы проекта в Ваш проект а именно в менеджер-библиотек 
 - Настроить файл по пути : ```xthreading/xconfig.php```
 - Добавить и настроить драйвер-запуска под Ваш проект, по пути: ```xthreading/driverRun/```, тык<a href="#logicController">Как настроить драйвер</a></li>
 - Посетить запускающий файл библиотеки: ```xthreading.php``` и прописать сверху в переменную ```$dir_Xthreading``` путь до запускающего файла(С деланно для того что-бы не было конфликтов с разными модулями проекта к примеру ```VqMod``` и разными кэшированиями).
<br><br>


<a id="rout"></a>
##### Интерфейсы управления(Как это работает?)
В данном случае библиотека **Xthreading** подключена к самому верхнему уровню ООП у вас может отличаться!

Доступные общие методы:
 - **$this->xthreading->setRoute('sale/order/test2')** 
    + ->setRoute() -Определить путь до запускаемого метода (Передаётся в драйвер для дальнейшего определения). В данном случае (sale-папка, order-файл, test2-метод)
 - **$this->xthreading->setData($data)**
   + ->setData() -Устанавливает данные для передачи запускающему методу в текущем случае методу ```test2```, тип данных любой. 
 - **$this->xthreading->setBeacon('Маячок_1')**
   + ->setBeacon() -Пометьте процесс своим маячком для будущего распознавания в ответе(Когда процессы будут выполнены и вернётся общий массив с ответами) после метода ```->await()```.
 - **$this->xthreading->add()**
   + ->add() -Добавляет и инициализирует задачу в ожидающий процесс. (Требует установленного метода ```->setRoute()```) без него выдаст исключение.
 - **$this->xthreading->reset()**
   + ->reset() -Сбросывает все настройки и процессы библиотеки по умолчанию.
 - **$this->xthreading->run()**
   + ->run() -Запускает все добавленные процессы асинхронно (Требует установленного метода ```->add()```) добавленных процессов в ожидание.

Библиотека поддерживает цепочечные вызовы быстрый пример добавляем процесс в очередь для ожидания:
```
$this->xthreading->setRoute('sale/order/test2')->setData('Я передал строкой-1')->setBeacon('Маячок_1')->add();
$this->xthreading->setRoute('sale/order/test2')->setData('Я передал строкой-2')->setBeacon('Маячок_2')->add();
```
**Метод test2($data)** -К которому обращаемся имеет следующий простой код:
```
public function test2($data)
  {
	  sleep(3);
    return $data;
  }
```
В данном случае Мы добавили два процесса в ожидание, они ещё не запущены.
Что-бы запустить добавленные задачи обратимся к методу.
```
$array_respons = $this->xthreading->run()->await();
```
**$array_respons** - Будет содержать ответ запущенной задачи в массиве.
Вы заметили что идёт обращение сразу к двум методам ```->run()->await()``` это нужно для запуска всех добавленных процессов и ожидания ответа, методом ```->await()``` Мы сообщаем интерпретатору кода -> Ожидать завершения всех процессов и вернуть ответ в виде массива.

**Ответ придёт через 3 секунды ровно**
Вот пример ответа что определилось в переменной **$array_respons**:
```
Array
(
    [0] => Array // Второй процесс!
        (
            [beacon] => Маячок_2
            [status] => ok
            [completed_in_time] => 3
            [respons] => Я передал строкой-2
        )

    [1] => Array // Первый процесс!
        (
            [beacon] => Маячок_1
            [status] => ok
            [completed_in_time] => 3
            [respons] => Я передал строкой-1
        )
)
```

Как вы уже заметили что второй процесс в ответе пришёл быстрее чем первый процесс:) , это всё потому что процессы запускаются и работают не зависимо друг от друга а так-же от основного скрипта.  !И что-бы не спутаться в ответах желательно устанавливать маячки на каждый добавляемый процесс методом ```->setBeacon('Маячок_1')```  и в переборе ```foreach```  его лучше будет определить.

**Асинхронность процессов** -Так-же заметьте что два процесса выполнились ровно за 3 секунды, если запускать их последовательно ждали бы 6 сек.
<br><br><br>



<a id="configApacheNginx"></a>
##### Дополнительные интерфейсы 
  В данном случае библиотека **Xthreading** подключена к самому верхнему уровню ООП у вас может отличаться!

##### Установка конфигураций
!!!Конфигурации не обязательно менять таким способом достаточно изменить общий конфигурационный файл ```xthreading/xconfig.php```!!!
 - **$this->xthreading->config->setUser('www-data')** 
    + ->setUser() -Устанавливает пользователя в системе Линукс для запуска процессов (Определяется при запуске менеджера-драйверов).
 - **$this->xthreading->setMemoryLimit(60)**
   + ->setMemoryLimit() -Устанавливает лимит используемой памяти процессам в (МБ) (Определяется при запуске менеджера-драйверов). 
 - **$this->xthreading->setDriver('driverOpenCart')**
   + ->setDriver() -Устанавливает нужный драйвер для запуска процессов, драйвер служит промежуточным водителем между библиотекой и движком вашего проекта для взаимодействия. 
 - **$this->xthreading->config->setMaxTime(10)**
   + ->setMaxTime() -Устанавливает лимит по времени-исполнения процессам в (СЕК) (Определяется при запуске менеджера-драйверов).
 - **$this->xthreading->config->get()**
   + ->get() -Возвращает все настройки из файла ```xthreading/xconfig.php``` конфигураций.

Пример установки конфигураций для процесса:
```
$this->xthreading->config->setUser('www-data')->setMemoryLimit(50)->setDriver('driverOpenCart')->setMaxTime(5);
```
<br><br><br>

<a id="filesStructure"></a>
##### Файловая структура библиотеки
```
├─ xthreading\ 
|         |             
|         ├─ driverRun\                     "Папка для подключаемых драйверов"
|         |         └─ driverOpenCart.php   "Сам драйвер на примере движка OpenCart-version 2.0.1.1 !(Можно изменить в настройках)"
|         |
|         ├─ logs\                          "Папка для лорнирования подключаемых драйверов !(Можно изменить в настройках)" 
|         |    └─ log.txt                   "Файл для логов(Пример)"
|         |
|         ├─ pid\                           "Временная папка для запускаемых процессов (Системная)"
|         |
|         ├─ infoSystem.php                 "Файл для отслеживания ЦПУ,память системы линукс (Системный)"
|         ├─ managerDriver.php              "Файл управления подключаемых драйверов (Системный)"
|         ├─ worker.php                     "Файл запуска процессов (Системный)"
|         └─ xconfig.php                    "Файл конфигурации библиотеки (Системный-Требует настройки)"
|              
└─ xthreading.php                           "Стартующий файл библиотеки (Требуется подключить к менеджеру-библиотек сайта, проекта)"
```

<br><br>


<a id="logicController"></a>
##### Как настроить драйвер

Драйвер в библиотеке **Xthreading** служит путеводителем, прослойкой между самой библиотекой и движком-проектом, сайтом для запуска нужных классов, методов.

Все драйверы находятся по пути ```xthreading/driverRun/``` и здесь файлы к примеру ```xthreading/driverRun/driverOpenCart.php``` в данном случае у нас имеется драйвер ```driverOpenCart.php``` настроенный для работы с движком (OpenCart-version 2).

###### Входные данные в драйвер
В драйвер приходят 2 глобальные переменные ```$routerX``` и ```$configX```  
 1.```$routerX```  -Будет содержать строковый тип из установочного метода ```->setRoute('sale/order/test2')```
 2. ```$configX``` -Содержит массив всех настроек из файла  ```xthreading/xconfig.php```

###### Выходные данные из драйвера
Цель драйвера определить правильно глобальную переменную ```$transferOutData``` вот так она выглядит:
```
$transferOutData = [
      'action'          => [
                             [file] => '/home/alex/Workspace/testGAMESTIL/gamestil/admin_HprKrCJp2ng2fvjRsFfb/controller/sale/order.php',
                             [class] => 'Controllersaleorder',
                             [method] => 'test2'
                           ],
      'construct_data' => (Здесь любые типы данных для вызываемого класса точнее для метода __construct()).
];
 ```
В ключ ```$transferOutData['construct_data']``` = В случае с OpenCart требуется передать переменную ```$registry```
<br>

 Выглядит драйвер следующим образом(Можно копировать и модифицировать для дальнейших драйверов ):
```
<?php

/******************************************************************************************************
 * Цель драйвера -Загрузить движок текущего проекта(Сайта) и определить глобальную переменую => $action
 * -> Входные(input) глобальные перемены:
 *    1.$routerX - полный путь роуетра как задан методом $this->xthreading->setRoute()
 *    2.$configX - Все необходимые конфиги.
 * 
 * 
 * <- Выходная(output) глобальная перемена пример: 
 * $transferOutData = [
 *       'action'  => array(
 *                         [file] => /home/alex/Workspace/testGAMESTIL/gamestil/admin_HprKrCJp2ng2fvjRsFfb/controller/sale/order.php
 *                         [class] => Controllersaleorder
 *                         [method] => test2
 *                        ),
 * 
 *       'construct_data' => array and string.
 * ];
 * 
 * !! $transferOutData['action']         - (Обязательный)!!>Передайте (путь запускающего файла), (Запускающий класс), (Запускающий метод).
 * !! $transferOutData['construct_data'] - (Необязательный)!!>Передайте конструктору класса если требуется. В Опен-карт это $registry
 *****************************************************************************************************/


/*----------(input data)------------> */
$routerX = $routerX; //-Роутер.
$configX = $configX; //-Конфиг.
/*----------------------------------> */



/***************************************************************************************
 * Загрузка всех конфигов и служб движка Open-Cart.
 * !!!===> Настройте драйвер под Вашу систему(Движок, сайта). 
 ***************************************************************************************/
if (isset($routerX) && !isset($_GET['model'])) $_GET['model'] = $routerX;
// Подключаем файл конфигурации проекта(Приложения).
if (is_file($configX['dir_application'] . 'config.php')) {
    require_once($configX['dir_application'] . 'config.php');
}
//-Подключаем движок проекта.
require_once(DIR_SYSTEM . 'startup.php');
// Registry
$registry = new Registry();
// Config
$config = new Config();
$registry->set('config', $config);
// Database
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db', $db);
// Settings
$query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0'");
foreach ($query->rows as $setting) {
    if (!$setting['serialized']) {
        $config->set($setting['key'], $setting['value']);
    } else {
        $config->set($setting['key'], unserialize($setting['value']));
    }
}

// Подключаем загрузчики проекта.
$loader = new Loader($registry);
$registry->set('load', $loader);
// Url
$url = new Url(HTTP_SERVER, $config->get('config_secure') ? HTTPS_SERVER : HTTP_SERVER);
$registry->set('url', $url);
// Log
$log = new Log($config->get('config_error_filename'));
$registry->set('log', $log);

// Request
$request = new Request();
$registry->set('request', $request);
// Response
$response = new Response();
$response->addHeader('Content-Type: text/html; charset=utf-8');
$registry->set('response', $response);
// Cache
$cache = new Cache('file');
$registry->set('cache', $cache);
//Session
$session = new Session();
$registry->set('session', $session);
// Language
$languages = array();
$query = $db->query("SELECT * FROM `" . DB_PREFIX . "language`");
foreach ($query->rows as $result) {
    $languages[$result['code']] = $result;
}

$config->set('config_language_id', $languages[$config->get('config_admin_language')]['language_id']);
// Language
$language = new Language($languages[$config->get('config_admin_language')]['directory']);
$language->load('default');
$registry->set('language', $language);
// Document
$registry->set('document', new Document());
// Currency
$registry->set('currency', new Currency($registry));
// Weight
$registry->set('weight', new Weight($registry));
// Length
$registry->set('length', new Length($registry));
// User
$registry->set('user', new User($registry));
// OpenBay Pro
$registry->set('openbay', new Openbay($registry));
// Dir creator
$registry->set('dir', new Dircreator($registry));

// Event
$event = new Event($registry);
$registry->set('event', $event);
$query = $db->query("SELECT * FROM " . DB_PREFIX . "event");
foreach ($query->rows as $result) {
    $event->register($result['trigger'], $result['action']);
}
// Action
$action = [];
$path = '';

/*********
 * (Определить файл, класс, метод в $action).
 */
$parts = explode('/', str_replace('../', '', (string)$routerX));
foreach ($parts as $part) {
    $path .= $part;
    if (is_dir(DIR_APPLICATION . 'controller/' . $path)) {
        $path .= '/';
        array_shift($parts);
        continue;
    }
    $file = DIR_APPLICATION . 'controller/' . str_replace(array('../', '..\\', '..'), '', $path) . '.php';
    if (is_file($file)) {
        $action['file'] = $file;
        $action['class'] = 'Controller' . preg_replace('/[^a-zA-Z0-9]/', '', $path);
        array_shift($parts);
        break;
    }
}
$method = array_shift($parts);
($method) ? $action['method'] = $method : 'index';



/* <<<---- Выходные(output data) данные ---------- */
$transferOutData = [ //-Требуется определить.
    'action'         => $action,
    'construct_data' => $registry
];
/* <<<-------------------------------------------- */


/* !!! Нужно определить для дальнейшей работы диспетчера драйвера библиотеки (Xthreading):
------------------------------------------------------------------------------------------
 * <- Выходная переменая пример: 
 * $transferOutData = [
 *       'action'  => array(
 *                         [file] => /home/alex/Workspace/testGAMESTIL/gamestil/admin_HprKrCJp2ng2fvjRsFfb/controller/sale/order.php
 *                         [class] => Controllersaleorder
 *                         [method] => test2
 *                        ),
 * 
 *       'construct_data' => array and string.
 * ];
 * 
 * !! $transferOutData['action']         - (Обязательный)!!>Передайте (путь запускающего файла), (Запускающий класс), (Запускающий метод).
 * !! $transferOutData['construct_data'] - (Необязательный)!!>Передайте конструктуру класса если требуется. В Опен-карт это $registry
 */

```

<br><br>



