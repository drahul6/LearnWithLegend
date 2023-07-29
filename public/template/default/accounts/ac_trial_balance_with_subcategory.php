<?php

use v2\Models\Wallet\ChartOfAccount;
use v2\Models\Wallet\BasicAccountType;
use v2\Models\Wallet\CompanyAccountType;

include 'inc/headers.php'; ?>

<div class="row">

    <div class="col-md-6">

    </div>
    <div class="col-md-6 text-right">
        <div class="btn-group">
            <div class="btn-group">
                <button type="button" class="btn btn-white dropdown-toggle" data-toggle="dropdown">
                    Export As <span class="caret"></span></button>
                <ul class="dropdown-menu" role="menu">
                    <a class="dropdown-item" href="<?= domain; ?>/trial-balance/export_to_pdf">PDF</a>
                    <!-- <a class="dropdown-item" href="<?= domain; ?>/trial-balance/export_to_csv">CSV</a> -->
                </ul>
            </div>
        </div>

    </div>
    <div class="col-md-12">
        <hr>
        <h5 class="text-center"><?= $this->company->name; ?></h5>
        <h4 class="text-center">Trial Balance</h4>
        <h5 class="text-center">As at <?= date("M j, Y", strtotime($as_of_date)); ?>

        </h5>

        <div class="col-md-12">

            <hr>

        </div>
        <div class="col-md-12">
            <div class="panel panel-default">
                <!-- <div class="panel-heading">
                                        <h3 class="text-center"><strong>Order summary</strong></h3>
                                    </div> -->
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <td class="text-"><strong>Code</strong></td>
                                    <td><strong>Account</strong></td>
                                    <td class="text-center"><strong>Debits</strong></td>
                                    <td class="text-right"><strong>Credits</strong></td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sorted_into_subcategories as $basic_account_id => $subcategories) :
                                    $basic_account = BasicAccountType::find($basic_account_id);
                                ?>

                                    <tr>
                                        <td class="text-">
                                            <b><?= $basic_account->code; ?></b>
                                        </td>
                                        <td>
                                            <strong>
                                                <?= $basic_account['name']; ?>
                                            </strong>
                                        </td>
                                        <td class="text-center"> </td>
                                        <td class="text-right"> </td>
                                    </tr>

                                    <?php foreach ($subcategories as $subcategory_id => $details) :
                                        $sub_category = CompanyAccountType::find($subcategory_id);

                                    ?>
                                        <tr>
                                            <td class="text-">
                                                <!-- <b><?= $basic_account->code; ?></b> -->
                                            </td>
                                            <td>
                                                <strong style="margin-left: 20px;">
                                                    <?= $sub_category['name']; ?>
                                                </strong>
                                            </td>
                                            <td class="text-center"> </td>
                                            <td class="text-right"> </td>
                                        </tr>
                                        <?php foreach ($details as $account_id => $account) : ?>
                                            <tr>
                                                <td class="text- text-info">
                                                    <?= $account['account_code']; ?>
                                                </td>
                                                <td class="text-info">
                                                    <strong class="text-info" style="margin-left: 40px;">
                                                        #<?= $account['id']; ?> <?= $account['account_name']; ?>
                                                    </strong>
                                                </td>
                                                <td class="text-center">
                                                    <?php
                                                    $debits[] = $account['raw_debit_balance'] ?? 0;
                                                    echo $account['debit_balance'] ?? 0; ?></td>
                                                <td class="text-right">
                                                    <?php

                                                    $credits[] = $account['raw_credit_balance'] ?? 0;

                                                    echo $account['credit_balance'] ?? 0; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>



                                <tr>
                                    <td class="emptyrow text-center">
                                    </td>
                                    <td class="emptyrow  text-center"><strong>Total</strong></td>
                                    <td class="emptyrow text-center">
                                        <?= ChartOfAccount::account_format(array_sum($debits)); ?></td>
                                    <td class="emptyrow text-right">
                                        <?= ChartOfAccount::account_format(array_sum($credits)); ?></td>
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