<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="css/favicon.ico" rel="icon" type="image/x-icon"/>
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

<div class="container">
    <div class="row">
        <br><br>