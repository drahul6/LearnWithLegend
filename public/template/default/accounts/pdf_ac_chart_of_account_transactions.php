<!-- <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"> -->

<style>
    table tbody tr:nth-child(even) {
        background: lightgray !important;
    }

    table tbody tr td,
    table thead tr td {
        padding: 5px;

    }

    table tbody tr,
    table thead tr {
        line-height: 15px;
    }


    table thead th {
        background-color: grey;
        text-align: right;
        padding: 10px;
    }
</style>

<div class="container">

            <div class="row">
                <div class="col-sm-12 text-danger">
                    <h4><strong><?= $this->company->name; ?></strong></h4>
                </div>
            </div>

            <div class="section-tint super-shadow">

                <div class="row">
                    <div class="col-sm-12">

                        <div class="">

                            <div class="row">
                                <div class="col-xs-8">
                                    <h3><?= $chart_of_account->account_name; ?></h3>
                                    <small><?=$transactions['date_period'] ?? '';?></small>
                                    <small><?=$transactions['note'];?></small>

                                </div>



                            </div>
                        </div>
                    </div>
                    <hr>
                    <table autosize="1" class="" width="100%">
                        <thead>
                            <tr>
                                <th style="text-align: center;">REF</th>
                                <th style="text-align: center;">TRANS DATE</th>
                                <th style="text-align: center;">VALUE DATE</th>
                                <th style="text-align: left;">DESCRIPTION</th>
                                <th style="text-align: right;">DEBITS</th>
                                <th style="text-align: right;">CREDITS</th>
                                <th style="text-align: right;">BALANCE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td colspan="2" style="text-align: center;">Balance B/F</td>
                                <td style="text-align: right;"><?= ($transactions['balance_bf']); ?></td>

                            </tr>
                            <?php


                            foreach ($transactions['transactions'] as  $transaction) :
                                $second_leg = $transaction;
                                ?>
                                <tr>
                                    <td style="text-align: center;"><?= $transaction->journal_id; ?></td>
                                    <td style="text-align: center;"><?= date("Y-m-d", strtotime($transaction->journal->updated_at)); ?></td>
                                    <td style="text-align: center;"><?= date("Y-m-d", strtotime($transaction->journal->journal_date)); ?></td>
                                    <td style="text-align: left;">
                                        <?= $transaction->description; ?>
                                    </td>

                                   <td style="text-align: right;"> <?= MIS::money_format($debits[] = $transaction->debit ?? 0); ?></td>
                                   <td style="text-align: right;"><?= MIS::money_format($credits[] = $transaction->credit ?? 0); ?></td>
                                   <td style="text-align: right;"> <?= $transaction->formattedPostBalance; ?></td>

                                </tr>
                            <?php endforeach; ?>

                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="text-align: right;"><strong>Total</strong></td>
                                <td style="text-align: right;"><?= MIS::money_format(array_sum($debits ?? [])); ?></td>
                                <td style="text-align: right;"><?= MIS::money_format(array_sum($credits ?? [])); ?></td>
                                <td></td>

                            </tr>
                        </tbody>
                    </table>
                    <?php include 'inc/footers.php'; ?>
                </div>
            </div>


        </div>
            <style>
            

                .table>tbody>tr>.emptyrow {
                    border-top: none;
                }

                .table>thead>tr>.emptyrow {
                    border-bottom: none;
                }

                .table>tbody>tr>.highrow {
                    border-top: 3px solid;
                }
            </style>


