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
function msg($mess, $tag = null, $style= null)
{
    $tag = is_null($tag) ? "h4" : $tag;
    if(strpos($style, ":")) echo "<".$tag." ". ($style? "style=\"".$style:"")."\"".">$mess</$tag>";
    else echo "<".$tag." ". ($style? "class=\"".$style:"")."\"".">$mess</$tag>";
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

    if(!empty($message)){
        fputs($fd, date("Y-m-d H:i:s") . ";" . $message . "\r\n");
    }else{
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
