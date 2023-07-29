<?php
$page_title = "Bank Detail";
include 'includes/header.php';
?>
     
        <div class="page-wrapper">

            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Bank Detail</h3>
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
              
              
              <div class="row" >
                <div class="col-12">
                  <div class="card">
                   
                    <div class="card-body  collapse show" id="make_deposit<?=$key;?>">


                       <form id="bank_info_form"
                       class="ajax_for" 
                       action="<?=domain;?>/user-profile/update_bank_info" method="post">                               

                       <?=$auth->bank_detail->account_name ?? '';?><hr>
                       
                         <div class="form-group">
                             <label for="bank_name" class="pull-left">Bank Name <sup>*</sup></label>
                             <select name="bank_id" class="form-control">
                               <option value="">Select</option>
                               <?php foreach ($financial_banks as  $bank):?> 
                                 <option value="<?=$bank->id;?>" 
                                   <?=((isset($auth->bank_detail->bank_id)) && ($bank->id == $auth->bank_detail->bank_id)) ?'selected' : '';?>><?=$bank->bank_name;?></option>
                               <?php endforeach ;?> 

                             </select>
                         </div>
                           
                         <div class="form-group">
                            <label for="account_number" class="pull-left">Bank Account Number <sup></sup></label>
                             <input type="account_number" name="account_number"  value="<?=$auth->bank_detail->account_number??'';?>" id="account_number" class="form-control" >
                         </div>


                         <div class="form-group">

                               <button type="submit" class="btn btn-secondary  btn-flat">Save</button>

                         </div>
                       </form>







                    </div>

                  </div>
                </div>
              </div>



            </div>

<?php include 'includes/footer.php';?>
