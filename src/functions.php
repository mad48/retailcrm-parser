<?php

// вспомогательные функции
// ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

// проверка подключения
function checkConnection($client)
{
    try {
        $response = $client->usersList();
    } catch (\RetailCrm\Exception\CurlException $e) {
        return "Connection error: " . $e->getMessage();
    }
    if (isset($response['errorMsg'])) {
        return $response['errorMsg'];
    }
}


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


// Returns used memory (either in percent (without percent sign) or free and overall in bytes)
function getServerMemoryUsage($getPercentage=true)
{
    $memoryTotal = null;
    $memoryFree = null;

    if (stristr(PHP_OS, "win")) {
        // Get total physical memory (this is in bytes)
        $cmd = "wmic ComputerSystem get TotalPhysicalMemory";
        @exec($cmd, $outputTotalPhysicalMemory);

        // Get free physical memory (this is in kibibytes!)
        $cmd = "wmic OS get FreePhysicalMemory";
        @exec($cmd, $outputFreePhysicalMemory);

        if ($outputTotalPhysicalMemory && $outputFreePhysicalMemory) {
            // Find total value
            foreach ($outputTotalPhysicalMemory as $line) {
                if ($line && preg_match("/^[0-9]+\$/", $line)) {
                    $memoryTotal = $line;
                    break;
                }
            }

            // Find free value
            foreach ($outputFreePhysicalMemory as $line) {
                if ($line && preg_match("/^[0-9]+\$/", $line)) {
                    $memoryFree = $line;
                    $memoryFree *= 1024;  // convert from kibibytes to bytes
                    break;
                }
            }
        }
    }
    else
    {
        if (is_readable("/proc/meminfo"))
        {
            $stats = @file_get_contents("/proc/meminfo");

            if ($stats !== false) {
                // Separate lines
                $stats = str_replace(array("\r\n", "\n\r", "\r"), "\n", $stats);
                $stats = explode("\n", $stats);

                // Separate values and find correct lines for total and free mem
                foreach ($stats as $statLine) {
                    $statLineData = explode(":", trim($statLine));

                    //
                    // Extract size (TODO: It seems that (at least) the two values for total and free memory have the unit "kB" always. Is this correct?
                    //

                    // Total memory
                    if (count($statLineData) == 2 && trim($statLineData[0]) == "MemTotal") {
                        $memoryTotal = trim($statLineData[1]);
                        $memoryTotal = explode(" ", $memoryTotal);
                        $memoryTotal = $memoryTotal[0];
                        $memoryTotal *= 1024;  // convert from kibibytes to bytes
                    }

                    // Free memory
                    if (count($statLineData) == 2 && trim($statLineData[0]) == "MemFree") {
                        $memoryFree = trim($statLineData[1]);
                        $memoryFree = explode(" ", $memoryFree);
                        $memoryFree = $memoryFree[0];
                        $memoryFree *= 1024;  // convert from kibibytes to bytes
                    }
                }
            }
        }
    }

    if (is_null($memoryTotal) || is_null($memoryFree)) {
        return null;
    } else {
        if ($getPercentage) {
            return (100 - ($memoryFree * 100 / $memoryTotal));
        } else {
            return array(
                "total" => $memoryTotal,
                "free" => $memoryFree,
            );
        }
    }
}

function getNiceFileSize($bytes, $binaryPrefix=true) {
    if ($binaryPrefix) {
        $unit=array('B','KiB','MiB','GiB','TiB','PiB');
        if ($bytes==0) return '0 ' . $unit[0];
        return @round($bytes/pow(1024,($i=floor(log($bytes,1024)))),2) .' '. (isset($unit[$i]) ? $unit[$i] : 'B');
    } else {
        $unit=array('B','KB','MB','GB','TB','PB');
        if ($bytes==0) return '0 ' . $unit[0];
        return @round($bytes/pow(1000,($i=floor(log($bytes,1000)))),2) .' '. (isset($unit[$i]) ? $unit[$i] : 'B');
    }
}
