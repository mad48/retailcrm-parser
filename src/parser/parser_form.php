
<?php if (empty($_POST)) { ?>
    <link href="/vendor/kartik-v/bootstrap-fileinput/css/fileinput.min.css" media="all" rel="stylesheet"
          type="text/css"/>
    <script src="/vendor/kartik-v/bootstrap-fileinput/js/fileinput.min.js"></script>
    <script src="/vendor/kartik-v/bootstrap-fileinput/themes/fa/theme.js"></script>
    <script src="/vendor/kartik-v/bootstrap-fileinput/js/locales/ru.js"></script>

    <form class="form-inline" enctype="multipart/form-data" action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">

        <input type='hidden' name='sessid' value='<?= $_SESSION['sessid'] ?>'>

        <label class="control-label">Выберите файл</label>
        <input id="excel" name="excel" type="file" class="file-loading">
        <div id="errorBlock" class="help-block"></div>
        <script>
            $(document).on('ready', function () {
                $("#excel").fileinput({
                    showPreview: false,
                    language: "ru",
                    allowedFileExtensions: ["xls", "xlsx"],
                    elErrorContainer: "#errorBlock"
                });
            });
        </script>

    </form>

<? } ?>


<?php if (!empty($_POST)) { ?>
    <button type="button" class="btn btn-primary" onclick="window.location.href='<? echo $_SERVER['PHP_SELF'] ?>'">
        Назад
    </button>
	<br><br>
<? } ?>
