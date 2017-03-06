<?php
function echo_table($data, $error)
{
    //pre($error);
    echo '<br><br><table class="table table-hover">
        <thead>
            <tr>
                <th>Номер</th>
                <th>Статус заказ</th>
                <th>Оплата</th>
                <th>Тип оплаты</th>
                <th>Оплачено</th>
                <th>Дата оплаты</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($data as $order) {
        echo '<tr>
                <td>' . $order['number'] . '</td>';
        echo '<td class="';
        echo($error[$order["number"]]["status"] ? " alert-danger" : $error[$order["number"]]["status"]);
        echo '"><span  data-toggle="tooltip"  title="' . $error[$order["number"]]["status"] . '">' . $order['status'] .
            '</span></td>';

        echo '<td class="';
        echo($error[$order["number"]]["paymentStatus"] ? " alert-danger" : $error[$order["number"]]["paymentStatus"]);
        echo '"><span  data-toggle="tooltip"  title="' . $error[$order["number"]]["paymentStatus"] . '">' . $order['paymentStatus'] .
            '</span></td>';

        echo '<td class="';
        echo($error[$order["number"]]["paymentType"] ? " alert-danger" : $error[$order["number"]]["paymentType"]);
        echo '"><span  data-toggle="tooltip"  title="' . $error[$order["number"]]["paymentType"] . '">' . $order['paymentType'] .
            '</span></td>';

        echo '<td class="';
        echo($error[$order["number"]]["oplacheno"] ? " alert-danger" : $error[$order["number"]]["oplacheno"]);
        echo '"><span  data-toggle="tooltip"  title="' . $error[$order["number"]]["oplacheno"] . '">' . $order['oplacheno'] .
            '</span></td>';

        echo '<td class="';
        echo($error[$order["number"]]["dataoplat"] ? " alert-danger" : $error[$order["number"]]["dataoplat"]);
        echo '"><span  data-toggle="tooltip"  title="' . $error[$order["number"]]["dataoplat"] . '">' . $order['dataoplat'] .
            '</span></td>';

        echo '</tr>';
    }
    echo '</tbody>
</table>';

    echo '<script type="text/javascript">
    $(function () {
        $(\'[data-toggle="tooltip"]\').tooltip({placement: \'top\', html: \'true\'})
    })
</script>';
}

?>



