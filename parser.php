<?php
/**
 * Парсер Ecxel (скрипт изменения данных в CRM используя файл excel).
 */

require 'vendor/autoload.php';
require 'config.php';

require 'src/functions.php';
require 'src/header.php';

require 'src/parser/parser_form.php';
require 'src/parser/parser_table.php';


define("DEBUG", false);

// идентификатор настроек подключения.  см. config.php
$crm = "retailcrm2";
$config = file_exists('config-dev.php') ? require_once 'config-dev.php' : require_once 'config.php';

$client = new \RetailCrm\ApiClient(
    $config[$crm]['url'],
    $config[$crm]['key']
);

if (!empty($con_err = checkConnection($client))) {
    msg("Проверьте настройки подключения в файле config.php. " . $con_err, "h4", "alert alert-danger");
    die();
}


logger("");
logger("Вызов скрипта parser");

function getExcelFile()
{
    if (!isset($_FILES['excel']['name'])) return false;

    $uploaddir = __DIR__ . '/uploads/';

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
        msg("Файл корректен и был успешно загружен", "h4", "alert alert-info");

    } else {
        msg("Проблемы с сохранением файла!", "h4", "alert alert-danger");
        return false;
    }

    return $uploadfile;
}


$excelfile = getExcelFile();

if (DEBUG) $excelfile = __DIR__ . '/uploads/order-01-03-17.10-01_5c1c36.xls';

if (isset($_FILES) && !$excelfile) {
    exit();//"Нет файла для обработки"
} else {

}

logger("Файл: " . $excelfile);

if (DEBUG) echo '<br>Memory usage before PHPExcel: ', convert(memory_get_usage());

$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
$cacheSettings = array('memoryCacheSize' => '512MB');
PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

$inputFileType = PHPExcel_IOFactory::identify($excelfile);  // узнаем тип файла
$objReader = PHPExcel_IOFactory::createReader($inputFileType); // создаем объект для чтения файла
//$objReader->setReadDataOnly(true);
//$objReader->setReadFilter(new MyReadFilter1());

$objPHPExcel = $objReader->load($excelfile);

if (DEBUG) echo '<br>Memory usage after loading xls: ', convert(memory_get_usage());

$objWorksheet = $objPHPExcel->getActiveSheet();

$highestRow = $objWorksheet->getHighestRow(); // e.g. 10
$highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'

// Increment the highest column letter
$highestColumn++;

$data = [];

for ($row = 2; $row <= $highestRow; ++$row) {

    $number = $objWorksheet->getCell("A" . $row)->getValue();

    $data[$number]['number'] = $number;
    $data[$number]['status'] = $objWorksheet->getCell("B" . $row)->getValue();
    $data[$number]['paymentStatus'] = $objWorksheet->getCell("C" . $row)->getValue();
    $data[$number]['paymentType'] = $objWorksheet->getCell("D" . $row)->getValue();
    $data[$number]['oplacheno'] = $objWorksheet->getCell("E" . $row)->getValue();
    $data[$number]['dataoplat'] = $objWorksheet->getCell("F" . $row)->getValue();

}


$objPHPExcel->disconnectWorksheets();
unset($objPHPExcel);
if (DEBUG) echo '<br>Memory usage after unset PHPExcel: ', convert(memory_get_usage());

//pre($data);


// получение списка допустимых статусов заказа
$statuses = getStatuses($client);
// получение допустимых состояний оплаты заказа
$paymentStatuses = getPaymentStatuses($client);
// получение допустимых способов оплаты заказа
$paymentTypes = getPaymentTypes($client);

$error = [];
// проверка содержимого файла на ошибки
foreach ($data as $oder) {

    // проверка допустимых статусов заказа
    if (!array_key_exists($oder['status'], $statuses)) {
        $error[$oder['number']]['status'] = "Заказ " . $oder['number'] . ". Ошибка в имени статуса заказа. Статус <b>" . $oder['status'] . "</b> в CRM отсутствует";
        //msg($error[$oder['number']]['status'], "div");
    }

    // проверка допустимых состояний оплаты заказа
    if (!array_key_exists($oder['paymentStatus'], $paymentStatuses)) {
        $error[$oder['number']]['paymentStatus'] = "Заказ " . $oder['number'] . ". Ошибка в имени статуса оплаты заказа. Статус оплаты <b>" . $oder['paymentStatus'] . "</b> в CRM отсутствует";
        //msg($error[$oder['number']]['paymentStatus'], "div");
    }

    // проверка допустимых способов оплаты заказа
    if (!array_key_exists($oder['paymentType'], $paymentTypes)) {
        $error[$oder['number']]['paymentType'] = "Заказ " . $oder['number'] . ". Ошибка в имени способа оплаты заказа. Способ оплаты <b>" . $oder['paymentType'] . "</b> в CRM отсутствует";
        //msg($error[$oder['number']]['paymentType'], "div");
    }

    // проверка суммы на числовое значение
    if (!empty($oder['oplacheno']) && !is_numeric($oder['oplacheno'])) {
        $error[$oder['number']]['oplacheno'] = "Заказ " . $oder['number'] . ". Ошибка в поле \"Оплачено\". <b>" . $oder['oplacheno'] . "</b> не числовое значение";
        //msg($error[$oder['number']]['oplacheno'], "div");
    }

    // проверка даты
    $date = $oder['dataoplat'];
    if ($date != "") {

        if (is_numeric($date) && $date != 0) {
            $date = date("d.m.Y", PHPExcel_Shared_Date::ExcelToPHP($date));
            $oder['dataoplat'] = $date;
            $data[$oder['number']]['dataoplat'] = $date; // для отображения в таблице
        }

        $date_arr = explode(".", $date);
        if (!empty($date) && !checkdate((int)$date_arr[1], (int)$date_arr[0], (int)$date_arr[2])) {
            $error['number']['dataoplat'] = "Заказ " . $oder['number'] . ". Ошибка в поле \"Дата оплаты\". <b>" . $oder['dataoplat'] . "</b> Недопустимая дата";
            //msg($error['number']['dataoplat'], "div");
        }

    }


}//end foreach

echo_table($data, $error);

if (!empty($error)) {
    msg("Импорт файла невозможен из-за наличия в нем ошибок.", "h4", "alert alert-warning");
    logger("Импорт файла невозможен из-за наличия в нем ошибок." . print_r($error, true));
    die();
}

$error_import = [];

// если нет ошибок
if (empty($error)) {

    // обновление параметров заказа в CRM
    foreach ($data as $oder) {

        $orderlist = $client->ordersList(['numbers' => [$oder['number']]]);
        $oder['id'] = $orderlist['orders'][0]['id'];

        if (DEBUG) {
            echo '<pre><br>$orderedit = $client->ordersEdit(';
            echo ' <br>[';
            echo '<br>&nbsp;&nbsp;"id" => ' . $oder['id'] . ',';
            echo '<br>&nbsp;&nbsp;"status" => ' . $statuses[$oder['status']] . ',';
            echo '<br>&nbsp;&nbsp;"paymentStatus => ' . $paymentStatuses[$oder['paymentStatus']] . ',';
            echo '<br>&nbsp;&nbsp;"paymentType" => ' . $paymentTypes[$oder['paymentType']] . ',';
            echo '<br>&nbsp;&nbsp;"customFields" => [';
            echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;"oplacheno" => ' . $oder['oplacheno'] . ',';
            echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;"dataoplat" => ' . ($oder['dataoplat'] != "" ? date("Y-m-d", strtotime($oder['dataoplat'])) : "");
            echo '<br>&nbsp;&nbsp;]';
            echo '<br>], id);';
            echo '<br></pre>';
        }

        if ($oder['dataoplat'] != "") {
            $orderedit = $client->ordersEdit(
                [
                    'id' => $oder['id'],
                    "status" => $statuses[$oder['status']],
                    "paymentStatus" => $paymentStatuses[$oder['paymentStatus']],
                    "paymentType" => $paymentTypes[$oder['paymentType']],
                    "customFields" => [
                        "oplacheno" => $oder['oplacheno'],
                        "dataoplat" => ($oder['dataoplat'] != "" ? date("Y-m-d", strtotime($oder['dataoplat'])) : "")
                    ]
                ], 'id');

        } else {

            $orderedit = $client->ordersEdit(
                [
                    'id' => $oder['id'],
                    "status" => $statuses[$oder['status']],
                    "paymentStatus" => $paymentStatuses[$oder['paymentStatus']],
                    "paymentType" => $paymentTypes[$oder['paymentType']],
                    "customFields" => [
                        "oplacheno" => $oder['oplacheno']
                    ]
                ], 'id');
        }

        // pre($orderedit);

        if ($orderedit->isSuccessful()) {
            if (DEBUG) {
                msg("Заказ " . $oder['number'] . " успешно обновлен", "div");
            }
            logger("Заказ " . $oder['number'] . " успешно обновлен");
        } else {
            $error_import[$oder['number']]['id'] = $oder['id'];
            $error_import[$oder['number']]['code'] = $orderedit->getStatusCode();
            $error_import[$oder['number']]['msg'] = $orderedit->getErrorMsg();
            if (DEBUG) {
                msg("Заказ " . $oder['number'] . ". Ошибка импорта. " . implode(" : ", $error_import[$oder['number']]), "div", "color: red");
            }
            logger("Заказ " . $oder['number'] . ". Ошибка импорта. " . implode(" : ", $error_import[$oder['number']]));
        }


    }


}

if (!empty($error_import)) {
    foreach ($error_import as $error) {
        msg("Заказ " . $error['id'] . ". Ошибка импорта. " . implode(" : ", $error), "h4", "alert alert-danger");
        logger("Заказ " . $error['id'] . ". Ошибка импорта. " . implode(" : ", $error));
    }
} else {
    msg("Импорт успешно завершен", "h4", "alert alert-success");
    logger("Импорт успешно завершен");
}

?>
