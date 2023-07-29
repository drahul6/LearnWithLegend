<?php

$page_title = "View Journal";
include 'includes/header.php';

use v2\Models\Wallet\ChartOfAccount;

$involved_accounts = $journal->involved_accounts;
$total_credit = $involved_accounts->sum('credit');
$total_debit = $involved_accounts->sum('debit');; ?>

<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <?php include 'includes/breadcrumb.php'; ?>

                <h3 class="content-header-title mb-0">View Journal</h3>
            </div>

            <!--  <div class="content-header-right col-md-6 col-12">
            <div class="btn-group float-md-right" role="group" aria-label="Button group with nested dropdown">
              <div class="btn-group" role="group">
                <button class="btn btn-outline-primary dropdown-toggle dropdown-menu-right" id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="ft-settings icon-left"></i> Settings</button>
                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1"><a class="dropdown-item" href="card-bootstrap.html">Bootstrap Cards</a><a class="dropdown-item" href="component-buttons-extended.html">Buttons Extended</a></div>
              </div><a class="btn btn-outline-primary" href="full-calender-basic.html"><i class="ft-mail"></i></a><a class="btn btn-outline-primary" href="timeline-center.html"><i class="ft-pie-chart"></i></a>
            </div>
          </div> -->
        </div>
        <div class="content-body">

            <section id="video-gallery" class="card">
                <div class="card-content">
                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-12">

                                <div class="col-md-12">
                                    <div class="btn-group">
                                        <?php if ($journal->is_editable()) : ?>
                                            <a href="<?= domain; ?>/admin/edit_journal/<?= $journal->id; ?>" type="button" class="btn btn-outline-dark">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        <?php endif; ?>

                                        <div class="btn-group">
                                            <button type="button" class="btn btn-outline-dark dropdown-toggle" data-toggle="dropdown">
                                                <i class="fa fa-paperclip"></i> <span class="caret"></span></button>
                                            <ul class="dropdown-menu" role="menu">
                                                <?php foreach ($journal->attachments as $file) :
                                                    $filename = end(explode('/', $file));
                                                ?>
                                                    <li><a target="_blank" href="<?= domain; ?>/<?= $file; ?>"><?= $filename; ?></a></li>
                                                <?php endforeach; ?>

                                            </ul>
                                        </div>
                                        <a href="<?= domain; ?>/journals/new/admin" class="btn btn-outline-dark">
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
                                                    <strong>Amount:</strong>
                                                    <?= MIS::money_format($total_credit ?? 0); ?>
                                                    <br>
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
                                                        <?php foreach ($involved_accounts as  $account) :

                                                            $trace = $journal->is_published() ? "?journal[ref]=$journal->id" : '';


                                                        ?>
                                                            <tr>
                                                                <td>
                                                                    <a href="<?= domain; ?>/admin/transactions/<?= $account->chart_of_account->id; ?><?= $trace; ?>">

                                                                        <strong><?= $account->chart_of_account->account_name; ?>
                                                                            <span class="badge badge-dark"><?= $account->chart_of_account->currency; ?> </span></strong>
                                                                    </a>
                                                                    <br>
                                                                    <small>
                                                                        Account Holder: <?= $account->chart_of_account->owner->DropSelfLink ?? 'NA'; ?></small>
                                                                    <p><?= $account->description; ?></p>
                                                                </td>
                                                                <!-- <td class="text-right"></td> -->
                                                                <td class="text-right"> <?= ($account->a_debit ?? 0); ?></td>
                                                                <td class="text-right"> <?= ($account->a_credit ?? 0); ?></td>
                                                                <td class="text-right"> <?= MIS::money_format($account->debit ?? 0); ?></td>
                                                                <td class="text-right"> <?= MIS::money_format($account->credit ?? 0); ?></td>
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


                    </div>
                </div>
            </section>



        </div>
    </div>
</div>
<!-- END: Content-->

<?php include 'includes/footer.php'; ?>