<link rel="stylesheet" type="text/css" href="<?=$asset;?>/css/bootstrap.min.css">

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


<div class="container">
<div class="col-md-12 col-12 ">
            <!-- using a bootstrap card -->
            <div class="card">
                <!-- card body -->
                <div class="card-body p-2">
                  
                    <!-- invoice logo and title -->
                    <div class="invoice-logo-title row py-2">
                        <div class="col-6 d-flex flex-column justify-content-center align-items-start">
                            <h4 class="text-dark text-uppercase">Statement of Account</h4>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td>Account Type:</td>
                                    <td class="text-uppercase"><?=$chart_of_account->account_name;?></td>
                                </tr>
                                <tr>
                                    <td>Account Number:</td>
                                    <td><?=$chart_of_account->account_number;?></td>
                                </tr>
                                <tr>
                                    <td>Statement Date:</td>
                                    <td><?=date("Y-m-d");?></td>
                                </tr>
                                <tr>
                                    <td>Period Covered:</td>
                                    <td><?=$transactions['date_note'];?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-6 d-flex justify-content-end invoice-logo">
                            <img src="<?=$logo;?>" alt="company-logo" height="46" width="164">
                        </div>
                    </div>
                    <hr style="margin: 0px;">

                    <!-- invoice address and contacts -->
                    <div class="row invoice-adress-info py-2">
                        <div class="col-6 mt-1 from-info">
                            <div class="info-title mb-1">
                                <span><?=$chart_of_account->owner->fullname;?></span>
                            </div>
                            <div class="company-name mb-1">
                                <span class="text-muted">
                                    <?php

                                        use v2\Models\Wallet\ChartOfAccount;

                                $details = $chart_of_account->owner->DetailsArray;?>
                                </span>
                            </div>
                            <div class="company-address mb-1">
                                <span class="text-muted"><?=$chart_of_account->owner->fullAddress;?></span>
                                <!-- <span class="text-muted">9205 Whitemarsh Street New York, NY 10002</span> -->
                            </div>
                            <div class="company-email  mb-1 mb-1">
                                <!-- <span class="text-muted"><?=$chart_of_account->owner->email;?></span> -->
                            </div>
                            <div class="company-phone  mb-1">
                                <!-- <span class="text-muted"><?=$chart_of_account->owner->phone;?></span> -->
                            </div>
                        </div>




                        <div class="col-6 mt-1 to-info">
                           <table class="table table-sm table-borderless">
                            <thead>
                                <tr>
                                    <th scope="col" colspan="2" class="text-uppercase">Account Summary</th>
                                </tr>
                            </thead>

                                <tbody>
                                    
                               <tr>
                                   <td>Begining Balance at <?=$transactions['journal_date']['start_date'];?></td>
                                   <td><?=$chart_of_account->CurrencySymbol;?><?=$transactions['balance_bf'];?></td>
                               </tr>
                               <tr>
                                   <td>Total Credit</td>
                                   <td><?=$chart_of_account->CurrencySymbol;?><?=MIS::money_format($transactions['total_credit']);?></td>
                               </tr>
                               <tr>
                                   <td>Total Debit:</td>
                                   <td><?=$chart_of_account->CurrencySymbol;?><?=MIS::money_format($transactions['total_debit']);?></td>
                               </tr>
                               <tr>
                                   <td>Closing Balance at <?=$transactions['journal_date']['end_date'];?></td>
                                   <td><?=$chart_of_account->CurrencySymbol;?><?=MIS::money_format($transactions['closing_balance']);?></td>
                               </tr>
                                </tbody>
                           </table>

                        </div>
                    </div>

                    <!--product details table -->
                    <div class="product-details-table py-2 table-responsive">

                        <!-- <table class="table table-borderles table-striped table-dar" > -->
                            <table autosize="1" class="" width="100%">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">REF</th>
                                    <th style="text-align: left;">DATE</th>
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
                                    <td colspan="2" style="text-align: center;">Balance B/F</td>
                                    <td style="text-align: right;"><?= ($transactions['balance_bf']); ?></td>

                                </tr>
                                <?php


                                foreach ($transactions['transactions'] as  $transaction) :
                                    $second_leg = $transaction;
                                    ?>
                                    <tr>
                                        <td style="text-align: center;"><?= $transaction->journal_id; ?></td>
                                        <td style="text-align: left;"><?= date("Y-m-d", strtotime($transaction->journal->journal_date)); ?></td>
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
                                    <td style="text-align: right;"><strong>Total</strong></td>
                                    <td style="text-align: right;"><?= MIS::money_format(array_sum($debits ?? [])); ?></td>
                                    <td style="text-align: right;"><?= MIS::money_format(array_sum($credits ?? [])); ?></td>
                                    <td></td>

                                </tr>
                            </tbody>
                        </table>


                    </div>
                    <hr>
                    <p><small>
                        * This is <?=project_name;?>
                    </small></p>
                </div>
            </div>
        </div>
        </div>