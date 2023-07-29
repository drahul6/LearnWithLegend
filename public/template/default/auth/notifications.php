<?php
$page_title = "Notifications";
include 'includes/header.php';
?>
     
        <div class="page-wrapper">

            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Notifications</h3>
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
              
                
             <section id="video-gallery" class="card">
              <!--  <div class="card-header">
                 <h4 class="card-title">Notifications</h4>
                 <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                     <div class="heading-elements">
                       <ul class="list-inline mb-0">
                           <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                           <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                       </ul>
                   </div>
               </div> -->
                <div class="card-content">
             <div class="card-body">
             
             <?php if(is_iterable($notifications)):?>

                 <?php foreach ($notifications as $notification):?>


                     <div class="timeline-panel notification">
                         <div class="timeline-heading">
                             <h4 class="timeline-title">
                                 <a href="<?=domain;?>/user/notifications/<?=$notification->id;?>">
                                     <?=$notification->heading;?></a>
                                     
                             </h4>
                             <p>
                                 <small class="text-muted"><i class="far fa-clock"></i> 
                                     <?=$notification->created_at->format("M j Y - H:i A");?>
                                 </small>

                                 <small class="text-muted float-right">
                                     <?=$notification->seen_status();?>
                                 </small>
                             </p>
                         </div>
                         <div class="timeline-body">
                             <p><?=$notification->Intro;?></p>
                         </div>
                     </div>


                 <?php endforeach;?>

                 <?php if ($notifications->isEmpty()) :?>
                   <center>  Your Notifications will appear here </center>
                 <?php endif  ;?>

                 
             <?php else:?>


                 <div class="timeline-panel notification">
                     <div class="timeline-heading">
                         <h4 class="timeline-title"><?=$notifications->heading;?></h4>
                         <p>
                             <small class="text-muted"><i class="far fa-clock"></i> 
                                 <?=$notifications->created_at->format("M j Y - H:i A");?>
                             </small>
                         </p>
                     </div>
                     <div class="timeline-body" style="overflow-x: scroll;">
                         <p><?=$notifications->message;?></p>
                     </div>
                 </div>

             <?php endif;?>


             </div>
           </div>
             </section>




                <?php if (isset($total)) :?>
                <ul class="pagination">
                 <?= $this->pagination_links($total, $per_page);?>
               </ul>
                <?php endif  ;?>




            </div>

<?php include 'includes/footer.php';?>
