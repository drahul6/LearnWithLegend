<?php include 'inc/headers.php'; ?>

<?php

use v2\Models\Wallet\ChartOfAccount;

$involved_accounts = $journal->involved_accounts;
$total_credit = $involved_accounts->sum('credit');
$total_debit = $involved_accounts->sum('debit');; ?>
<div class="row">

    <div class="col-md-12">

        <div class="col-md-12">
            <div class="btn-group">
                <?php if ($journal->is_editable()) : ?>
                    <a href="<?= $journal->editLink; ?>" type="button" class="btn btn-white">
                        <i class="fa fa-edit"></i>
                    </a>
                <?php endif; ?>

                <div class="btn-group">
                    <button type="button" class="btn btn-white dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-paperclip"></i> <span class="caret"></span></button>
                    <ul class="dropdown-menu" role="menu">
                        <?php foreach ($journal->attachments as $file) :
                            $filename = end(explode('/', $file));
                        ?>
                            <li><a target="_blank" href="<?= domain; ?>/<?= $file; ?>"><?= $filename; ?></a></li>
                        <?php endforeach; ?>

                    </ul>
                </div>
                <a href="<?= domain; ?>/journals/new" class="btn btn-white">
                    + New Journal
                </a>
            </div>
            <hr>
            <div class="row">

                <div class="col-xs-12 col-md-6 ">
                    <div class="panel panel-default height">
                        <div class="panel-heading"><?= $journal->publishedState; ?></div>
                        <div class="panel-body">
                            <strong>Created By:</strong> <?= $journal->accountant->fullname ?? 'System'; ?> at <?= date("M j, Y h:ia", strtotime($journal->created_at)); ?><br>
                            <strong>Notes:</strong> <?= $journal->notes; ?><br>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-6  pull-right">
                    <div class="panel panel-default height">
                        <div class="panel-heading">Journal #<?= $journal->id; ?></div>
                        <div class="panel-body">
                            <strong>Date:</strong> <?= $journal->journal_date; ?><br>
                            <strong>Journal Amount:</strong><?= $journal->currency; ?><?= MIS::money_format($journal->c_amount ?? 0); ?><br>
                            <strong>Amount:</strong><?= ChartOfAccount::$base_currency; ?><?= MIS::money_format($total_credit ?? 0); ?><br>
                            <!-- <strong>Reference Number:</strong> <?= $journal->reference; ?><br> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <!-- <h3 class="text-center"><strong>Order summary</strong></h3> -->
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <td><strong>Account</strong></td>
                                    <!-- <td class="text-right"><strong>Tax</strong></td> -->
                                    <td class="text-right"><strong>Debits </strong></td>
                                    <td class="text-right"><strong>Credits</strong></td>
                                    <td class="text-right"><strong>Debits(<?= ChartOfAccount::$base_currency; ?>)</strong></td>
                                    <td class="text-right"><strong>Credits(<?= ChartOfAccount::$base_currency; ?>)</strong></td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($involved_accounts as  $line) : ?>
                                    <tr>
                                        <td>
                                            #<?= $line->chart_of_account->id; ?> <i class="fa fa-user"></i>
                                            <?= $line->chart_of_account->owner->fullname ?? ''; ?>
                                            <br>
                                            <strong>
                                                <?= $line->chart_of_account->account_name; ?>
                                                <span class="badge badge-dark"><?= $line->chart_of_account->currency; ?> </span>
                                            </strong>
                                            <p><?= $line->description; ?></p>
                                        </td>
                                        <!-- <td class="text-right"></td> -->
                                        <td class="text-right"> <?= ($line->a_debit ?? 0); ?></td>
                                        <td class="text-right"> <?= ($line->a_credit ?? 0); ?></td>
                                        <td class="text-right"> <?= MIS::money_format($line->debit ?? 0); ?></td>
                                        <td class="text-right"> <?= MIS::money_format($line->credit ?? 0); ?></td>
                                    </tr>
                                <?php endforeach; ?>

                                <tr>
                                    <td class="highrow"></td>
                                    <!-- <td class="highrow text-right"></td> -->
                                    <td class="highrow text-right"></td>
                                    <td class="highrow text-right"><strong>Subtotal (<?= ChartOfAccount::$base_currency; ?>)</strong></td>
                                    <td class="highrow text-right"><?= MIS::money_format($total_debit ?? 0); ?></td>
                                    <td class="highrow text-right"><?= MIS::money_format($total_credit ?? 0); ?></td>
                                </tr>

                                <tr>
                                    <td class="emptyrow"></td>
                                    <!-- <td class="highrow text-right"></td> -->
                                    <td class="highrow text-right"></td>
                                    <td class="emptyrow text-right"><strong>Total (<?= ChartOfAccount::$base_currency; ?>)</strong></td>
                                    <td class="emptyrow text-right">
                                        <?= MIS::money_format($total_debit ?? 0); ?></td>
                                    <td class="emptyrow text-right">
                                        <?= MIS::money_format($total_credit ?? 0); ?></td>
                                </tr>


                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .height {
                min-height: 200px;
            }

            .icon {
                font-size: 47px;
                color: #5CB85C;
            }

            .iconbig {
                font-size: 77px;
                color: #5CB85C;
            }

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






    </div>
</div>


<?php include 'inc/footers.php'; ?>