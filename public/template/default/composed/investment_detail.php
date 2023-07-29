              <div class="row match-height">   

                <div class="col-md-6">

                  <?php

                  $detail = $pack->ExtraDetailArray;
                  $display_growth = $pack->maturity * 0.01 * $pack->ExtraDetailArray['annual_roi'] ;

                  ;?>

                  <style>
                    .card{
                      border-radius: 10px;
                    }
                    .small-padding{

                      padding-top: 2px !important;
                      padding-bottom: 2px !important;
                      background: #073f2d00;  

                    }


                  </style>          
                  <img src="<?=$asset;?>/images/logo/packs/<?=$pack->ExtraDetailArray['investment']['pack_id'];?>.png"
                  alt=" <?=$pack->ExtraDetailArray['investment']['name'];?>" width="190" class="float-left img-fluid">
                  <br>
                  <h4 class="card-title mt-1">YOUR FINANCE PACK: ID#<?=$pack->id;?> <br>
                   <span class="badge badge-dark"> <?=$pack->ExtraDetailArray['investment']['name'];?> </span> - 
                   <?=$currency;?><?=$pack->ExtraDetailArray['capital'];?>
                 </h4>
                 <div class="progress progress-sm mt-1 mb-0">
                  <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:<?=$pack->maturity;?>%"><?=$display_growth;?>%</div>              
                </div>
                <p class="card-text">Your Pack Expires when it reaches <?=$pack->ExtraDetailArray['annual_roi'];?>% return.</p>
                <a href="<?=domain;?>/user/purchase-investment" class="btn custom-warning btn-whiteen-3">Target: <?=$currency;?><?=$detail['total_worth'];?></a>
              </div>



              <div class="col-md-6">


                <div class="card  text-left col-12">
                  <div class="card-content">
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-12">                               
                          <ul class="list-group list-group-flush">
                            <?php if ((isset($context)) && ($context=='admin')):?>
                            <li class="small-padding list-group-item">User: <?=$pack->user->DropSelfLink;?> </li>
                          <?php endif;?>

                          <li class="small-padding list-group-item"> Target: <?=$currency;?><?=$detail['total_worth'];?>, Returned: <?=$currency;?><?=$pack['Returns'];?></li>
                          <li class="small-padding list-group-item"> Maturity: <?=$pack->maturity;?>%,  Status: <?=$pack->roiStatus;?></li>
                          <li class="small-padding list-group-item"> Purchase Date: 
                            <span class="badge badge-dark"><?=date("M j, Y h:ia" , strtotime($pack->paid_at));?></span>
                          </li>
                          <li class="small-padding list-group-item"> Last Paid Date: 
                            <span class="badge badge-primary">
                              <?=$pack->LastPaidDate;?>
                            </span>

                          </li>
                          <li class="small-padding list-group-item">ID: #<?=$pack->id;?></li>
                        </ul>

                      </div>


                    </div>

                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table">
              <thead>
                <th>Sn</th>
                <th>Date-Split</th>
                <th>Roi(<?=$currency;?>)</th>
                <th>Cummulative(<?=$currency;?>)</th>
                <!-- <th>Split</th> -->
              </thead>
              <tbody>
                <?php 
                $spread_dates = array_keys($detail['spread']);
                $i=1;$bal=0; foreach ($detail['spread'] as $spread_date => $chunk) :
                $bal+=$chunk;
                ?>
                <tr>
                  <td><?=$i++;?></td>
                  <td><span class="badge badge-secondary"><?=date("M j, Y" , strtotime($spread_date));?></span>
                    <?php if (in_array( $spread_date, $detail['split_dates']) || (end($spread_dates) ==$spread_date)) :?>
                    <span class="badge badge-success">Split<i class="fa fa-check-circle "></i></span>
                  <?php endif;?>

                </td>
                <td><?=$chunk;?></td>
                <td><?=$bal;?></td>
              </tr>
            <?php endforeach;?>
          </tbody>

            </table>
          </div>
