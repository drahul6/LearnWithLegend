<?php

use League\ISO3166\ISO3166;

$page_title = "Edit Product";
include 'includes/header.php';

$item = $product;
$ecom_settings = SiteSettings::ecommerceSettings();
?>


<script>
    app.controller('EditProductController', function($scope, $http) {

        $scope.$no_in_cart = "6453";
        $scope.types_of_product = "<?= $item->type_of_product; ?>";
    });
</script>


<div class="page-wrapper" ng-controller="EditProductController">

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h3 style="display:inline;" class="page-title text-truncate text-dark font-weight-medium mb-1">Edit Product
                    <br><small><?= $product->DisplayableStatus; ?></small>
                </h3>


                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href=""><?= $note ?? ""; ?></a>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center">
                <div class="customize-input float-right">
                    <div class="btn-group">
                        <a href="<?= domain; ?>/user/create_product" class="btn btn-outline- custom-shadow custom-radius">+ Create Product</a>
                    </div>

                </div>

            </div>
        </div>


        <div class="container-flui">
            <div class="card">
                <form method="post" enctype="multipart/form-data" class="card-body ajax_form" action="<?= domain; ?>/user/update_product">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <?= $this->csrf_field('update_products'); ?>
                            <label>Name</label>
                            <input type="" name="name" class="form-control" required="required" value="<?= $item->name; ?>" placeholder="Item name">
                        </div>

                        <input type="hidden" name="item_id" value="<?= $item->id; ?>">


                        <div class="form-group col-md-6">
                            <label>Stock</label>
                            <input type="" name="stock" class="form-control" value="<?= $item->stock; ?>" placeholder="Qty available">
                            <small class="helper-text">Leave empty for unlimited stock.</small>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Country (Location) </label>
                            <select class="form-control" name="data[country]" value="">
                                <option>Select</option>
                                <?php
                                $iso = (new ISO3166);
                                foreach ($iso->all() as $key => $country) : ?>
                                    <option <?= @$item->data['country'] == strtolower($country['name']) ? "selected" : ""; ?> value="<?= strtolower($country['name']); ?>"><?= $country['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>




                        <div class="form-group col-md-6">
                            <label>Price</label>
                            <input type="" name="price" class="form-control" required="required" value="<?= $item->price; ?>" placeholder="Item price">
                        </div>

                        <div class="form-group col-md-6">
                            <label>Pools Commission (%)</label>
                            <input type="number" min="<?= $ecom_settings['min_pools_commission']; ?>" step="1" max="100" name="data[pools_commission]" class="form-control" required="required" value="<?= $item->data['pools_commission'] ?? ''; ?>" placeholder="Pools commission">
                            <small class="helper-text">% of sale price for into monthly pool</small>
                        </div>

                        <div class="form-group col-md-6">

                            <div class="row">
                                <?php foreach ($product->images['images'] ?? [] as $key => $value) :

                                    $image_path = (!file_exists($value['thumbnail'])) ? Products::default_ebook_pix()
                                        : "$domain/{$value['thumbnail']}";

                                ?>
                                    <div class="col-2">
                                        <img src="<?= $image_path; ?>" style="width: 80px; border: 1px solid beige; height: 70px; object-fit: cover;">
                                        <i class="fa fa-times-circle delete-image" onclick="select_this_for_delete(this)"></i>
                                        <input type="checkbox" name="images_to_be_deleted[]" value="<?= $key; ?>" style="display: none;">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <label>Cover Image</label>
                            <small style="float:right;font-size: 10px;" class="text-danger">Marked pictures will be deleted when you save.</small>
                            <input type="file" accept="image/*" multiple="" name="front_image[]" class="form-control" placeholder="Item price">



                        </div>

                        <style type="text/css">
                            .delete-image:hover {
                                color: red;
                                cursor: pointer;
                            }

                            .delete-image {
                                position: absolute;
                                top: 0px;
                                font-size: 20px;
                            }
                        </style>



                        <div class="form-group col-md-6">
                            <label>Type of product</label>
                            <select class="form-control" ng-model="types_of_product" name=" type_of_product" value="">
                                <option>Select</option>
                                <?php foreach (Products::$types_of_product as $key => $type) : ?>
                                    <option <?= $item->type_of_product == $type ? "selected" : ""; ?> value="<?= $type; ?>"><?= $type; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>


                        <div class="form-group col-md-6" ng-show="types_of_product=='digital'">
                            <label>Downloadable File</label>
                            <small class="helper-text text-danger float-right">
                                <a class="text-decoration-underline" href="<?= $item->UserDownloadLink; ?>"> Preview link </a>
                            </small>
                            <input type="file" name="downloadable_files" class="form-control" placeholder="Item price">
                            <!-- <small class="helper-text text-danger float-right">* Preferably .Zip</small> -->
                        </div>



                        <div class="form-group col-md-12">
                            Description
                            <textarea id="editor1" class="form-control" name="description" rows="4" required="required" placeholder="Item description"><?= $item->description; ?></textarea>
                        </div>



                        <div class="form-group col-6">
                            <button type="submit" class="form-control btn-primary">
                                Save
                            </button>

                        </div>
                        <div class="form-group col-6">
                            <a onclick="$confirm_dialog = new ConfirmationDialog('<?= domain; ?>/shop/submit_for_review/<?= $item->id; ?>')" href="javascript:void(0);">
                                <button class="form-control btn btn-secondary  text-white" type="button">
                                    Put on sale
                                    <i class="fa fa-check-circle"></i>
                                </button>
                            </a>
                        </div>
                    </div>

                </form>
            </div>


            <script>
                select_this_for_delete = function($element) {
                    $checkbox = $element.nextSibling.nextSibling;

                    if ($checkbox.checked == false) {
                        $checkbox.checked = true;
                        $element.style.color = 'tomato';
                    } else {
                        $checkbox.checked = false;
                        $element.style.color = 'black';

                    }

                }

                CKEDITOR.replace('editor1');
                CKEDITOR.replace('editor2');
            </script>


        </div>

        <?php include 'includes/footer.php'; ?>