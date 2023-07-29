<?php
$page_title = "Profile";
include 'includes/header.php';?>

<script src="<?=general_asset;?>/js/angulars/registration.js"></script>

<!-- BEGIN: Content-->
<div class="app-content content" ng-controller="RegisterationController">
  <div class="content-wrapper">
    <div class="content-header row">
      <div class="content-header-left col-md-6 col-12 mb-2">
        <?php include 'includes/breadcrumb.php';?>

        <h3 class="content-header-title mb-0">Profile </h3>
      </div>

    </div>
    <div class="content-body">

      <section id="video-gallery" class="card">
        <div class="card-header">
          <h4 class="card-title">Profile <?=$user->VerifiedBagde;?></h4>
          <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
          <div class="heading-elements">
            <ul class="list-inline mb-0">
              <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
              <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
            </ul>
          </div>
        </div>

        <style>
          .full_pro_pix{
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 100%;
            border: 1px solid #cc444433;
          }
        </style>


        <div class="card-content">

          <div class="card-body row">
            <div class="col-md-4" style="
            margin-bottom: 20px;
            border: 1px solid #14181f42;
            padding: 19px;">
            <form class="form-horizontal ajax_form" 
            id="registration_form" method="post" enctype="multipart/form-data" action="<?=domain;?>/user-profile/update_profile_picture">
            <div class="user-profile-image" align="center" style="">
              <img id="myImage" src="<?=domain;?>/<?=$user->profilepic;?>" alt="your-image" class="full_pro_pix" />
              <input type='file' name="profile_pix" onchange="form.submit();" id="uploadImage" style="display:none ;" />
              <h4><?=ucfirst($user->username);?></h4>
              <h4><?=ucfirst($user->fullname);?></h4>
              <?=$user->activeStatus;?>
              <!-- <label for="uploadImage" class="btn btn-secondary " style=""> Change Picture</label> -->

              <br>
              <!-- <span class="text-danger">*click update profile to apply change</span> -->
            </div>
          </form>
          <hr>


          <div class="col-md-12">
            <?php foreach (v2\Models\UserDocument::$document_types as $key => $type) :?>
           
              <!-- <div class=" card"> -->
                  <div class="card-header">
                    <!-- <h4 class="card-title" style="display: inline;"> -->
                      <a data-toggle="collapse" title="click to see uploaded documents" href="#collapse1<?=$key;?>"><i class="ft-caret"></i> <?=$type['name'];?></a>

                      <form class="ajax_for float-right" method="post"  action="<?=domain;?>/user_doc_crud/upload_document" 
                        enctype="multipart/form-data">
                        <input style="display:none; " type="file" name="document" onchange="form.submit();">
                        <?php 
                          $document = $user->documents->where('document_type', $key)->first();
                        if ((($document != null) && (! $document->is_status(2))) || ($document == null) ):?>
                          <button class="btn btn-dark btn-sm" type="button" onclick="form.document.click();">+ Upload</button> 
                        <?php endif;?>
                        <input type="hidden" name="type" value="<?=$key;?>">
                      </form>                                

                    <!-- </h4> -->
                  </div>
                  <div id="collapse1<?=$key;?>" class=" collapse show" >
                    <div class="card-body">
                      <ul class="list-group list-group-flush">
                        <?php $i=1; foreach ($user->documents->where('document_type', $key) as $key => $doc) :?>
                        <!-- The Modal -->
                        <div class="modal" id="myModal<?=$doc->id;?>">
                          <div class="modal-dialog modal-lg  bg-dark">
                            <div class="modal-content"style="background: black;">

                              <!-- Modal Header -->
                              <div class="modal-header"  style="background: black; border-color: black; ">
                                <h4 class="modal-title"> <?=$type['name'];?> - <?=$doc->DisplayStatus;?>  </h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                              </div>

                              <!-- Modal body -->
                              <div class="modal-body text-center" style="background: black;">
                                <img src="<?=domain;?>/<?=$doc->path;?>" style="width: 100%;object-fit: contain;">
                              </div>

                            
                            </div>
                          </div>
                        </div>






                          <li class="list-group-item card-header"><?=$i;?>) <?=$doc->DisplayStatus;?>  
                          <a href="<?=domain;?>/<?=$doc->path;?>" data-toggle="modal" data-target="#myModal<?=$doc->id;?>"  class="float-right custom-warning btn btn-sm">Open</a><br>
                            <!-- <small>hyuu uho i</small> -->
                          </li>
                        <?php break; $i++; endforeach ;?>
                      </ul>
                        
                    </div>
                  </div>
            <?php endforeach ;?>

                                          

            </div>



             <div class="col-md-12 card">
               <div class="card-header">
                 <h4 class="card-title">
                   <a data-toggle="collapse" href="#change_password">Change Password</a>
                 </h4>
               </div>
               <div id="change_password" class=" collapse show">
            <div class="card-body card-body-bordered collapse show" id="demo1" >

            <div class=" text-center">
                <form  method="post" class="ajax_form" action="<?=domain;?>/user-profile/admin_change_password" style="padding: 10px;">
                    <?=@$this->csrf_field('change_password');?>

                    <div class="form-group mb-0">
                        <input type="password" required="required"  name="new_password" class="form-control" placeholder="New Password">
                        <span class="text-danger"><?=@$this->inputError('new_password');?></span>
                    </div>

                    <input type="hidden" name="id" value="<?=$user->id;?>">

                    <div class="form-group mb-1">
                        <input type="password" required="required" name="confirm_password" class="form-control" placeholder="Confirm password">
                        <span class="text-danger"><?=@$this->inputError('confirm_password');?></span>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <button type="button" onclick="$confirm_dialog = 
                                  new DialogJS( submit_form, [form], 'Are you sure ?')"  class="btn btn-outline-dark btn-block">Submit</button>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>

            </div>
              </div>
            </div>
          </div>


        </div>

        <script>
          submit_form = function($form){
            $form.submit();
          }
          
        </script>

        <div class="col-md-8" style="
        margin-bottom: 20px;
        border: 1px solid #14181f42;
        padding: 19px;">

        <div class=" card">
          <div class="card-header">
            <h4 class="card-title">
              <a data-toggle="collapse" href="#collapse1">Profile</a>
            </h4>
          </div>
          <div id="collapse1" class=" collapse show">
       <div class="card-body card-body-bordered collapse show" id="demo1" >
         <form id="profile_form"
         class="ajax_form" 
         action="<?=domain;?>/user-profile/update_profile_by_admin" method="post">
         <div class="form-group">
           <label for="username" class="pull-left">Username *</label>
           <input type="text"  name="username" disabled="" value="<?=$user->username;?>" id="username" class="form-control" value="">
         </div>

         <div class="form-group">
           <label>Gender</label>
           <select  class="form-control form-control" name="gender" >
             <option value="">Select</option>
             <?php foreach (User::$genders as $key => $value) :?>
               <option value="<?=$key;?>" <?=($user->gender==$key)? 'selected' : '';?>><?=$value;?></option>
             <?php endforeach ;?>
           </select>
         </div>

          <input type="hidden" name="user_id"  value="<?=MIS::dec_enc('encrypt', $user->id);?>">

         <div class="form-group">
           <label for="firstName" class="pull-left">First Name *</label>
           <input  type="text" name="firstname"  value="<?=$user->firstname;?>" id="firstName" class="form-control">
         </div>

         <div class="form-group">
           <label for="lastName" class="pull-left">Last Name <sup>*</sup></label>
           <input   type="text" name="lastname" id="lastName" class="form-control"  value="<?=$user->lastname;?>">
         </div>

         <div class="form-group">
           <label for="birthdate" class="pull-left">Birth Date <sup>*</sup></label>
           <input  type="date" name="birthdate" id="birthdate" class="form-control"  value="<?=$user->birthdate;?>">
         </div>

         <div class="form-group">
           <label for="email" class="pull-left">Email Address<sup>*</sup></label>
           <div class="input-group bootstrap-touchspin bootstrap-touchspin-injected">
             <span class="input-group-btn input-group-prepend"></span>
             <input  id="tch3" name="email"   value="<?=$user->email;?>"
             data-bts-button-down-class="btn btn-secondary btn-outline" data-bts-button-up-class="btn btn-secondary btn-outline" class="form-control">
             <span class="input-group-btn input-group-append">
               <button class="btn btn-sm btn-outline bootstrap-touchspin-up" type="button">Require Verification</button>
             </span>
           </div> 
         </div>


         <div class="form-group">
           <label for="phone" class="pull-left">Phone<sup>*</sup></label>
           <div class="input-group bootstrap-touchspin bootstrap-touchspin-injected">
             <span class="input-group-btn input-group-prepend"></span>
             <input id="tch3" name="phone"   value="<?=$user->phone;?>"
             data-bts-button-down-class="btn btn-secondary btn-outline" data-bts-button-up-class="btn btn-secondary btn-outline" class="form-control">
             <span class="input-group-btn input-group-append">
               <button class="btn btn-sm btn-outline bootstrap-touchspin-up" type="button">Require Verification</button>
             </span>
           </div> 
         </div>                                        


         <div class="form-group">
           <label for="address" class="pull-left">Address <sup>*</sup></label>
           <input type="text" name="address" id="address" class="form-control"  value="<?=$user->address;?>">
         </div>


         <div class="form-group">
           <label for="country" class="pull-left">Country<sup>*</sup></label>
           <select class="form-control" name="country">
            <option value=""></option>
            <?php foreach (World\Country::all() as $key => $country) :?>
              <option <?=($user->country == $country->id)?'selected' : '';?> value="<?=$country->id;?>"><?=$country->name;?></option>
            <?php endforeach ;?>
          </select>
        </div>




          <!--    <div class="form-group">
                 <label for="bank_name" class="pull-left">Bank Name <sup>*</sup></label>
                 <input type="" name="bank_name"  value="<?=$user->bank_name;?>" id="bank_name" class="form-control" >
             </div>

               
           
             <div class="form-group">
                <label for="bank_account_name" class="pull-left">Bank Account Name<sup></sup></label>
                 <input type="bank_account_name" name="bank_account_name"  value="<?=$user->bank_account_name;?>" id="bank_account_name" class="form-control" >
             </div>

           
           

           
             <div class="form-group">
                <label for="bank_account_number" class="pull-left">Bank Account Number <sup></sup></label>
                 <input type="bank_account_number" name="bank_account_number"  value="<?=$user->bank_account_number;?>" id="bank_account_number" class="form-control" >
               </div> -->


               <div class="form-group">

                 <button type="submit" class="btn btn-secondary btn-block btn-flat">Update Profile</button>

               </div>
             </form>

           </div>

  </div>
</div>



</div>



</div>
</div>
</section>

</div>
</div>
</div>
<!-- END: Content-->

<?php include 'includes/footer.php';?>
