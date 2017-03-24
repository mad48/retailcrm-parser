<?php
header('Content-Type: text/html; charset=utf-8');

session_start();

unset($_SESSION['auth']);
unset($_SESSION['auth_error']);
unset($_SESSION['debug']);

if (isset($_SESSION['auth'])) {
    header("location:parser.php");
}

if (isset($_POST['login'])) {

    require_once 'src/functions.php';
    $config = getConfig();
    if ($_POST['login'] == $config['login'] && $_POST['password'] == $config['password']) {
        $_SESSION['auth'] = 1;
        header("location: parser.php");
    } else {
        $_SESSION['auth_error'] = 1;

    }

}

?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="images/favicon.ico" rel="icon" type="image/x-icon"/>
    <title><?= basename($_SERVER['SCRIPT_NAME'], ".php") ?> - retailCRM</title>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <link rel="stylesheet" href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="vendor/twbs/bootstrap/dist/css/bootstrap-theme.min.css">
    <script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="css/style.css" type="text/css"/>

</head>
<body>

<div class="container">
    <div class="row">
        <div class="form">

            <form  id="authform" class="form-horizontal" role="form" action="" method="POST">
                <h3 style="text-align: right">Парсер XLS</h3>
                <div class="form-group">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label"></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" placeholder="Логин" name="login">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-2 control-label"></label>
                        <div class="col-sm-10">
                            <input type="password" class="form-control" placeholder="Пароль" name="password">
                        </div>
                    </div>
                    <!--            <div class="form-group">
                                    <div class="col-sm-offset-2 col-sm-10">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="not_attach_ip"> Не прикреплять к IP (не безопасно)
                                            </label>
                                        </div>
                                    </div>
                                </div>-->
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-default btn-sm pull-right">Войти</button>
                        </div>
                    </div>
            </form>
            <? if (isset($_SESSION['auth_error'])) echo '<div class="alert alert-danger">Логин или пароль указан не верно</div>'; ?>

        </div><!-- form  -->
        <script>document.forms.authform.login.focus();</script>
    </div>
</div>
</body>
</html>


