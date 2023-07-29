<!DOCTYPE html>
<html ng-app="app" class="loading" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="">

    <link href="<?= $this_folder; ?>/../assets/extra-libs/c3/c3.min.css" rel="stylesheet">

    <link href="<?= $this_folder; ?>/../assets/dist/css/style.min.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="<?= $asset; ?>/css/binary-tree.css">



    <script src="<?= asset; ?>/angulars/angularjs.js"></script>
    <script src="<?= asset; ?>/angulars/angular-sanitize.js"></script>

    <script>
        let $base_url = "<?= domain; ?>";
        var app = angular.module('app', ['ngSanitize']);
    </script>

</head>

<body>
    <div class="">

        <?php
        $market_details = $product->market_details();; ?>
        <div class="container " id="content" ng-controller="ShopController">

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
                                <button type="a" onclick="buy_now();" class="btn btn-rounded custom-shadow btn-sm btn-outline-secondary"><i class="feather-icon icon-shopping-bag me-2"></i>Buy Now</button>

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
                                            <td>Location:</td>
                                            <td><?= $product->data['location'] ?? 'N/A'; ?></td>

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

                location.href = `${$base_url}/shop/full-view/${$this_item}`;
                return;

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
                width: 100% !important;
                object-fit: cover;
            }
        </style>


    </div>
</body>

</html>