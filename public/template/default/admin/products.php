<?php
$page_title = "Products";
include 'includes/header.php'; ?>


<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 ">
                <?php include 'includes/breadcrumb.php'; ?>

                <h3 class="content-header-title mb-0">Products</h3>
            </div>

            <div class="content-header-right col-md-6">
                <?= $note; ?>
            </div>
        </div>
        <div class="content-body">

            <section id="video-gallery" class="card">
                <div class="card-header">
                    <h4 class="card-title"></h4>
                    <?php include_once 'template/default/composed/filters/products.php'; ?>

                </div>
                <div class="card-content">
                    <div class="card-body table-responsive">

                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Seller</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <?php $i = 1;
                            foreach ($products as $product) : ?>
                                <tr>
                                    <td>
                                        #<?= $i; ?>
                                        <?= $product->type_of_product; ?>
                                        <h4><?= $product->name ?? 'New Product'; ?></h4>
                                        Price: $<?= $product->price ?? ''; ?> <br>
                                        Pools %: <?= $product->data['pools_commission'] ?? ''; ?>
                                        <br>Stock:<?= $product->stock ?? "unlimited" ?>
                                    </td>
                                    <td>
                                        <?= $product->seller->DropSelfLink; ?>
                                    </td>
                                    <td>
                                        <?= $product->QuickDescription ?? 'Quick Description'; ?>
                                        <hr>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= domain; ?>/shop/product_detail/<?= $product->id; ?>" class="btn btn-primary">View</a>

                                            <?php if (!$product->isPhysical()) : ?>
                                                <a href="<?= $product->AdminDownloadLink; ?>" class="btn btn-primary">Download</a>
                                            <?php endif; ?>

                                            <?= $product->DisplayedStatusActions("no", "btn-group", "get"); ?>
                                        </div>

                                    </td>
                                    <td>

                                        <?= $product->DisplayableStatus; ?><br>
                                        created:<small class="badge badge-dark"> <?= date("M j Y h:ia", strtotime($product->created_at)); ?></small><br>
                                        updated:<small class="badge badge-dark"> <?= date("M j Y h:ia", strtotime($product->updated_at)); ?></small><br>
                                    </td>


                                </tr>

                            <?php $i++;
                            endforeach; ?>


                            </tbody>
                        </table>




                        <?php if (count($products) == 0) : ?>
                            <div class="alert alert-white text-center">
                                Your products will show here.
                            </div>
                        <?php endif; ?>

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