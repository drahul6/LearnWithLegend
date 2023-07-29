<div id="content" ng-controller="ShopController">



  <section id="image-grid">

    <div class="card-header" style="display:none;">
      <h4 class="card-title col-md-6">Items <span class="badge badge-secondary"><?= (@$category); ?></span>
      </h4>
      <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
      <div class="heading-elements">
        <input type="" placeholder="Search..." class="form-control" ng-model="searchText">
      </div>
    </div>

    <div class="row" ng-cloak>
      <div class="col-md-12" ng-show="$shop.$items.length==0">
        <div class="alert alert-dark text-center">
          Items for sale will display here <i class="fa fa-spin fa-circle-notch"></i>
        </div>
      </div>


      <div class="col-md-3" ng-repeat="($index, $item) in $shop.$items  | filter:searchText">

        <div class="card custom-shadow">
          <img class="card-img-top img-fluid img-responsive" src="{{$item.market_details.thumbnail}}" alt="" style="object-fit: cover;height: 250px;">
          <div class="card-body">
            <a href="{{$item.market_details.single_link}}">
              <h5 style="text-transform:capitalize;">{{$item.market_details.name}}</h5>
            </a>

            <div class="">
              <del class="cent" ng-show="$item.market_details.old_price != undefined">
                <span ng-bind-html="$shop.$config.currency"></span>{{$item.market_details.old_price}}
              </del>
              <b class=""> <span ng-bind-html="$shop.$config.currency"></span>{{$item.market_details.price | number:2}}</b>
            </div>

            <div>
              <small><span class="text-muted">Sold by</span> {{$item.market_details.by}}</small>
              <small style="display:block;"><span class="text-muted  fas fa-map-marker-alt"></span> {{$item.data.country}} | {{$item.type_of_product}}</small>

            </div>
            <!-- <span class="course-subtext">
              <span class="" ng-bind-html='$item.market_details.star_rating.stars'></span>
            </span>
            -->
            <span class="">
              <i data-feather="file-text" class="feather-icon"></i>
              <!-- ng-click="$shop.$cart.add_item($item)" -->
              <a href="{{$item.market_details.buy_now_link}}"><span class="btn btn-rounded btn-outline-secondary  btn-sm"> <i class="fas fa-cart-plus"></i> Buy </span></a>
              <a href="{{$item.market_details.single_link}}"><span class=" btn btn-rounded btn-outline-secondary btn-sm"> <i class="fas fa-eye"></i> View</span> </a>
              <small ng-hide="$item.market_details.stock ==undefined">{{$item.market_details.stock}} stock </small>
            </span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <center ng-cloak>
    <!--     <button ng-click="fetch_more_items()" ng-hide="$hide_more_btn || $shop.$items.length==0" class="btn btn-rounded btn-outline-secondary  btn-sm">Load More</button>
    <button ng-show="$hide_more_btn" class="btn btn-rounded btn-outline-light  btn-sm">No More Records</button>
 -->
    <ul class="pagination pagination-sm">
      <?= $this->pagination_links($data, $per_page); ?>
    </ul>
  </center>




  <!-- Modal -->
  <div id="quick_view_modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">

      <div class="modal-content">
        <!--     <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel17">{{$shop.$quickview.title}} </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
                </div> -->
        <div class="modal-body">

          <span ng-bind-html="$shop.$quickview.market_details.quickview"></span>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn grey btn-outline-light" data-dismiss="modal">Close</button>
          <button ng-click="$shop.$cart.add_item($shop.$quickview)" type="button" class="btn btn-outline-secondary">
            <i class="ft ft-shopping-cart"></i>
            Buy Now</button>
        </div>
      </div>
    </div>
  </div>
</div>