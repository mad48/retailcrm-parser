<?php

// вспомогательные функции
// ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------


// no comments
function pre($param)
{
    echo "<pre>";
    print_r($param);
    echo "</pre>";
}

// оформление заголовков сообщений
function msg($mess, $tag = null, $style = null)
{
    $tag = is_null($tag) ? "h4" : $tag;
    if (strpos($style, ":")) echo "<" . $tag . " " . ($style ? "style=\"" . $style : "") . "\"" . ">$mess</$tag>";
    else echo "<" . $tag . " " . ($style ? "class=\"" . $style : "") . "\"" . ">$mess</$tag>";
}


// получение и распечатка массива элементов таблицей
function prntbl($obj, $arrname, $params = null)
{
    $arr = $obj[$arrname];

    if (count($arr) && !is_null($params)) {

        echo '<table>';
        echo '<tr>';
        foreach ($params as $param) {
            echo '<th>' . $param . '</th>';
        }
        echo "</tr>";

        foreach ($arr as $item) {
            echo "<tr>";
            foreach ($params as $param) {
                echo '<td>' . $item[$param] . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }

    return $arr;
}

function logger($message)
{
    $fd = fopen(basename($_SERVER['SCRIPT_NAME'], ".php") . ".log", 'a') or die("не удалось создать лог-файл");

    if (!empty($message)) {
        fputs($fd, date("Y-m-d H:i:s") . ";" . $message . "\r\n");
    } else {
        fputs($fd, "\r\n");
    }

    fclose($fd);
}


// получение списка допустимых статусов заказа
function getStatuses($client)
{
    $statuses = [];

    $statuses_arr = $client->StatusesList()['statuses'];
    //pre($paymentStatuses);

    foreach ($statuses_arr as $status) {
        $statuses[$status['name']] = $status['code'];
    }

    //msg("Допустимые статусы заказа");
    //pre($statuses);

    return $statuses;
}

// получение допустимых состояний оплаты заказа
function getPaymentStatuses($client)
{
    $statuses = [];

    $statuses_arr = $client->paymentStatusesList()['paymentStatuses'];

    foreach ($statuses_arr as $status) {
        $statuses[$status['name']] = $status['code'];
    }

    //msg("Допустимые статусы оплаты заказа");
    //pre($statuses);

    return $statuses;
}

// получение допустимых способов оплаты заказа
function getPaymentTypes($client)
{
    $statuses = [];

    $statuses_arr = $client->paymentTypesList()['paymentTypes'];

    foreach ($statuses_arr as $status) {
        $statuses[$status['name']] = $status['code'];
    }

    //msg("Допустимые типы оплаты заказа");
    //pre($types);

    return $statuses;
}

function convert($size)
{
    $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}

function getConfig($confname = 'retailcrm')
{
    //$confname -  идентификатор настроек подключения.  см. config.php

    if (!file_exists('config.php')) {
        $fd = fopen("config.php", 'w') or die("не удалось создать файл");
        $conf = "<?php
    return  [
        'retailcrm' => [
        
            // retailCRM URL (example: https://mysite.retailcrm.ru)
            'url' => '',
        
            // ключ доступа к api (example: OKCPewvhjjllNVjY0gW1dQkodjjyBq7BL) 
            'key' => ''
        ]
    ];";

        fwrite($fd, $conf);
        fclose($fd);
        msg("Создан config.php. Укажите URL и ключ API в config.php", "div", "alert alert-danger");
        die();
    }

    if (file_exists('config.php')) {
        $config = require 'config.php';

        if (empty($config[$confname]['key'])) {
            msg("Укажите ключ API в config.php", "div", "alert alert-danger");
            die();
        }

        return $config[$confname];
    }
    return false;
}


function getExcelFile()
{
    if (!isset($_FILES['excel'])) return false;
    else return $_FILES['excel'];
}


function saveExcelFileCopy($uploadsdir)
{
   // $uploadsdir = 'uploads';

    if (!is_dir($uploadsdir)) {
        if (!mkdir($uploadsdir, 0777, true)) {
            die('Не удалось создать директорию ' . $uploadsdir);
        }
    }

    $uploaddir = $uploadsdir;//__DIR__ . '/../' . $uploadsdir . '/';

    $info = pathinfo($_FILES['excel']['name']);

    if (!in_array(strtolower($info['extension']), ['xls', 'xlsx'])) {
        msg("Поддерживаемые типы файлов: XLS, XLSX");
        return false;
    }

    $filename = basename($_FILES['excel']['name'], '.' . $info['extension']);


    $uploadfile = $uploaddir . (new DateTime)->format('Y-m-d-H-m-s') . "_" . basename($_FILES['excel']['name']);

    if (file_exists($uploadfile)) {
        $uploadfile = $uploaddir . (new DateTime)->format('Y-m-d-H-m-s') . "_" . $filename . "." . $info['extension'];
    }

    if (move_uploaded_file($_FILES['excel']['tmp_name'], $uploadfile)) {
        msg("Файл был успешно загружен", "div", "alert alert-info");

    } else {
        msg("Проблемы с сохранением файла!", "div", "alert alert-danger");
        return false;
        die();
    }

    return $uploadfile;
}

function saveExcelFile($path)
{
    //if (!isset($_FILES['excel']['name'])) return false;

    $uploadsdir = 'uploads';

    if (!is_dir($uploadsdir)) {
        if (!mkdir($uploadsdir, 0777, true)) {
            die('Не удалось создать директорию ' . $uploadsdir);
        }
    }

    $uploaddir = __DIR__ . '/../' . $uploadsdir . '/';

    $info = pathinfo($_FILES['excel']['name']);

    if (!in_array(strtolower($info['extension']), ['xls', 'xlsx'])) {
        msg("Поддерживаемые типы файлов: XLS, XLSX");
        return false;
    }

    $filename = basename($_FILES['excel']['name'], '.' . $info['extension']);


    $uploadfile = $uploaddir . (new DateTime)->format('Y-m-d-H-m-s') . "_" . basename($_FILES['excel']['name']);

    if (file_exists($uploadfile)) {
        $uploadfile = $uploaddir . (new DateTime)->format('Y-m-d-H-m-s') . "_" . $filename . "." . $info['extension'];
    }

    if (move_uploaded_file($_FILES['excel']['tmp_name'], $uploadfile)) {
        msg("Файл был успешно загружен", "div", "alert alert-info");

    } else {
        msg("Проблемы с сохранением файла!", "div", "alert alert-danger");
        return false;
    }

    return $uploadfile;
}