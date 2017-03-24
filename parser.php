<?php
session_start();
if (!isset($_SESSION['auth'])) header("location: index.php");

/**
 * Парсер Excel (скрипт изменения данных в CRM используя файл excel).
 */

require_once 'vendor/autoload.php';

require_once 'src/functions.php';
require_once 'src/header.php';

if (isset($_GET['debug'])) {
    if ($_GET['debug'] == 'true') $_SESSION['debug'] = true;
    if ($_GET['debug'] == 'false') $_SESSION['debug'] = false;
}

if (isset($_SESSION['debug']) && $_SESSION['debug'] == true) define("DEBUG", true);
else define("DEBUG", false);

$config = getConfig();
if (!$config) {
    msg("Ошибка получения настроек из config.php", "div", "alert alert-danger");
    die();
}

$client = new \RetailCrm\ApiClient(
    $config['url'],
    $config['key']
);


/*$orderedit = $client->ordersEdit(
    [
        "id" => 34450,
        "customFields" => [
            "dataoplat" => '2015-11-14'
        ]
    ], 'id', 'nikitin.myprintbar.ru');
pre($orderedit);

$orderlist = $client->ordersList(['numbers' => [32977]]);
pre($orderlist);*/


require_once 'src/parser/parser_form.php';
require_once 'src/parser/parser_table.php';

logger("");
logger("Вызов скрипта parser");

$excelfilepath = "";

/*if (DEBUG) {
    if (DEBUG) $excelfilepath = __DIR__ . '/example.xls';
} else {*/
$excelfile = getExcelFile();
if (!$excelfile) {
    msg("Загрузите файл для обработки", "div", "alert alert-warning");
    die();
} else {
    $excelfilepath = saveExcelFileCopy(__DIR__ . "/uploads/");
}
//}


logger("Файл: " . $excelfilepath);

//if (DEBUG) echo '<br>Memory usage before PHPExcel: ', convert(memory_get_usage());

$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
$cacheSettings = array('memoryCacheSize' => '512MB');
PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

$inputFileType = PHPExcel_IOFactory::identify($excelfilepath);  // узнаем тип файла
$objReader = PHPExcel_IOFactory::createReader($inputFileType); // создаем объект для чтения файла
//$objReader->setReadDataOnly(true);
//$objReader->setReadFilter(new MyReadFilter1());

$objPHPExcel = $objReader->load($excelfilepath);

//if (DEBUG) echo '<br>Memory usage after loading xls: ', convert(memory_get_usage());

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
//if (DEBUG) echo '<br>Memory usage after unset PHPExcel: ', convert(memory_get_usage());

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

    // проверка существования заказа
    $orderlist = $client->ordersList(['numbers' => [$oder['number']]]);

    if (DEBUG) {
        echo '<a  data-toggle="collapse" data-target="#obefore' . $oder['number'] . '" style="cursor: pointer">Заказ ' . $oder['number'] . ' до внесения изменений</a><br>';
        echo '<div id="obefore' . $oder['number'] . '" class="collapse">';
        pre($orderlist);
        echo '</div>';

    }
    if (empty($orderlist['orders'])) {
        $error[$oder['number']]['number'] = "Заказ " . $oder['number'] . " не существует в CRM";
    }

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
            $error[$oder['number']]['dataoplat'] = "Заказ " . $oder['number'] . ". Ошибка в поле \"Дата оплаты\". <b>" . $oder['dataoplat'] . "</b> Недопустимая дата";
            //msg($error[$oder['number']]['dataoplat'], "div");
        }

    }


}//end foreach

msg("Содержимое файла:", "h4", "alert alert-info");
echo_table($data, $error);

if (!empty($error)) {
    msg("Импорт файла невозможен из-за наличия в нем ошибок", "h4", "alert alert-warning");
    logger("Импорт файла невозможен из-за наличия в нем ошибок" . print_r($error, true));
    die();
}

$error_import = [];

// если нет ошибок
if (empty($error)) {

    // обновление параметров заказа в CRM
    foreach ($data as $oder) {

        $orderlist = $client->ordersList(['numbers' => [$oder['number']]]);


        if (empty($orderlist['orders'])) {
            msg("Заказ " . $oder['number'] . " не существует в CRM. Импорт заказа пропущен", "div", "alert alert-warning");
            continue;
        } else {
            $oder['id'] = $orderlist['orders'][0]['id'];
            $oder['totalSumm'] = $orderlist['orders'][0]['totalSumm'];
            $oder['site'] = $orderlist['orders'][0]['site'];
        }
//&nbsp;&nbsp;"paymentType" => ' . $paymentTypes[$oder['paymentType']] . ',
        $orderedit_str = '
            number = ' . $oder['number'] . '
            $orderedit = $client->ordersEdit(
            [
            &nbsp;&nbsp;"id" => ' . $oder['id'] . ',
            &nbsp;&nbsp;"status" => ' . $statuses[$oder['status']] . ',
            &nbsp;&nbsp;"paymentStatus => ' . ($oder['oplacheno'] == $oder['totalSumm'] ? 'paid' : $paymentStatuses[$oder['paymentStatus']]) . ',
            &nbsp;&nbsp;"customFields" => [
            &nbsp;&nbsp;&nbsp;&nbsp;"paymenttype" => ' . $paymentTypes[$oder['paymentType']] . ',
            &nbsp;&nbsp;&nbsp;&nbsp;"givemeparser" => ' . $oder['oplacheno'] . ',
            &nbsp;&nbsp;&nbsp;&nbsp;"dataoplat" => ' . ($oder['dataoplat'] != "" ? date("Y-m-d", strtotime($oder['dataoplat'])) : "null") . '
            &nbsp;&nbsp;]
            ], id, ' . $oder['site'] . ');';


//"paymentType" => $paymentTypes[$oder['paymentType']],
        $orderedit = $client->ordersEdit(
            [
                "id" => $oder['id'],
                "status" => $statuses[$oder['status']],
                "paymentStatus" => ($oder['oplacheno'] == $oder['totalSumm'] ? 'paid' : $paymentStatuses[$oder['paymentStatus']]), //Если сумма в xls файле совпадает с суммой заказа, то выставлять статус оплаты "Оплачен".
                "customFields" => [
                    "paymenttype" => $paymentTypes[$oder['paymentType']],
                    "givemeparser" => $oder['oplacheno'],
                    "dataoplat" => ($oder['dataoplat'] != "" ? date("Y-m-d", strtotime($oder['dataoplat'])) : "null")
                ]
            ], 'id', $oder['site']);


        if (DEBUG) {
            $orderlist = $client->ordersList(['numbers' => [$oder['number']]]);
            echo '<a  data-toggle="collapse" data-target="#oafter' . $oder['number'] . '" style="cursor: pointer">Заказ ' . $oder['number'] . ' после внесения изменений</a><br>';
            echo '<div id="oafter' . $oder['number'] . '" class="collapse">';
            echo "<pre>" . $orderedit_str . "</pre>";
            pre($orderlist);
            echo '</div>';
        }

        if ($orderedit->isSuccessful()) {
            /*            if (DEBUG) {
                            msg("Заказ  number=" . $oder['number'] . " успешно обновлен", "div");
                        }*/
            logger("Заказ number=" . $oder['number'] . " успешно обновлен");
        } else {
            $error_import[$oder['number']]['number'] = $oder['number'];
            $error_import[$oder['number']]['id'] = $oder['id'];
            $error_import[$oder['number']]['code'] = $orderedit->getStatusCode();
            $error_import[$oder['number']]['msg'] = $orderedit->getErrorMsg();
            $error_import[$oder['number']]['site'] = $oder['site'];
            if (isset($orderedit['errors'])) $error_import[$oder['number']]['errors'] = $orderedit['errors'];

            if (DEBUG) {
                msg("Проблема с заказом number=" . $oder['number'], "h4", "alert alert-danger");
                logger("Проблема с заказом number=" . $oder['number']);
                //pre($orderedit_str);
                pre($orderedit);

            }
        }

    }


}

if (!empty($error_import)) {
    msg("Ошибки:", "h4", "alert alert-danger");
    foreach ($error_import as $error) {
        msg("Ошибка обновления заказа. Заказ number=" . $error['number'] . " id=" . $error['id'] . " site=" . $error['site'] . " code=" . $error['code'] . " msg=" . $error['msg'] . (!empty($error['errors']) ? " error=" . print_r($error['errors'], true) : ""), "h4", "alert alert-danger");
        logger("Ошибка обновления заказа. Заказ number=" . $error['number'] . " id=" . $error['id'] . " site=" . $error['site'] . " code=" . $error['code'] . " msg=" . $error['msg'] . (!empty($error['errors']) ? " error=" . print_r($error['errors'], true) : ""));
    }
} else {
    msg("Импорт успешно завершен", "h4", "alert alert-success");
    logger("Импорт успешно завершен");
}


?>
