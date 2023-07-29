<?php
$page_title = "Wallet";
include 'includes/header.php';



?>


<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-md-4 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Wallet</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <!--   <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="index-2.html" class="text-muted">Apps</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Ticket List</li>
                        </ol> -->
                    </nav>
                </div>
            </div>
            <div class="col-md-8 align-self-center">
                <div class="customize-input float-right">
                    <div class="btn-group">
                        <button class="btn btn-outline- custom-shadow custom-radius" data-toggle="modal" data-target="#fund">Fund</button>
                        <button class="btn btn-outline- custom-shadow custom-radius" data-toggle="modal" data-target="#transfer">Transfer</button>
                        <button class="btn btn-outline- custom-shadow custom-radius" data-toggle="modal" data-target="#withdrawal">Withdraw</button>
                        <button class="btn btn-outline- custom-shadow custom-radius" data-toggle="modal" data-target="#donate">Donate</button>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <div class="container-fluid">




        <div class="col-12" style="display:;">
            <div class="card-group">


                <div class="card border-right">
                    <div class="card-body">
                        <div class="d-flex d-lg-flex d-md-block align-items-center">
                            <div>
                                <div class="d-inline-flex align-items-center">
                                    <h2 class="text-dark mb-1 font-weight-medium"><?= ($balance['account_currency']['balance']); ?></h2>
                                </div>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Book Balance</h6>
                                <span class="badge bg-dark font-12 text-white font-weight-medium badge-pill  d-block"></span>
                            </div>
                            <div class="ml-auto mt-md-3 mt-lg-0">

                                <div class="ml-auto mt-md-3 mt-lg-0">
                                    <?= ($balance['account_currency']['currency']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-right">
                    <div class="card-body">
                        <div class="d-flex d-lg-flex d-md-block align-items-center">
                            <div>
                                <div class="d-inline-flex align-items-center">
                                    <h2 class="text-dark mb-1 font-weight-medium"><?= ($balance['account_currency']['available_balance']); ?></h2>
                                </div>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Available Balance</h6>
                                <span class="badge bg-success font-12 text-white font-weight-medium badge-pill  d-block"></span>
                            </div>
                            <div class="ml-auto mt-md-3 mt-lg-0">

                                <div class="ml-auto mt-md-3 mt-lg-0">
                                    <?= ($balance['account_currency']['currency']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <h4 class="card-title">History</h4>

            <div class="card">
                <div class="">
                    <div class=" list-group">

                        <?php if ($transactions['transactions']->isEmpty()) : ?>
                            <div class="text-center">Your history will show here</div>
                        <?php endif; ?>



                        <?php foreach ($transactions['transactions'] as $transaction) :

                        ?>
                            <li class="custom-shadow list-group-item d-fle justify-content-between align-items-center" style="padding: 10px;">
                                <span class="float-left">
                                    #<?= $transaction->id; ?><?= @$transaction->journal->payment_method ?? ''; ?>
                                    <br><small> <?= date("M j, Y, H:i", strtotime($transaction->journal->created_at)); ?></small><br>
                                    <small> <?= $transaction->notes ?? $transaction->tag; ?></small>
                                </span>
                                <span class="tx-20 float-right text-right"> <?= $transaction->journal->publishedState; ?><br>
                                    <?= $transaction->formattedAmounts['a_amount']; ?>
                                    <br> <span class="" style="font-size: 12px;">bal after:</span> <?= ($balance['account_currency']['currency_symbol']); ?><?= $transaction->a_post_available_balance; ?></span>
                            </li>

                        <?php endforeach; ?>

                    </div>
                </div>
            </div>
            <ul class="pagination pagination-sm">
                <?= $this->pagination_links($transactions['data'], $transactions['per_page']); ?>
            </ul>


        </div>
    </div>





    <div class="modal" id="withdrawal">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Withdraw</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">


                    <div class="">
                        <div class="col-12">
                            <small>Withdrawal Fee: <?= $withdrawal_fee_percent; ?>% </small><br>
                            <small>Minimum Withdrawal: <?= $currency; ?><?= MIS::money_format($min_withdrawal_usd); ?></small><br>
                            <hr>
                        </div>
                        <?php if ($balance['account_currency']['available_balance'] >= $min_withdrawal_usd) : ?>

                            <form class="col-12 ajax_form" method="POST" action="<?= domain; ?>/withdrawals/submit_withdrawal_request">


                                <?= $this->csrf_field(); ?>

                                <div class="form-group">
                                    <label>Amount (<?= $currency; ?>)</label>
                                    <input type="number" step="1" min="<?= $min_withdrawal_usd; ?>" max="<?= $balance['account_currency']['available_balance']; ?>" name="amount" required="" class="form-control">
                                </div>


                                <div class="form-group">
                                    <label>Select Method</label> <small><a href="<?= domain; ?>/user/withdrawal-methods">Add withdrawal method</a></small>
                                    <select class="form-control" required="" name="method">
                                        <option value="">Select Payment method</option>
                                        <?php foreach (v2\Models\UserWithdrawalMethod::ForUser($auth->id)->get() as $key => $option) : ?>
                                            <option value="<?= $option->id; ?>"><?= v2\Models\UserWithdrawalMethod::$method_options[$option['method']]['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>


                                <?= $this->use_2fa_protection(); ?>


                                <div class="form-group">
                                    <button type="submit" class="btn btn-outline-dark">Submit</button>
                                </div>

                            </form>

                        <?php else : ?>

                            <div class="col-12">

                                <center>
                                    <p>You need <?= $currency; ?><?= MIS::money_format($min_withdrawal_usd); ?> at least to be able to request a withdrawal.</p>
                                </center>
                            </div>


                        <?php endif; ?>

                    </div>



                </div>

            </div>
        </div>
    </div>


    <div class="modal" id="transfer">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Transfer</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <form class="col-12 ajax_form" method="POST" action="<?= domain; ?>/user/submit_user_transfers">
                        <?= $this->csrf_field(); ?>
                        <small>Transfer Fee: <?= $transfer_fee; ?>% </small><br>
                        <small>Minimum Transfer : <?= $currency; ?> <?= $min_transfer; ?> </small>
                        <hr>

                        <div class="form-group">
                            <label>Amount to Transfer (<?= $currency; ?>)</label>
                            <input type="number" step="1" min="<?= $min_transfer; ?>" max="<?= $balance['account_currency']['available_balance']; ?>" required="" name="amount" class="form-control">
                        </div>



                        <div class="form-group">
                            <label>To Username</label>
                            <input type="text" min="" required="" name="username" class="form-control">
                        </div>

                        <?= $this->use_2fa_protection(); ?>


                        <div class="form-group">
                            <button type="submit" class="btn btn-outline-dark">Transfer</button>
                        </div>

                    </form>


                </div>

            </div>
        </div>
    </div>

    <div class="modal" id="donate">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Donate to Month's Pool</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <form class="col-12 ajax_form" method="POST" action="<?= domain; ?>/user/donate_to_pool">
                        <?= $this->csrf_field(); ?>
                        <small>Minimum Donation : <?= $currency; ?> <?= $min_donation; ?> </small>

                        <hr>
                        <div class="form-group">
                            <label>Amount to Donate (<?= $currency; ?>)</label>
                            <input type="number" step="1" min="<?= $min_donation; ?>" max="<?= $balance['account_currency']['available_balance']; ?>" required="" name="amount" class="form-control">
                        </div>


                        <?= $this->use_2fa_protection(); ?>

                        <div class="form-group">
                            <button type="submit" class="btn btn-outline-dark">Donate</button>
                        </div>

                    </form>


                </div>

            </div>
        </div>
    </div>



    <div class="modal" id="fund" style="">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Deposit</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>


                <!-- Modal body -->
                <div class="modal-body">
                    <form class="ajax_form" method="POST" action="<?= domain; ?>/user/initiate_deposit" data-function="initiate_payment">
                        <small>Minimum Deposit: <?= $currency; ?><?= MIS::money_format($min_deposit); ?></small><br>
                        <hr />

                        <div class="form-group">
                            <label>Amount (<?= $currency; ?>)</label>
                            <input type="number" step="1" min="<?= $min_deposit; ?>" name="amount" required="" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Select Processor</label>
                            <select class="form-control" name="payment_method" require="">
                                <option value="">Select Payment method</option>
                                <?php
                                $payment_methods = array_filter($shop->get_available_payment_methods(), function ($item) {
                                    return !in_array($item['name'], ['wallet', 'Wallet']);
                                });



                                foreach ($payment_methods as $key => $option) : ?>
                                    <option value="<?= $key; ?>"><?= $option['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-outline-dark">Deposit</button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>



    <script>
        initiate_payment = function($data) {
            console.log($data);

            const queryString = new URLSearchParams($data);

            switch ($data.gateway) {
                case 'rave':
                    payWithRave($data);
                    break;

                case 'paypal':
                    // code block
                    window.location.href = `${$base_url}/shop/checkout?${queryString}`
                    break;
                case 'stripe':
                    // code block
                    window.location.href = `${$base_url}/shop/checkout?${queryString}`
                    break;

                default:
                    // code block
                    break;
            }
        }
    </script>





    <?php include 'includes/footer.php'; ?>