<?php
$page_title = 'Transactions';
include 'includes/header.php';

$balance = $chart_of_account->get_balance();

?>


<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-6  mb-2">
                <?php include 'includes/breadcrumb.php'; ?>

                <h3 class="content-header-title mb-0" style="display:inline;">Transactions</h3>
            </div>




            <div class="content-header-right col-6">
                <div class="btn-group float-right" role="group" aria-label="Button group with nested dropdown">
                    <small class="float-right"> <?= $transactions['note']; ?></small>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="row match-heigh">
                <div class="col-xl-4 col-lg-6 col-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="media align-items-stretch">
                                <div class="p-2 text-center bg-success bg-darken-2">
                                    <i class="ft-briefcase font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-gradient-x-success white media-body">
                                    <h5> Acct. No.: <?= $chart_of_account->account_number; ?></h5>
                                    <p class="text-bold-400 mb-0"><i class="ft-c"></i>
                                        <?= $chart_of_account->account_name; ?>
                                    </p>
                                    <small><?= $chart_of_account->custom_account_type->name; ?>
                                        <?= $chart_of_account->custom_account_type->basic_account->name; ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-6 col-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="media align-items-stretch">
                                <div class="p-2 text-center bg-success bg-darken-2">
                                    <i class="ft-briefcase font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-gradient-x-success white media-body">
                                    <small>Book Bal:</small>
                                    <?= $chart_of_account->CurrencySymbol; ?><?= $balance['account_currency']['balance']; ?>
                                    <br>
                                    <small>Avail Bal:</small>
                                    <?= $chart_of_account->CurrencySymbol; ?><?= $balance['account_currency']['available_balance']; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-6 col-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="media align-items-stretch">
                                <div class="p-2 text-center bg-dark bg-darken-2">
                                    <i class="ft-user font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-gradient-x- white media-body">
                                    <h5 style="color: white!important"> <?= $chart_of_account->owner->DropSelfLink; ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            Period: <?= $transactions['date_note']; ?>

            <section id="video-gallery" class="card">
                <div class="card-header">

                    <?php include_once 'template/default/composed/filters/transactions.php'; ?>
                    <h4 class="card-title" style="display: inline;"></h4>


                    <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                    <!-- <div class="heading-elements">
                   <div class="btn-group">
                       <div class="btn-group">

                           <button type="button" class="btn btn-outline-dark btn-sm dropdown-toggle" data-toggle="dropdown">
                               Export As <span class="caret"></span>
                             </button>
                           <ul class="dropdown-menu" role="menu">
                                   <a class="dropdown-item" href="<?= $chart_of_account->ExportLink; ?>">PDF 
                                   </a></ul>
                       </div>
                   </div>

                </div> -->
                </div>
                <div class="card-content">
                    <div class="card-body table-responsive">

                        <table id="charts_of_accounts_table" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>REF</th>
                                    <th>TRANS DATE</th>
                                    <th>VALUE DATE</th>
                                    <th>DESCRIPTION</th>
                                    <th>DEBITS</th>
                                    <th>CREDITS</th>
                                    <th>BOOK BAL</th>
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
                                    <td><?= ($transactions['available_balance_bf']); ?></td>

                                </tr>
                                <?php


                                foreach ($transactions['transactions'] as  $transaction) :
                                    $second_leg = $transaction;
                                ?>
                                    <tr>
                                        <td><a href="<?= domain; ?>/admin/view_journal/<?= $transaction->journal_id; ?>"><?= $transaction->journal_id; ?></a></td>
                                        <td><?= date("Y-m-d", strtotime($transaction->journal->updated_at)); ?></td>
                                        <td><?= date("Y-m-d", strtotime($transaction->journal->journal_date)); ?></td>
                                        <td><?= $transaction->journal->publishedState; ?>
                                            <!-- <br><strong><?= $second_leg->chart_of_account->account_name; ?></strong> -->
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

                                </tr>
                            </tbody>
                        </table>


                    </div>
                </div>
            </section>

            <ul class="pagination">
                <?= $this->pagination_links($transactions['data'], $per_page); ?>
            </ul>

        </div>
    </div>
</div>
<!-- END: Content-->


<div id="new_category_app"></div>

<?php include 'includes/footer.php'; ?>