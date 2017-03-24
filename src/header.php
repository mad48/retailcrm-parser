<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="images/favicon.ico" rel="icon" type="image/x-icon"/>
    <title><?= basename($_SERVER['SCRIPT_NAME'], ".php") ?> - retailCRM</title>

    <!--<script type='text/javascript' src='http://code.jquery.com/jquery-3.1.1.js'></script>-->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css">

    <!-- Optional theme -->
    <link rel="stylesheet" href="vendor/twbs/bootstrap/dist/css/bootstrap-theme.min.css">

    <!-- Latest compiled and minified JavaScript -->
    <script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="css/style.css" type="text/css"/>
</head>
<body>

<script>
    $(document).ready(function () {
        var height = $("body").height();
        $("body,html").animate({"scrollTop": height}, "slow");
    });
</script>


<div class="prel"><img  src="../images/preloader.gif"/></div>


<script>
    $(window).load(function () {
        $('.prel').fadeOut(0);
        $('.mydv').css('display', 'block');
    });
</script>


<div class="container mydv" style="display:none">
    <div class="row">

        <nav class="navbar navbar-default">
            <!-- Бренд и переключатель, который вызывает меню на мобильных устройствах -->
            <div class="navbar-header">
                <!-- Кнопка с полосочками, которая открывает меню на мобильных устройствах -->
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                        data-target="#main-menu" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <!-- Бренд или логотип фирмы (обычно содержит ссылку на главную страницу) -->
                <a href="#" class="navbar-brand">Парсер XLS</a>
            </div>
            <!-- Содержимое меню (коллекция навигационных ссылок, формы и др.) -->
            <div class="collapse navbar-collapse" id="main-menu">
                <!-- Список ссылок, расположенных слева -->
                <ul class="nav navbar-nav">
                    <!--Элемент с классом active отображает ссылку подсвеченной -->
                    <li class="active"><a href="#">Файл <span class="sr-only">(current)</span></a></li>
                    <!--                    <li><a href="#">Статьи</a></li>
                                        <li><a href="#">Новости</a></li>-->
                </ul>
                <!-- Список ссылок, расположенный справа -->
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="#" onclick="window.location.href='/'">Выйти</a></li>
                </ul>
            </div>
        </nav>
