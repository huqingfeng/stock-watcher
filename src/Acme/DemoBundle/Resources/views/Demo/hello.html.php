
        <link rel="stylesheet" href="lib/css/bootstrap.css">
        <script src="lib/javascript/jquery-1.11.2.min.js"></script>
        <script src="lib/javascript/bootstrap.js"></script>


        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.6/css/jquery.dataTables.min.css"></style>
        <script type="text/javascript" src="https://cdn.datatables.net/1.10.6/js/jquery.dataTables.min.js"></script>


        <script type="text/javascript">
            $(function() {
                $('#fileTable').DataTable();

            })
        </script>

        <style type="text/css">
            body {
                margin: auto;
                width: 980px;
            }

        </style>


        <h3 class="alert alert-success">Current Stock Information</h1>

        <table class="table">
            <tr>
                <th>Symbol</th>
                <th>Current Price</th>
                <th>Last Day</th>
                <th>Last Week</th>
                <th>Last Month</th>
                <th>Last Three Month</th>
                <th>Last Six Month</th>
                <th>Last Year</th>
            </tr>

            <?php foreach($stockRecords as $stockRecord) : ?>
            <tr>
                <td><?php echo $stockRecord['symbol'] ?></td>
                <td><?php echo '$'.$stockRecord['current_price'] ?></td>
                <?php foreach($timeInterval as $time) : ?>
                    <?php if(isset($stockRecord[$time]) && $stockRecord[$time]['decrease_ratio'] > 0) : ?>
                    <?php $stockRecord[$time]['decrease_ratio'] = '+'.$stockRecord[$time]['decrease_ratio'] ?>
                    <?php endif ?>

                <td class="<?php echo isset($stockRecord[$time]) && $stockRecord[$time]['risk'] ? 'label label-warning' : '' ?>">
                    <?php echo isset($stockRecord[$time]) ? '$'.$stockRecord[$time]['last_price'] : '' ?>
                    <?php echo isset($stockRecord[$time]) ? '('.$stockRecord[$time]['decrease_ratio'].'%)' : '' ?>
                </td>

                <?php endforeach ?>
            </tr>

            <?php endforeach ?>

        </table>

   

