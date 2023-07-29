<?php
$page_title = "Account plan";
include 'includes/header.php';

$auth = $user;
;?>




<!-- BEGIN: Content-->
<div class="app-content content">
  <div class="content-wrapper">
    <div class="content-header row">
      <div class="content-header-left col-md-6 col-12 mb-2">
        <?php include 'includes/breadcrumb.php';?>

        <h3 class="content-header-title mb-0">Account plan <small><small>-Membership</small></small></h3>
      </div>

    </div>
    <div class="content-body">
      <div class="alert alert-light">
        <?=$user->dropSelfLink;?>
      </div>
      <hr>
      <style>
        .small-padding{
          padding: 3px;
        }

        .custom-lightgreen{
          background: #1e7257 ;
        }
      </style>

      <div class="row match-height">   

        <div class="col-md-2 d-none d-lg-block">   
          &nbsp;
        </div>
        <div class=" col-md-4">   
          <div class="card">   
           <div class="card-content">
            <div class="card-body">
              <h4 class="card-title">Black Partner</h4>
              <h6 class="card-subtitle text-mute"> <b class="float-right">
                <b>$</b>0.00<!--  /Month --></b>
              </h6> 
            </div>

            <div class="card-body">
              <!-- <h6 class="card-subtitle text-mute">Support card subtitle</h6> -->
              <!-- <p class="card-text">Excluding VAT 0% </p> -->
              <ul class="list-group list-group-flush">

                  <li class="list-group-item use-bg small-padding">
                    <span class="badge custom-blue float-right"><i class="fa fa-check"></i></span>
                      University Access (Limited)                          
                </li>

                  <li class="list-group-item use-bg small-padding">
                    <span class="badge custom-blue float-right"><i class="fa fa-check"></i></span>
                      Passive Income (Limited)                          
                </li>

                <li class="list-group-item use-bg small-padding">
                  <span class="badge bg-danger float-right"><i class="fa fa-times"></i></span>
                Participate in compensation plan                          </li>


                <li class="list-group-item use-bg small-padding">
                  <span class="badge bg-danger float-right"><i class="fa fa-times"></i></span>
                Get leadership rewards                          </li>


                <li class="list-group-item use-bg small-padding">
                  <span class="badge bg-danger float-right"><i class="fa fa-times"></i></span>
                Qualification for trips and quartely conventions                           </li>


                <li class="list-group-item use-bg small-padding">
                  <span class="badge bg-danger float-right"><i class="fa fa-times"></i></span>
                Recieve promotional items                           </li>


                <li class="list-group-item use-bg small-padding">
                  <span class="badge bg-danger float-right"><i class="fa fa-times"></i></span>
                Step by step guide to generate $10,000 commission                          </li>


          

              </ul>
              <br>
              <br>
              <br>
             
             <div class="form-group">
              <button type="button" class="btn  btn-light" onclick="$confirm_dialog =
               new ConfirmationDialog('<?=domain;?>/admin/choose_membership/<?=$user->id;?>/1', 'Are you sure ?')"  >Choose</button>
             </div>


            </div>
          </div>
        </div>
      </div>



      <?php foreach (SubscriptionPlan::available()->get() as  $subscription): 
      if ($subscription->price ==0) {continue;}
      ?>

      <div class=" col-md-4">   
        <div class="card">   
         <div class="card-content">
          <div class="card-body">
            <h4 class="card-title"><?=$subscription->name;?></h4>
            <h6 class="card-subtitle text-mute"> <b class="float-right">
              <?=$currency;?><?=MIS::money_format($subscription->price);?><!--  /Month --></b>
            </h6> 
          </div>

          <div class="card-body">
            <!-- <h6 class="card-subtitle text-mute">Support card subtitle</h6> -->
            <!-- <p class="card-text">Excluding VAT <?=(int)$subscription->percent_vat;?>% </p> -->
            <ul class="list-group list-group-flush">
              <?php foreach (SubscriptionPlan::$benefits as $key => $benefit): ?>

                <li class="list-group-item use-bg small-padding">
                  <?php if ($subscription->DetailsArray['benefits'][$key] == 1) :?>
                    <span class="badge badge-success float-right"><i class="fa fa-check"></i></span>
                    <?php else :?>
                      <span class="badge bg-danger float-right"><i class="fa fa-times"></i></span>
                    <?php endif ;?>
                    <?=$benefit['title'];?>
                  </li>

                <?php endforeach;?>
              </ul>
              <br>
              <?php if (@$auth->subscription->payment_plan['price']  < $subscription->price):?>
             
          <?php endif ;?>

          <?php if (@$auth->subscription->payment_plan['id']  == $subscription->id):?>
            <div class="form-group">
              <button type="button" class="btn btn-sm btn-success">Current</button>
              <small><?=$auth->subscription->NotificationText;?></small>
            </div>
          <?php else :?>

            <div class="form-group">
              <button type="button" class="btn  btn-light" onclick="$confirm_dialog =
               new ConfirmationDialog('<?=domain;?>/admin/choose_membership/<?=$user->id;?>/<?=$subscription->id;?>', 'Are you sure ?')"  >Choose</button>
            </div>

          <?php endif ;?>

        </div>
      </div>
    </div>
  </div>

<?php endforeach;?>
</div>



<script>

  submit_request = function($form){

      $form.submit();
  }



  initiate_payment= function($data){

    switch($data.payment_method) {
     case 'coinpay':
                       // code block
                       window.location.href = $base_url+ 
                       "/shop/checkout?item_purchased=packages&order_unique_id="+$data.id+"&payment_method=coinpay";

                       break;

                       case 'paypal':
                       // code block
                       window.location.href = $base_url+ 
                       "/shop/checkout?item_purchased=packages&order_unique_id="+$data.id+"&payment_method=paypal";

                       break;
                       case 'razor_pay':
                       // code block
                       window.SchemeInitPayment($data.id);
                       break;
                       default:
                       // code block
                     }
                   }
                 </script>

               </div>
             </div>
           </div>
           <!-- END: Content-->

           <?php include 'includes/footer.php';?>
