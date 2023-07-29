<?php
$page_title = "Cart";
include 'includes/header.php';
?>

<div class="page-wrapper" ng-controller="ShopController" ng-cloak id="content">

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Cart</h3>
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
        <section class="row">
            <div class="col-md-9">
                <div id="kick-start" class="card">

                    <div class="card-content collapse show">
                        <div class="card-body">
                            <div class="card-text">
                                <center ng-hide="$shop.$cart.$items.length>0" style='margin:30px; '><i class="fa fa-spinner fa-spin fa-2x"></i></center>

                                <div ng-repeat="($index, $item) in $shop.$cart.$items" class="media" style="border-bottom: 1px solid #dddddd;margin-bottom: 5px;">
                                    <a class="media-left pr-1 d-lg-block d-md-block d-none" href="#">
                                        <img class="media-object" src="{{$item.market_details.thumbnail}}" alt="{{$item.market_details.name}} image" style="width: 64px;height: 64px; object-fit: cover;">
                                    </a>

                                    <div class="media-body">
                                        <h4 class="media-heading"><b>{{$item.market_details.name}}</b></h4>
                                        <span ng-bind-html=$item.market_details.short_description></span>
                                    </div>

                                    <div class=" quantity">
                                        Qty:<input style="width:35px;" ng-change="$shop.$cart.update_server();" type="number" class="quantity-input" ng-model="$item.qty" id="qty-4" min="1">
                                        <a href="javascript:void(0);" ng-click="$shop.$cart.remove_item($item)" class=" text-danger far fa-times-circle"></a>

                                        <div style="margin-top: 10px; text-align:right;">
                                            <b>${{$item.market_details.price * $item.qty}}</b> <br>
                                            <small>${{$item.market_details.price }} x 2 items</small>
                                        </div>
                                    </div>

                                </div>
                                <br>
                                <a href="javascript:void;" ng-click="$shop.$cart.empty_cart()" class="btn btn-rounded btn-outline-danger  btn-sm">Empty Cart</a>
                                <a href="<?= $domain; ?>/user/marketplace" class=" btn btn-rounded btn-outline-secondary  btn-sm"> Continue Shopping</a><br>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sidebar-content card  d-lg-block col-md-3">
                <div class="card-body">
                    <h6>Summary</h6>
                    <table class="table table-striped">
                        <tr>
                            <th style="padding: 5px;">Order</th>
                            <td class="text-right" style="padding: 5px;">
                                <span ng-bind-html="$shop.$config.currency"></span> {{($shop.$cart.calculate_total()) |  number:2}}
                            </td>
                        </tr>

                        <tbody id="payment_breakdown">

                            <tr class="order-total">
                                <th style="padding: 5px;">Total Payable</th>
                                <td class="text-right" style="padding: 5px;"><b>
                                        $
                                        {{($shop.$cart.calculate_total()) |  number:2}}

                                    </b>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <form id="" class="ajax_form" data-function="on_complete_order" method="post" action="<?= domain; ?>/shop/complete_order/make_payment">
                        <textarea style="display: none;" name="cart">
                        {{$shop.$cart}}
                        </textarea>
                        <div class="form-group">
                            <!-- <label>Pay With* </label> <small class="float-right"> </small> -->

                            <!--  <select class="form-control" required="" name="payment_method" onchange="present_payment_methods(form);">
                                <option value="">Select Payment method</option>
                                <?php foreach ($shop->get_available_payment_methods() as $key => $option) : ?>
                                    <option value="<?= $key; ?>"><?= $option['name']; ?></option>
                                <?php endforeach; ?>
                            </select> -->

                            <input type="hidden" value="wallet" name="payment_method">
                        </div>

                        <button id="get_breakdown_btn" type="button" style="display: none;" class="btn btn-dark" onclick="get_breakdown(form)"> Set Payment Method</button>
                        <button type="submit" class="btn btn-success" style="" id="submit_btn">Continue >></button>
                    </form>


                    <script>
                        get_breakdown = function($form) {
                            // console.log($form);
                            $($form).attr('data-function', 'on_complete_breakdown');
                            $('#submit_btn').click();
                        }

                        on_complete_breakdown = function($data) {
                            $('#payment_breakdown').html($data.breakdown.line);
                            $('#get_breakdown_btn').css('display', 'none');
                            $('#submit_btn').css('display', 'block');

                            $action = $base_url + '/shop/complete_order/make_payment';
                            $($form).attr('action', $action);
                            $($form).attr('data-function', 'on_complete_order');
                            // console.log($form);
                        }


                        present_payment_methods = function($form) {
                            $('#submit_btn').css('display', 'none');
                            $('#get_breakdown_btn').css('display', 'block');

                            $action = $base_url + '/shop/complete_order/get_breakdown';
                            $($form).attr('action', $action);
                            $($form).attr('data-function', 'on_complete_breakdown');

                            get_breakdown($form);

                        }

                        on_complete_order = function($data) {

                            try {
                                const queryString = new URLSearchParams($data);

                                switch ($data.gateway) {
                                    case 'rave':
                                        payWithRave($data);
                                        break;

                                    case 'paypal':
                                        // code block
                                        window.location.href = `${$base_url}/shop/checkout?${queryString}`
                                        break;
                                    case 'stripe':
                                        // code block
                                        window.location.href = `${$base_url}/shop/checkout?${queryString}`
                                        break;

                                    default:
                                        // code block
                                        // window.location.href = `${$base_url}/user/my-orders`

                                        break;
                                }

                            } catch (e) {}
                        }
                    </script>


                </div>

            </div>
        </section>



    </div>
    <style>
        .remove-item {
            position: absolute;
            right: 20px;
            top: 20px;
        }
    </style>

    <?php include 'includes/footer.php'; ?>