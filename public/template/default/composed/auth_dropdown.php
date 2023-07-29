  <?php

  $current_url = MIS::current_url();
  $allowed = [
    'user/cart',
    'user/marketplace',
  ];; ?>

  <script src="<?= asset; ?>/angulars/shop.js"></script>


  <?php if (in_array($current_url, $allowed) || true) : ?>

    <li ng-cloak ng-controller="CartNotificationController" id="cart-notification" class="dropdown  nav-item">
      <a class="nav-link dropdown-toggle pl-md-3 position-relative" href="javascript:void(0)" id="bell" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="display:none;">
        <span class="fas fa-shopping-cart"></span>
        <span class="badge badge-primary notify-no rounded-circle">{{$cart.$items.length}}</span>
      </a>

      <div class="dropdown-menu dropdown-menu-right mailbox animated bounceInDown" style="width:300px;">
        <ul class="list-style-none">

          <li ng-show="$cart.$items.length==0">
            <center class="dropdown-header m-0"><span class="grey darken-2">Your Cart is Empty</span>
            </center>
          </li>
          <li>

            <div class="message-center notifications position-relative ps-container ps-theme-default" ng-repeat="($index, $item) in $cart.$items" href="javascript:void(0)">
              <div class="w-100 d-inline-block v-middle p-2" style="border-bottom:1px solid #ededed">
                <h4 class="m-0">{{$item.market_details.short_name}}</h4>
                <small>
                  <span class="float-">x{{$item.qty}} qty</span>
                </small>
                <time class="text-muted float-right">
                  <span ng-bind-html="$cart.$config.currency"></span>{{$item.market_details.price | number:2}} x{{$item.qty}}
                </time>
              </div>
            </div>
          </li>
          <li class="dropdown-menu-header">
            <div class="pl-2 text-right" ng-hide="$cart.$items.length==0">
              <span class="grey darken-2">Total:
                <span ng-bind-html="$cart.$config.currency"></span>
                <b style="font-size: 20px;">{{($cart.calculate_total()) | number:2}} </b>
              </span>
              <!--<span class="notification-tag badge badge-default badge-danger float-right m-0">
                {{$cart.$items.length}} Item(s)
              </span>
               -->
            </div>
          </li>
          <li class="dropdown-menu-footer text-center" ng-hide="$cart.$items.length==0">
            <a class="text-muted text-center" href="<?= domain; ?>/user/cart"> Checkout >></a>
          </li>
        </ul>
      </div>
    </li>
  <?php endif; ?>