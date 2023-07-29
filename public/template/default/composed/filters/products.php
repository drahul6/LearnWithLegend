       <div class="dropdown">
           <button type="button" class="btn btn-dark btn-sm dropdown-toggle" data-toggle="dropdown">
               <i class="fa fa-filter"></i>
           </button>
           <div class="dropdown-menu" style="padding: 20px;">
               <form action="<?= $action ?? ''; ?>" method="get" id="filter_form">
                   <div class="row">

                       <div class="form-group col-sm-6">
                           <label>Product</label><br>
                           <input type="" name="name" placeholder="" class="form-control" value="<?= $sieve['name'] ?? ''; ?>">
                       </div>

                       <div class="form-group col-sm-6">
                           <label>Seller</label><br>
                           <input type="" name="user" placeholder="First, Last, Middle Name, Phone, Email, Username" class="form-control" value="<?= $sieve['user'] ?? ''; ?>">
                       </div>
                   </div>



                   <div class="row">


                       <div class="form-group col-md-6">
                           <label>Status</label>
                           <select class="form-control" name="status">
                               <option value="">Select</option>

                               <?php foreach (Products::$statuses_config['states'] as $state) : ?>
                                   <option <?= ((isset($sieve['status'])) && ($sieve['status'] == (int)$key)) ? 'selected' : ''; ?> value="<?= $state['hierarchy']; ?>">
                                       <?= $state['name']; ?>
                                   </option>
                               <?php endforeach; ?>
                           </select>
                       </div>
                       <div class=" form-group col-md-6">
                           <label>Product Type</label>
                           <select class="form-control" name="type_of_product">
                               <option value="">Select</option>
                               <?php foreach (Products::$types_of_product as $value) : ?>
                                   <option <?= @$sieve['type_of_product'] == $value ? 'selected' : ""; ?>><?= $value; ?></option>
                               <?php endforeach; ?>
                           </select>
                       </div>

                   </div>


                   <div class="row">
                       <div class=" form-group col-sm-6">
                           <label>* Price (From):</label>
                           <input placeholder="Start" type="number" value="<?= $sieve['price']['start_date'] ?? ''; ?>" class="form-control" name="price[start_date]">
                       </div>


                       <div class=" form-group col-sm-6">
                           <label>* Price (To)</label>
                           <input type="number" placeholder="End " value="<?= $sieve['price']['end_date'] ?? ''; ?>" class="form-control" name="price[end_date]">
                       </div>
                   </div>


                   <div class="row">
                       <div class=" form-group col-sm-6">
                           <label>* Created (From):</label>
                           <input placeholder="Start" type="date" value="<?= $sieve['created']['start_date'] ?? ''; ?>" class="form-control" name="created[start_date]">
                       </div>


                       <div class=" form-group col-sm-6">
                           <label>* Created (To)</label>
                           <input type="date" placeholder="End " value="<?= $sieve['created']['end_date'] ?? ''; ?>" class="form-control" name="created[end_date]">
                       </div>


                   </div>


                   <div class="form-group">
                       <button type="Submit" class="btn btn-primary">Submit</button>
                       <!-- <a  onclick="$('#filter_form').reset()">Reset</a> -->
                   </div>
               </form>

           </div>
       </div>