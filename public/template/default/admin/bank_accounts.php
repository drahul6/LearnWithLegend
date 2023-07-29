<?php
$page_title = 'E-wallets';
include 'includes/header.php'; ?>


<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-6  mb-2">
                <?php include 'includes/breadcrumb.php'; ?>

                <h3 class="content-header-title mb-0" style="display:inline;">E-wallets</h3>
            </div>

            <div class="content-header-right col-6">
                <div class="btn-group float-right" role="group" aria-label="Button group with nested dropdown">
                    <small class="float-right"><?= $note; ?></small>
                </div>
            </div>
        </div>
        <div class="content-body">

            <section id="video-gallery" class="card">
                <div class="card-header">

                    <?php include_once 'template/default/composed/filters/bank_accounts.php'; ?>
                    <h4 class="card-title" style="display: inline;"></h4>


                    <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                    <div class="heading-elements">
                        <ul class="list-inline mb-0">
                            <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                            <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <!-- <th>#sn</th> -->
                                    <th>#Id</th>
                                    <th>User</th>
                                    <th>Account</th>
                                    <th>Balance</th>
                                    <th>Opened</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <?php $i = 1;
                            foreach ($bank_accounts as $bank_account) :

                                $balance = $bank_account->get_balance();


                            ?>
                                <tr>
                                    <!-- <td><?= $i; ?> </td> -->
                                    <td><?= $bank_account->id; ?> </td>
                                    <td>
                                        <?= $bank_account->owner->DropSelfLink ?? 'N/A'; ?>
                                    </td>
                                    <td><?= $bank_account->account_name; ?><br>
                                        <!-- Acc No:<?= $bank_account->account_number ?? 'N/A'; ?><br> -->
                                        <small>
                                            <code>CType: <?= $bank_account->custom_account_type->name ?? 'N/A'; ?></code><br>
                                            <code>Basic:<?= $bank_account->custom_account_type->basic_account->name ?? 'N/A'; ?></code><br>
                                            <code>No:<?= $bank_account->account_number ?? 'N/A'; ?></code><br>
                                        </small>
                                    </td>


                                    <td>
                                        Bal: <?= $bank_account->CurrencySymbol; ?><?= $balance['account_currency']['balance']; ?> <br>
                                        Avail Bal: <?= $bank_account->CurrencySymbol; ?><?= $balance['account_currency']['available_balance']; ?>
                                    </td>


                                    <td><span class="badge badge-secondary"><?= date('M j, Y h:i:A', strtotime($bank_account->created_at)); ?></span>
                                    </td>
                                    <td>
                                        <a class="" href="<?= domain; ?>/admin/transactions/<?= $bank_account->id; ?>">Transactions</a>
                                        <!--   <div class="dropdown">
                          <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-toggle="dropdown">
                          </button>
                          <div class="dropdown-menu">
                          </div>
                        </div> -->

                                    </td>
                                </tr>

                            <?php $i++;
                            endforeach; ?>


                            </tbody>
                        </table>


                    </div>
                </div>
            </section>

            <ul class="pagination">
                <?= $this->pagination_links($data, $per_page); ?>
            </ul>

        </div>
    </div>
</div>
<!-- END: Content-->


<div id="new_category_app"></div>

<?php include 'includes/footer.php'; ?>