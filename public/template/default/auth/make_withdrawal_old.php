<?php
$page_title = "Make Withdrawal";
include 'includes/header.php';



use v2\Models\Withdrawal;

$balances = Withdrawal::payoutBalanceFor($auth->id);

?>

<div class="page-wrapper">

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Make Withdrawal</h3>
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
            <div class="col-5 align-self-center">
                <div class="customize-input float-right">
                    <div class="btn-group float-md-right" role="group" aria-label="Button group with nested dropdown">
                        <a class="btn btn-sm btn-outline-secondary" href="javascript:void(0);">
                            Avail Bal: <?= $currency; ?><?= MIS::money_format($balances['available_payout_balance']); ?>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <div class="container-fluid">

        <div class="card">
            <div class="card-header" data-toggle="collapse" data-target="#make_deposit">
                <div class="heading-elements">
                    <ul class="list-inline mb-0">
                        <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                        <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                    </ul>
                </div>

            </div>
            <div class="card-body collapse show" id="make_deposit">
                <div class="col-12">

                    <small>Payout: <?= $currency; ?><?= MIS::money_format($balances['payout_balance']); ?></small><br>
                    <small>Withdrawal Fee: <?= $balances['withdrawal_fee']; ?>% </small><br>
                    <small>Minimum Withdrawal: <?= $currency; ?><?= MIS::money_format($balances['min_withdrawal']); ?></small><br>
                    <hr>
                </div>
                <?php if ($balances['available_payout_balance'] > 0) : ?>

                    <form class="col-12 ajax_for" method="POST" action="<?= domain; ?>/withrawals/withdrawal_bonus_to_share">

                        <div class="form-group">
                            <label>Amount (<?= $currency; ?>)</label>
                            <input type="number" step="1" min="<?= $balances['min_withdrawal']; ?>" name="amount" required="" class="form-control">
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-outline-dark">Submit</button>
                        </div>

                    </form>
                <?php else : ?>

                    <div class="col-12">

                        <center>
                            You need <?= $currency; ?><?= MIS::money_format($balances['min_withdrawal']); ?> at least to be able to request a withdrawal.
                        </center>
                    </div>


                <?php endif; ?>

            </div>

        </div>




    </div>

    <?php include 'includes/footer.php'; ?>