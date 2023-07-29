<?php
$page_title = "Transfer";
include 'includes/header.php';



$rules_settings =  SiteSettings::find_criteria('rules_settings');
$transfer_fee = $rules_settings->settingsArray['user_transfer_fee_percent'];
$min_transfer = $rules_settings->settingsArray['min_transfer_usd'];; ?>

<div class="page-wrapper">

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Transfer</h3>
                <!-- 
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="">Blank</a>
                                    </li>
                                </ol>
                            </nav>
                        </div> -->
            </div>
            <!-- <div class="col-5 align-self-center">
                        <div class="customize-input float-right">
                            <select class="custom-select custom-select-set form-control bg-white border-0 custom-shadow custom-radius">
                                <option selected>Aug 19</option>
                                <option value="1">July 19</option>
                                <option value="2">Jun 19</option>
                            </select>
                        </div>
                    </div> -->
        </div>
    </div>


    <div class="container-fluid">

        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <form method="POST" action="<?= domain; ?>/user/submit_user_transfers">
                        <?= $this->csrf_field(); ?>
                        <small>Deposit Wallet: <?= $currency; ?><?= MIS::money_format($deposit_balance); ?></small><br>
                        <small>Transfer Fee: <?= $transfer_fee; ?>% </small><br>
                        <small>Minimum Transfer : <?= $currency; ?> <?= $min_transfer; ?> </small>
                        <hr>

                        <div class="form-group">
                            <label>Amount to Transfer (<?= $currency; ?>)</label>
                            <input type="number" step="1" min="<?= $min_transfer; ?>" required="" name="amount" class="form-control">
                        </div>


                        <div class="form-group">
                            <label>From Wallet</label>
                            <select class="form-control" required="" name="wallet">
                                <option value="">Select Wallet</option>
                                <?php foreach ($wallet->available_wallets($auth) as $key => $option) : ?>
                                    <option value="<?= $key; ?>"><?= $option['name']; ?> &nbsp;&nbsp; (<?= $currency; ?><?= $option['balance']; ?>) </option>
                                <?php endforeach; ?>
                            </select>
                        </div>


                        <div class="form-group">
                            <label>To Username</label>
                            <input type="text" min="" required="" name="username" class="form-control">
                        </div>


                        <div class="form-group">
                            <button type="submit" class="btn btn-outline-dark">Transfer</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>


    </div>

    <?php include 'includes/footer.php'; ?>