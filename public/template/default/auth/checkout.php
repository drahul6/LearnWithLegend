<?php
$page_title = "Checkout";
include 'includes/header.php';

$default_wallet = $auth->getAccount('default');
$avail_balance = ($default_wallet->get_balance()['account_currency']['available_balance']);


?>

<div class="page-wrapper" ng-controller="ShopController" ng-cloak id="content">

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Checkout</h3>
                <a href="<?= domain; ?>/user/marketplace">&lt;&lt; Market</a><br>
                <span>{{$shop.$cart.$items.length}} Item(s) in Cart</span>
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


    <div class="container-fluid">

        <div class="row card-body">
            <div class="col-md-5 order-md-2 mb-4">
                <h4 class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Your Order</span>
                    <!-- <span class="badge badge-secondary badge-pill">3</span> -->
                </h4>
                <div class="card">
                    <div class="card-body">
                        <div class="card-text">
                            <center ng-hide="$shop.$cart.$items.length>0" style='margin:30px; '><i class="fa fa-spinner fa-spin fa-2x"></i></center>

                            <div ng-repeat="($index, $item) in $shop.$cart.$items" class="media" style="border-bottom: 1px solid #dddddd;margin-bottom: 5px;">
                                <a class="media-left pr-1 d-lg-block d-md-block d-none" href="#">
                                    <img class="media-object" src="{{$item.market_details.thumbnail}}" alt="{{$item.market_details.name}} image" style="width: 64px;height: 64px; object-fit: cover;">
                                </a>

                                <div class="media-body">
                                    <h4 class="media-heading">{{$item.market_details.name}}
                                        <small class="badge badge-sm badge-light">
                                            {{$shop.$cart.$items[0].type_of_product}}
                                        </small>
                                    </h4>
                                    <!-- <span ng-bind-html="$item.market_details.short_description" class="text-muted"></span> -->
                                </div>

                                <a href="javascript:void(0);" ng-click="$shop.$cart.remove_item($item)" class="remove-item text-danger far fa-times-circle"></a>
                                <div class="quantity text-right">

                                    qty:<input style="width:35px;border-radius: 8px;border: 1px solid #e0e0e0;" max="{{$item.market_details.stock}}" ng-change="$shop.$cart.update_server();" type="number" class="quantity-input" ng-model="$item.qty" id="qty-4" min="1">

                                    <div style="margin-top:4px; text-align:right;">
                                        <b>${{$item.market_details.price * $item.qty}}</b> <br>
                                        <small>${{$item.market_details.price }} x {{$item.qty}} items</small>
                                    </div>
                                </div>

                            </div>
                            <table class="table table-striped">
                                <!--  <tr>
                                    <th style="padding: 5px;">Order</th>
                                    <td class="text-right" style="padding: 5px;">
                                        <span ng-bind-html="$shop.$config.currency"></span> {{($shop.$cart.calculate_total()) |  number:2}}
                                    </td>
                                </tr> -->
                                <tbody id="payment_breakdown">
                                    <tr class="order-total">
                                        <th style="padding: 5px;">Total Payable</th>
                                        <td class="text-right" style="padding: 5px;">
                                            <b>${{($shop.$cart.calculate_total()) |  number:2}}</b>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <!-- 
                            <a href="javascript:void;" ng-click="$shop.$cart.empty_cart()" class="btn btn-rounded btn-outline-light  btn-sm">Empty Cart</a>
                        -->
                            <a ng-show="$shop.$cart.$items.length==0" href="<?= $domain; ?>/user/marketplace" class=" btn btn-rounded btn-outline-secondary  btn-sm"> Continue Shopping</a><br>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-7 order-md-1">

                <form class="needs-validation" novalidate="" ng-show="$shop.$cart.$items[0].type_of_product=='physical'">
                    <h4 class="mb-3">Shipping details
                        <button class="float-righ btn btn-outline-light btn-rounded btn-sm " onclick="form.reset();" type="button">Reset</button>
                    </h4>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName">First name</label>
                            <input type="text" ng-init="$shop.$cart.$shipping_details.firstname='<?= $auth->firstname; ?>'" ng-model="$shop.$cart.$shipping_details.firstname" class="form-control" id="firstName" placeholder="" required="">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastName">Last name</label>
                            <input type="text" ng-init="$shop.$cart.$shipping_details.lastname='<?= $auth->lastname; ?>'" ng-model="$shop.$cart.$shipping_details.lastname" class="form-control" id="lastName" placeholder="" value="" required="">
                        </div>
                    </div>


                    <div class="mb-3">
                        <label for="email">Email</label>
                        <input type="email" ng-model="$shop.$cart.$shipping_details.email" class="form-control" id="email" placeholder="you@example.com">
                    </div>


                    <div class="mb-3">
                        <label for="address">Address</label>
                        <input type="text" ng-model="$shop.$cart.$shipping_details.address" class="form-control" id="address" placeholder="1234 Main St" required="">
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="country">Delivery method</label>
                            <select class="custom-select d-block w-100" ng-model='$shop.$cart.$shipping_details.delivery' id="country" required="">
                                <option value="">Select method</option>
                                <option value="free_pick_up">Free pick up</option>
                                <option value='contact_seller'>Contact seller privately</option>
                            </select>
                            <small class="helper">
                                <span ng-show="$shop.$cart.$shipping_details.delivery=='free_pick_up'"> This seller does not deliver.</span>
                                <span ng-show="$shop.$cart.$shipping_details.delivery=='contact_seller'">Contact the seller privately to organize postage and any delivery fees</span>
                            </small>

                        </div>
                    </div>


                </form>
                <h4>Payment</h4>
                <div class="d-block my-3">
                    <div class="custom-control custom-radio">
                        <input id="credit" name="paymentMethod" type="radio" class="custom-control-input" checked="" required="">
                        <label class="custom-control-label" for="credit"><span class="text-muted">Account Funds</span> ($<?= $avail_balance; ?>)</label>
                    </div>
                </div>

                <!-- <hr class="mb-4"> -->
                <form id="" class="ajax_form" data-function="on_complete_order" method="post" action="<?= domain; ?>/shop/complete_order/make_payment">
                    <textarea style="display: none ;" name="cart" class="form-control">
                    {{$shop.$cart}}
                    </textarea>
                    <div class="form-group">
                        <!--  
                                <label>Pay With* </label> <small class="float-right"> </small>
                                <select class="form-control" required="" name="payment_method" onchange="present_payment_methods(form);">
                                <option value="">Select Payment method</option>
                                <?php foreach ($shop->get_available_payment_methods() as $key => $option) : ?>
                                    <option value="<?= $key; ?>"><?= $option['name']; ?></option>
                                <?php endforeach; ?>
                            </select> -->

                        <input type="hidden" value="wallet" name="payment_method">
                    </div>

                    <button id="get_breakdown_btn" type="button" style="display: none;" class="btn btn-dark" onclick="get_breakdown(form)"> Set Payment Method</button>
                    <button ng-show="$shop.$cart.$items.length>0" type="submit" class="btn btn-block btn-success btn-rounded" id="submit_btn">Complete Order >></button>
                </form>
            </div>
        </div>




    </div>
    <script>
        var on_complete_order = function($data) {
            location.href = `${$base_url}/user/order/${$data.order.id}`;
        }
    </script>
    <style>
        .remove-item {
            position: absolute;
            left: 10px;
        }
    </style>

    <?php include 'includes/footer.php'; ?>