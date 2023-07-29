<?php include 'inc/headers.php'; ?>

<div class="row">
    <div class="col-sm-8">
        <h3><?= $chart_of_account->account_name; ?> <small class="badge badge-dark"><?= $chart_of_account->currency; ?></small></h3>
        <?= $chart_of_account->custom_account_type->name; ?>/<?= $chart_of_account->custom_account_type->basic_account->name; ?><br>
        <small><?= $chart_of_account->owner->DropSelfLink ?? ''; ?></small>
        <small> <?= $transactions['note']; ?></small>
        <br>
        <small> <?= $transactions['date_period'] ?? ''; ?></small>
    </div>


    <div class="col-sm-4 text-right">
        <div class="btn-group">
            <div class="btn-group">

                <button type="button" class="btn btn-white dropdown-toggle" data-toggle="dropdown">
                    Export As <span class="caret"></span></button>
                <ul class="dropdown-menu" role="menu">
                    <a class="dropdown-item" href="<?= domain; ?>/accounts/export_transaction_to_pdf/<?= $chart_of_account->id; ?>">PDF
                    </a>

                    <!-- <a class="dropdown-item" href="<?= domain; ?>/accounts/export_transaction_to_csv/<?= $chart_of_account->id; ?>">CSV
                        </a> -->

                </ul>
            </div>
        </div>

    </div>
</div>

<hr>
<table id="charts_of_accounts_table" class="table table-hover">
    <thead>
        <tr>
            <th>REF</th>
            <th>TRANS DATE</th>
            <th>VALUE DATE</th>
            <th>DESCRIPTION</th>
            <th>DEBITS</th>
            <th>CREDITS</th>
            <th>BALANCE</th>
            <th>AVAIL BAL</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td>Balance B/F</td>
            <td></td>
            <td></td>
            <td><?= ($transactions['balance_bf']); ?></td>
            <td></td>

        </tr>
        <?php


        foreach ($transactions['transactions'] as  $transaction) :
            $second_leg = $transaction;
        ?>
            <tr>
                <td><?= $transaction->journal_id; ?></td>
                <td><?= date("Y-m-d", strtotime($transaction->journal->updated_at)); ?></td>
                <td><?= date("Y-m-d", strtotime($transaction->journal->journal_date)); ?></td>
                <td><?= $transaction->journal->publishedState; ?><br><strong><?= $second_leg->chart_of_account->account_name; ?></strong>
                    <br><?= $transaction->description; ?>
                </td>

                <td> <?= MIS::money_format($debits[] = $transaction->debit ?? 0); ?></td>
                <td><?= MIS::money_format($credits[] = $transaction->credit ?? 0); ?></td>
                <td> <?= $transaction->formattedPostBalance; ?></td>
                <td> <?= $transaction->post_available_balance; ?></td>

            </tr>
        <?php endforeach; ?>

        <tr>
            <td></td>
            <td></td>
            <td colspan="2" class="text-center"><strong>Total</strong></td>
            <td><?= MIS::money_format(array_sum($debits ?? [])); ?></td>
            <td><?= MIS::money_format(array_sum($credits ?? [])); ?></td>
            <td></td>
            <td></td>

        </tr>
    </tbody>
</table>



<div>
    <nav class="pagination">
        <?= $this->pagination_links($transactions['data'], $per_page); ?>
    </nav>
</div>


<?php include 'inc/footers.php'; ?>