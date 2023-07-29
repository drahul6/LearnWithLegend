<?php

use v2\Models\Wallet\Classes\AccountManager;

$page_title = "Dashboard";
include 'includes/header.php';



?>



<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Bread crumb and right sidebar toggle -->
    <!-- ============================================================== -->
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-md-2">
                <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Hi <?= $auth->username; ?>!</h3>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="">Dashboard</a>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-md-10">
                <div class="aler  alert-dismissibl" style="border:1px solid #efefef ;height: 100px;">


                    <?= CMS::fetch('dashboard_ad_banner'); ?>

                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <!-- *************************************************************** -->
        <!-- Start First Cards -->
        <!-- *************************************************************** -->
        <div class="card-body">

            <div class="">

                <div class="alert alert-secondary alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= CMS::fetch('dashboard_message'); ?>
                </div>

            </div>

            <div class="card-group">


                <div class="card border-right">
                    <div class="card-body">
                        <div class="d-flex d-lg-flex d-md-block align-items-center">
                            <div>
                                <div class="d-inline-flex align-items-center">
                                    <h2 class="text-dark mb-1 font-weight-medium">
                                        <?= ($default_wallet->get_balance()['account_currency']['available_balance']); ?>
                                    </h2>
                                </div>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate"><?= $default_wallet->currency; ?> <?= $default_wallet->account_name; ?>
                                </h6>
                            </div>
                            <div class="ml-auto mt-md-3 mt-lg-0">
                                <span class="opacity-7 text-muted"><i data-feather="database"></i></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-right">
                    <div class="card-body">
                        <div class="d-flex d-lg-flex d-md-block align-items-center">
                            <div>
                                <div class="d-inline-flex align-items-center">
                                    <h2 class="text-dark mb-1 font-weight-medium">
                                        <?php $sponsor = $user->referred_members_uplines(1, 'enrolment');
                                        echo ($sponsor[1]['username']) ?? 'Nil'; ?>
                                    </h2>
                                </div>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Sponsor</h6>
                            </div>
                            <div class="ml-auto mt-md-3 mt-lg-0">
                                <span class="opacity-7 text-muted"><i data-feather="user"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex d-lg-flex d-md-block align-items-center">
                            <div>
                                <h2 class="text-dark mb-1 font-weight-medium">
                                    <?php echo $downlines = $auth->all_downlines_by_path('enrolment', false, 1)->count(); ?>

                                </h2>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Referrals</h6>
                            </div>
                            <div class="ml-auto mt-md-3 mt-lg-0">
                                <span class="opacity-7 text-muted"><i class="fas fa-sitemap"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex d-lg-flex d-md-block align-items-center">
                            <div>
                                <h2 class="text-dark mb-1 font-weight-medium">
                                    <span class="">
                                        <?= $auth->subscription->payment_plan->name; ?>
                                    </span>

                                </h2>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Account</h6>
                            </div>
                            <div class="ml-auto mt-md-3 mt-lg-0">
                                <span class="opacity-7 text-muted"><i class="fas fa-briefcase"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row match-height">
                <div class="col-lg-4 col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Account info</h4>

                            <ul class="list-style-none mb-0">
                                <li>
                                    <i class="fas fa-circle text-secondary font-10 mr-2"></i>
                                    <span class="text-muted">Name</span>
                                    <span class="text-dark float-right font-weight-medium"><?= $auth->fullname; ?></span>
                                </li>
                                <li class="mt-3">
                                    <i class="fas fa-circle text-secondary font-10 mr-2"></i>
                                    <span class="text-muted">Status</span>
                                    <span class="text-dark float-right font-weight-medium"> <?= $auth->subscription->payment_plan->name; ?></span>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Account Balance</h4>

                            <ul class="list-style-none mb-0">
                                <li>
                                    <i class="fas fa-circle text-secondary font-10 mr-2"></i>
                                    <span class="text-muted">Total Earnings</span>
                                    <span class="text-dark float-right font-weight-medium">$<?= MIS::money_format($wallet_summary['total_earnings']); ?></span>
                                </li>
                                <li class="mt-2">
                                    <i class="fas fa-circle text-secondary font-10 mr-2"></i>
                                    <span class="text-muted">Pending withdrawals</span>
                                    <span class="text-dark float-right font-weight-medium">$<?= MIS::money_format($wallet_summary['pending_withdrawals']); ?></span>
                                </li>
                                <li class="mt-2">
                                    <i class="fas fa-circle text-secondary font-10 mr-2"></i>
                                    <span class="text-muted">Total withdrawals</span>
                                    <span class="text-dark float-right font-weight-medium">$<?= MIS::money_format($wallet_summary['completed_withdrawals']); ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Pool</h4>

                            <ul class="list-style-none mb-0">
                                <li>
                                    <i class="fas fa-circle text-secondary font-10 mr-2"></i>
                                    <span class="text-muted">Total premium</span>
                                    <span class="text-dark float-right font-weight-medium"><?= MIS::format_to_thousand_unit($total_premium_members); ?></span>
                                </li>
                                <li class="mt-2">
                                    <i class="fas fa-circle text-secondary font-10 mr-2"></i>
                                    <span class="text-muted">Total members</span>
                                    <span class="text-dark float-right font-weight-medium"><?= MIS::format_to_thousand_unit($pools_summary['total_persons']); ?></span>
                                </li>
                                <li class="mt-2">
                                    <i class="fas fa-circle text-secondary font-10 mr-2"></i>
                                    <span class="text-muted">This months pools </span>
                                    <span class="text-dark float-right font-weight-medium"><?= $pools_summary['this_month_pools']; ?></span>
                                </li>
                                <li class="mt-2">
                                    <i class="fas fa-circle text-secondary font-10 mr-2"></i>
                                    <span class="text-muted">Value per person</span>
                                    <span class="text-dark float-right font-weight-medium"><?= $pools_summary['value_per_person']; ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-md-6">

                    <div class="alert alert-secondary alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <strong>Referral link:</strong>
                        <span onclick="copy_text('<?= $user->referral_link(); ?>')" class="col-md-5 progress progress-sm mb-0 " style="color: black ;display: inline; height: 17px;"><?= $user->referral_link(); ?>
                        </span>
                    </div>

                </div>
                <div class="col-md-6">
                    <div class="alert alert-secondary alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <strong>Landing page:</strong>
                        <span onclick="copy_text('<?= $domain; ?>')" class="col-md-5 progress progress-sm mb-0 " style="color: black ;display: inline; height: 17px;"><?= $domain; ?>
                        </span>
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">This month's pool</h4>
                            <ul class="list-group" style="max-height:250px;overflow-y:scroll">
                                <?php foreach ($this_month_donation_list as $key => $donation) : ?>
                                    <li class="list-group-item"><?= $donation->user->firstname ?? 'company'; ?> donated <?= $donation->amount; ?>$

                                        <?php if ($donation->user) : ?>
                                            <span class="badge float-right"><a href="<?= $donation->user->DisplayableViiralLegendLink ?? 'javascript:void(0)'; ?>" class="font-14 border-bottom">Follow on Viiral Legends</a></span>
                                        <?php endif; ?>

                                        <small style="display: block;"><?= $donation->notes; ?></small>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (count($this_month_donation_list) == 0) : ?>
                                    <li class="list-group-item text-center">Donations will show here </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Last month's pool</h4>
                            <ul class="list-group" style="max-height:250px;overflow-y:scroll">
                                <?php foreach ($last_month_donation_list as $key => $donation) : ?>
                                    <li class="list-group-item"><?= $donation->user->firstname ?? 'company'; ?> donated <?= $donation->amount; ?>$
                                        <?php if ($donation->user) : ?>
                                            <span class="badge float-right"><a href="<?= $donation->user->DisplayableViiralLegendLink ?? 'javascript:void(0)'; ?>" class="font-14 border-bottom">Follow on Viiral Legends</a></span>
                                        <?php endif; ?>
                                        <small style="display: block;"><?= $donation->notes; ?></small>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (count($last_month_donation_list) == 0) : ?>
                                    <li class="list-group-item text-center">Donations will show here </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <iframe id="myIframe" src="https://www.webtalklegends.com/updates"></iframe>

                    </div>
                </div>

                <div class="col-md-6" style="display:none;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Shares Table</h4>
                            <img class="card-img-top img-fluid" src="<?= asset; ?>/images/logo/shares-table.png" alt="Card image cap">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>