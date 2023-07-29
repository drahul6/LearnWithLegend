<?php
$page_title = "Sale #$sale->id";
include 'includes/header.php';
?>

<div class="page-wrapper">

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-12 ">
                <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Sale #<?= $sale->id; ?>
                    <small><?= $sale->DisplayableStatus; ?></small>
                </h3>

                <div class="d-sm-flex align-items-center justify-content-between">
                    <div>
                        <!-- <h4 class="mg-b-5">Invoice #<?= $sale->OrderId; ?></h4> -->
                        <p class="mb-0 tx-color-03">Created <?= date("M j, Y", strtotime($sale->paid_at)); ?></p>
                    </div>
                    <div class="mg-t-20 mg-sm-t-0">
                        <?php if (true) : ?>
                            <div class="btn-group btn-group-sm" role="group" aria-label="">
                                <a href="<?= $sale->SupportLink; ?>" class="btn btn-outline-secondary">Contact Buyer</a>

                                <a onclick="$confirm_dialog=new ConfirmationDialog('<?= $sale->UserMarkAsDeliveredLink; ?>')" href="javascript:void(0)" class="btn btn-outline-secondary">Mark as delivered</a>


                            </div>
                        <?php endif; ?>
                    </div>
                </div>


            </div>

        </div>
    </div>


    <div class="container-fluid">

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <label class="tx-sans tx-uppercase tx-10 tx-medium tx-spacing-1 tx-color-03">Seller</label>

                            <h6 class="tx-15 mg-b-10"><?= $sale->seller->fullname; ?>, @<?= $sale->seller->username; ?>.</h6>
                            <!-- <p class="mb-0">201 Something St., Something Town, YT 242, Country 6546</p> -->
                            <p class="mb-0">Tel No: <?= $sale->seller->phone; ?></p>
                            <p class="mb-0">Email: <?= $sale->seller->email; ?></p>
                        </div>

                        <div class="col-sm-6 mt-3">
                            <label class="tx-sans tx-uppercase tx-10 tx-medium tx-spacing-1 tx-color-03">Buyer</label>

                            <h6 class="tx-15 mg-b-10"><?= $sale->buyer->fullname; ?>, @<?= $sale->buyer->username; ?>.</h6>
                            <!-- <p class="mb-0">201 Something St., Something Town, YT 242, Country 6546</p> -->
                            <p class="mb-0">Tel No: <?= $sale->buyer->phone; ?></p>
                            <p class="mb-0">Email: <?= $sale->buyer->email; ?></p>

                        </div>

                        <div class="col-sm-6 col-lg-6 mt-3 mg-sm-t-0 mg-md-t-40">
                            <?php if ($sale->isPhysical()) :
                                $shipping = $sale->ShippingDetail;

                            ?>
                                <label class="tx-sans tx-uppercase tx-10 tx-medium tx-spacing-1 tx-color-03">Shipped To</label>
                                <h6 class="tx-15 mg-b-10"><?= $shipping['firstname']; ?> <?= $shipping['lastname']; ?></h6>
                                <p class="mb-0"><?= $shipping['address'] ?? ''; ?></p>
                                <p class="mb-0">Tel No: <?= $shipping['phone'] ?? ''; ?></p>
                                <p class="mb-0">Email: <?= $shipping['email'] ?? ''; ?></p>
                                <p class="mb-0">Delivery method: <span class="badge badge-light"><?= str_ireplace("_", " ", $shipping['delivery'] ?? ''); ?></span></p>
                            <?php endif; ?>
                        </div>

                        <div class="col-sm-6 col-lg-4 mt-3">
                            <b class="tx-sans tx-uppercase tx-8 tx-medium tx-spacing-1 tx-color-03">Invoice Information</small></b>
                            <ul class="list-unstyled lh-7">
                                <li class="d-flex justify-content-between">
                                    <span class="text-muted">Invoice Number</span>
                                    <span><?= $sale->OrderId; ?></span>
                                </li>

                                <li class="d-flex justify-content-between">
                                    <span class="text-muted">Due Date</span>
                                    <span><?= $sale->DueDate; ?></span>
                                </li>

                            </ul>
                        </div>
                    </div><!-- row -->

                    <div class="table-responsive mt-3">
                        <table class="table table-invoice bd-b table-sm">
                            <thead>
                                <tr>
                                    <th class="wd-20p">Item</th>
                                    <th class="wd-40p d-none d-sm-table-cell">Description</th>
                                    <th class="tx-center">QTY</th>
                                    <th class="tx-right">Unit</th>
                                    <th class="tx-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sale->order_detail() as $key => $item) :
                                    $amount = $item['qty'] * $item['price'];
                                ?>
                                    <tr>
                                        <td class="tx-nowrap"><?= $item['name']; ?>
                                            <br> <small class="badge badge-light"> <?= $item['type_of_product']; ?> </small>
                                            <br> <small class="badge badge-light">Pools Comm: <?= $item['data']['pools_commission']; ?>%</small>
                                        </td>
                                        <td class="d-none d-sm-table-cell tx-color-03"> <?= $item['market_details']['short_description']; ?> </td>
                                        <td class="tx-center"><?= $item['qty']; ?></td>
                                        <td class="tx-right">$<?= $item['price']; ?></td>
                                        <td class="tx-right">$<?= round($amount, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="row justify-content-between">
                        <div class="col-sm-6 col-lg-6 order-2 order-sm-0 mt-3 mg-sm-t-0">
                            <label class="tx-sans tx-uppercase tx-10 tx-medium tx-spacing-1 tx-color-03">Notes</label>
                            <p> Please contact the buyer to deliver order. </p>
                        </div>
                        <div class="col-sm-6 col-lg-4 order-1 order-sm-0">
                            <ul class="list-unstyled lh-7 pd-r-10">
                                <li class="d-flex justify-content-between">
                                    <span>Sub-Total</span>
                                    <span>$<?= $sale->total_price(); ?></span>
                                </li>

                                <li class="d-flex justify-content-between">
                                    <strong>Total Due</strong>
                                    <strong>$<?= $sale->total_price(); ?></strong>
                                </li>
                            </ul>

                            <?php if (!$sale->isPhysical()) :

                            ?>
                                <a href="<?= $sale->DownloadLink; ?>" class="btn btn-block btn-outline-secondary">Download Delivery</a>
                            <?php endif; ?>
                        </div>
                    </div><!-- row -->
                </div>

            </div>
        </div>


    </div>

    <?php include 'includes/footer.php'; ?>