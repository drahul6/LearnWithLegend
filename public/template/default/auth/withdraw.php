<?php
$page_title = "Withdraw";
include 'includes/header.php';



$rules_settings =  SiteSettings::find_criteria('rules_settings');
$min_deposit = $rules_settings->settingsArray['min_deposit_usd'];; ?>

?>
<script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>
<script src="<?= asset; ?>/angulars/rave-checkout.js"></script>


<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Withdraw</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="index-2.html" class="text-muted">Apps</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Ticket List</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center">
                <div class="customize-input float-right">
                    <button class="btn btn-outline-dark" data-toggle="modal" data-target="#myModal">Make Withdrawal</button>
                    <!-- <select class="custom-select custom-select-set form-control bg-white border-0 custom-shadow custom-radius">
                        <option selected="">Aug 19</option>
                        <option value="1">July 19</option>
                        <option value="2">Jun 19</option>
                    </select> -->
                </div>
            </div>
        </div>
    </div>


    <div class="container-fluid">



        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">History</h4>
                    <div class=" list-group">
                        <a href="javascript:void(0)" class="list-group-item list-group-item-action flex-column align-items-start">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">#223 Deposit</h5>
                                <small>2022-02-20 4:3am</small>
                            </div>
                            <p class="mb-1"><?= $currency; ?>4,000.00</p>
                            <small><span class="badge badge-success">completed</span></small>
                        </a>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- The Modal -->
    <div class="modal" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Deposit</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <form class="ajax_for" method="POST" action="<?= domain; ?>/user/submit_make_deposit" data-function="initiate_payment">
                        <small>Minimum Deposit: <?= $currency; ?><?= MIS::money_format($min_deposit); ?></small><br>
                        <hr />

                        <div class="form-group">
                            <label>Amount (<?= $currency; ?>)</label>
                            <input type="number" step="1" min="<?= $min_deposit; ?>" name="amount" required="" class="form-control">
                        </div>
                        <!-- <input type="hidden" name="payment_method" value="rave" required=""> -->

                        <div class="form-group" style="display:;">
                            <label>Select Processor</label>
                            <select class="form-control" name="payment_method" required="">
                                <option value="">Select Payment method</option>
                                <?php foreach ($shop->get_available_payment_methods() as $key => $option) : ?>
                                    <option value="<?= $key; ?>"><?= $option['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-outline-dark">Deposit</button>
                        </div>

                    </form>
                </div>

                <!--                 <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
 -->
            </div>
        </div>
    </div>






    <?php include 'includes/footer.php'; ?>