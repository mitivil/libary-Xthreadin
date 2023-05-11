<?php

/******************************************************************************************************
 * Цель драйвера -Загрузить движок текущего проекта(Сайта) и определить глобальную переменую => $action
 * -> Входные(input) глобальные переменые:
 *    1.$routerX - полный путь роуетра как задан методом $this->xthreading->setRoute()
 *    2.$configX - Все необходимые конфиги.
 * 
 * 
 * <- Выходная(output) глобальная переменая пример: 
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
