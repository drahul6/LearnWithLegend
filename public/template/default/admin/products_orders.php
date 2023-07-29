<?php
$page_title = "Orders";
include 'includes/header.php'; ?>


<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 ">
                <?php include 'includes/breadcrumb.php'; ?>

                <h3 class="content-header-title mb-0">Orders</h3>
            </div>

            <div class="content-header-right col-md-6">
                <?= $note; ?>
            </div>
        </div>
        <div class="content-body">

            <section id="video-gallery" class="card">
                <div class="card-header">
                    <h4 class="card-title"></h4>
                    <?php include_once 'template/default/composed/filters/products_orders.php'; ?>

                </div>
                <div class="card-content">
                    <div class="card-body table-responsive">

                        <table id="" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#Ref</th>
                                    <th>Buyer</th>
                                    <th>Seller</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order) :
                                    $item = $order->order_detail()[0];
                                    $amount = $item['qty'] * $item['price'];

                                ?>
                                    <tr>
                                        <td><?= $order->TransactionID; ?> <br>
                                            Price:$<?= $item['price']; ?>
                                            <br>Pools:<?= $item['data']['pools_commission']; ?>%
                                        </td>
                                        <td><?= $order->Buyer->DropSelfLink; ?></td>
                                        <td><?= $order->seller->DropSelfLink; ?></td>
                                        <td>
                                            <h4> <?= $item['name']; ?>
                                                <small class="badge badge-primary badge-pill"><?= $item['type_of_product']; ?> </small>
                                            </h4>
                                            <span>
                                                <?= $item['market_details']['short_description']; ?>
                                            </span>

                                        </td>
                                        <td><small class="badge badge-primary"><?= date("M j, Y h:iA", strtotime($order->created_at)); ?></small>
                                            <br><?= $order->payment; ?>
                                            <?= $order->DisplayableStatus; ?>
                                        </td>
                                        <td>

                                            <div class="dropdown">
                                                <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-toggle="dropdown">
                                                </button>
                                                <div class="dropdown-menu">

                                                    <?php if (!$order->is_paid()) : ?>
                                                        <a href="javascript:void(0);" class="dropdown-item" onclick="$confirm_dialog = new ConfirmationDialog('<?= domain; ?>/admin-products/mark_as_complete/<?= $order->id; ?>')"> Mark Paid</a>
                                                    <?php endif; ?>



                                                    <?php if ($order->payment_proof != null) : ?>
                                                        <a class="dropdown-item" target="_blank" href="<?= domain; ?>/<?= $order->payment_proof; ?>">See Proof</a>
                                                    <?php endif; ?>

                                                    <?php if (!$order->is_paid()) : ?>
                                                        <form id="fo<?= $order->id; ?>" class="ajax_form" action="<?= $order->reverifyLink; ?>" method="post">
                                                            <button type="submit" class="dropdown-item" class="">
                                                                Query
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <a href="<?= domain; ?>/admin/order/<?= $order->id; ?>" class="dropdown-item">
                                                        Open
                                                    </a>

                                                    <form id="payment_proof_form<?= $order->id; ?>" action="<?= domain; ?>/user/upload_payment_proof/<?= $order->id; ?>" method="post" enctype="multipart/form-data">
                                                        <input style="display: none" type="file" onchange="document.getElementById('payment_proof_form<?= $order->id; ?>').submit();" id="payment_proof_input<?= $order->id; ?>" name="payment_proof">

                                                        <input type="hidden" name="order_id" value="<?= $order->id; ?>">
                                                    </form>
                                                </div>
                                            </div>


                                        </td>
                                    </tr>
                                <?php endforeach; ?>

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

<?php include 'includes/footer.php'; ?>