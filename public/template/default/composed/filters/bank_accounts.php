       <div class="dropdown">
           <button type="button" class="btn btn-dark btn-sm dropdown-toggle" data-toggle="dropdown">
               <i class="fa fa-filter"></i>
           </button>
           <div class="dropdown-menu" style="padding: 20px;">
               <form action="<?= $action ?? ''; ?>" method="get" id="filter_form">
                   <div class="row">

                       <div class="form-group col-sm-6">
                           <label>Ref(ID)</label><br>
                           <input type="" name="ref" placeholder="ID (Ref)" class="form-control" value="<?= $sieve['ref'] ?? ''; ?>">
                       </div>


                       <div class="form-group col-sm-6">
                           <label>Account</label><br>
                           <input type="" name="name" placeholder="Account Number, Name" class="form-control" value="<?= $sieve['name'] ?? ''; ?>">
                       </div>
                   </div>

                   <div class="row">
                       <div class="form-group col-sm-6">
                           <label>Client Email</label><br>
                           <input type="email" name="email" class="form-control" value="<?= $sieve['email'] ?? ''; ?>">
                       </div>
                       <div class="form-group col-sm-6">
                           <label>Phone</label><br>
                           <input type="text" name="phone" class="form-control" value="<?= $sieve['phone'] ?? ''; ?>">
                       </div>
                   </div>



                   <div class="row">
                       <div class=" form-group col-sm-6">
                           <label>* opened(From):</label>
                           <input placeholder="Start" type="date" value="<?= $sieve['opened']['start_date'] ?? ''; ?>" class="form-control" name="opened[start_date]">
                       </div>


                       <div class=" form-group col-sm-6">
                           <label>* opened (To)</label>
                           <input type="date" placeholder="End " value="<?= $sieve['opened']['end_date'] ?? ''; ?>" class="form-control" name="opened[end_date]">
                       </div>


                   </div>


                   <div class="form-group">
                       <button type="Submit" class="btn btn-outline-secondary">Submit</button>
                       <!-- <a  onclick="$('#filter_form').reset()">Reset</a> -->
                   </div>
               </form>

           </div>
       </div>