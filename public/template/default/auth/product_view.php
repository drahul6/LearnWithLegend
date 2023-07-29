<?php
$page_title = "$product->name";
include 'includes/header.php';

?>

<div class="page-wrapper">

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h3 style="text-transform:capitalize;" class="page-title text-truncate text-dark font-weight-medium mb-1"><?= $product->name; ?></h3>
                <a href="<?= domain; ?>/user/marketplace">&lt;&lt; Market</a><br>

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


    <?php
    $market_details = $product->market_details();; ?>
    <div class="container-fluid " id="content" ng-controller="ShopController">

        <div class="card">
            <div class="card-content">
                <div class="card-body row">

                    <div class="product-img col-md-6">

                        <?= $product->DisplayImage; ?>

                    </div>
                    <div class="col-md-6">
                        <div class="ps-lg-10 mt-6 mt-md-0">
                            <!-- content -->
                            <small href="#!" class="mb-2 d-block text-muted">Sold by <?= $market_details['by']; ?></small>
                            <!-- heading -->
                            <h1 class="mb-1"><?= $product->name; ?> </h1>
                            <!--                                 <div class="mb-4">
                                    <small class="text-warning"> <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-half"></i>
                                    </small>
                                    <a href="#" class="ms-2">(30 reviews)</a>
                                </div> -->


                            <div class="fs-4">
                                <span class="fw-bold text-dark">$<?= $market_details['price']; ?></span>
                                <!-- <span class="text-decoration-line-through text-muted">$35</span> -->
                                <!-- <span><small class="fs-6 ms-2 text-danger">26% Off</small></span> -->
                            </div>
                            <!-- hr -->
                            <hr class="my-6">

                            <!-- Qty:<input value="1" id="qty" type="number" min="1" max="6"> -->
                            <!-- <button type="a" onclick="buy_now();" class="btn btn-rounded custom-shadow btn-sm btn-outline-secondary"><i class="feather-icon icon-shopping-bag me-2"></i>Buy Now</button> -->

                            <a href="<?= $product->BuyNowLink; ?>" class="btn btn-rounded custom-shadow btn-sm btn-outline-secondary"><i class="feather-icon icon-shopping-bag me-2"></i> Buy Now</a>

                        </div>

                        <!-- hr -->
                        <hr class="my-6">
                        <div>
                            <!-- table -->
                            <table class="table table-borderless table-sm mb-0">
                                <tbody>
                                    <tr>
                                        <td>Product Code</td>
                                        <td><?= $product->product_code; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Pools commission</td>
                                        <td><?= $product->data['pools_commission']; ?>%</td>
                                    </tr>
                                    <tr>
                                        <td>Location:</td>
                                        <td><?= $product->data['country'] ?? 'N/A'; ?></td>

                                    </tr>
                                    <tr>
                                        <td>Type:</td>
                                        <td><?= $product->type_of_product ?? 'N/A'; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Shipping:</td>
                                        <td><small>01 day shipping.</td>
                                    </tr>

                                </tbody>
                            </table>

                        </div>
                        <div class="mt-8">
                            <a onclick="navigator.share({
                            title: '<?= $product->name; ?>', text: '<?= $product->ShortDescription; ?>' , url: '<?= $product->ViewLink; ?>' })" class="btn btn-sm btn-outline-secondary" href="javascript:void(0);">
                                Share
                                <i class=" fas fa-share-alt"></i>
                            </a>
                        </div>

                    </div>
                </div>
            </div>



        </div>
        <div class="row">

            <div class=" col-md-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">

                            <h4 class="card-tile border-0">Product description</h4>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <?= $product->description; ?>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-3">
                <button onclick="buy_now();" class="btn btn-outline-dark btn-block">Buy Now</button>
            </div>
        </div>
    </div>

    <script>
        try {
            var $this_item = <?= $product->id; ?>;
        } catch (e) {}

        buy_now = function() {

            $.ajax({
                type: "POST",
                url: `${$base_url}/shop/get_single_item_on_market/${$this_item}`,
                data: null,
                contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
                processData: false, // NEEDED, DON'T OMIT THIS
                cache: false,
                success: function(data) {
                    $item = data.single_good;
                    $scope = angular.element($('#content')).scope();

                    $item.qty = $('#qty').val();

                    $scope.$shop.$cart.buy_now($item);
                    $scope.$apply();
                },
                error: function(data) {},
                complete: function() {}
            });
        }
    </script>


    <style>
        .product-img {
            /* height: 450px; */
            width: 100% !important;
            object-fit: cover;
        }
    </style>


    <?php include 'includes/footer.php'; ?>