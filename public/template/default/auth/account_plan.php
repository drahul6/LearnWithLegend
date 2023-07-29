<?php
$page_title = "Account Plans";
include 'includes/header.php';
?>

<div class="page-wrapper">

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Account Plans


                </h3>
                <small class="badge badge-dark"><?= $auth->BillingMode; ?> billing</small>
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
                    <?= MIS::generate_form([], "$domain/user/automate_account_plan_billing", "Set $auth->BillingButtonText billing", null, true); ?>
                    <!-- 
                    <select class="custom-select custom-select-set form-control bg-white border-0 custom-shadow custom-radius">
                        <option selected>Aug 19</option>
                        <option value="1">July 19</option>
                        <option value="2">Jun 19</option>
                    </select>
 -->
                </div>
            </div>
        </div>
    </div>


    <div class="container-fluid">



        <div class="row match-height">

            <?php foreach (SubscriptionPlan::available()->get() as  $subscription) : ?>

                <div class=" col-md-6">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <h4 class="card-title"><?= $subscription->name; ?></h4>
                                <?php if (@$auth->subscription->payment_plan['id']  == $subscription->id) : ?>
                                    <div class="form-group">
                                        <span type="span" class="badge badge-success -sm">Current</span>
                                        <small><?= $auth->subscription->NotificationText; ?></small>
                                    </div>
                                <?php endif; ?>
                                <h6 class="card-subtitle text-mute"> <b class="float-right">
                                        <?= $currency; ?><?= MIS::money_format($subscription->price); ?>
                                        /Month
                                    </b>
                                </h6>
                            </div>

                            <div class="card-body">
                                <!-- <h6 class="card-subtitle text-mute">Support card subtitle</h6> -->
                                <!-- <p class="card-text">Excluding VAT <?= (int)$subscription->percent_vat; ?>% </p> -->
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($subscription->DetailsArray['benefits'] as $key => $benefit) : ?>
                                        <li class="list-group-item small-padding text-capitalize">
                                            <?php if ($benefit == 1) : ?>
                                                <span class="badge badge-success float-right"><i class="fa fa-check"></i></span>
                                            <?php else : ?>
                                                <span class="badge badge-danger float-right"><i class="fa fa-times"></i></span>
                                            <?php endif; ?>
                                            <?= $key; ?>
                                        </li>

                                    <?php endforeach; ?>
                                </ul>
                                <br>
                                <?php if ($subscription['price'] != 0) : ?>
                                    <form style="display:inline;" id="upgrade_form<?= $subscription->id; ?>" method="post" class="ajax_form" action="<?= domain; ?>/user/create_upgrade_request">

                                        <input type="hidden" name="wallet" value="deposit">

                                        <input type="hidden" name="subscription_id" value="<?= $subscription->id; ?>">
                                        <br>
                                        <div class="form-group">
                                            <button class="btn btn-outline-dark" type="button" onclick="$confirm_dialog = new DialogJS(submit_request, [form], 'Are you sure ?')">Subscribe</button>
                                        </div>
                                    </form>

                                <?php endif; ?>


                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>




    </div>



    <script>
        submit_request = function($form) {

            $form.submit();
        }



        initiate_payment = function($data) {

            switch ($data.payment_method) {
                case 'coinpay':
                    // code block
                    window.location.href = $base_url +
                        "/shop/checkout?item_purchased=packages&order_unique_id=" + $data.id + "&payment_method=coinpay";

                    break;

                case 'paypal':
                    // code block
                    window.location.href = $base_url +
                        "/shop/checkout?item_purchased=packages&order_unique_id=" + $data.id + "&payment_method=paypal";

                    break;
                case 'razor_pay':
                    // code block
                    window.SchemeInitPayment($data.id);
                    break;
                default:
                    // code block
            }
        }
    </script>

    <?php include 'includes/footer.php'; ?>