           <div class="dropdown" style="display: inline;">
               <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown">
                   <i class="fa fa-filter"></i>
               </button>

               <div class="dropdown-menu" style="padding: 20px; ">
                   <form action="<?= $action ?? ''; ?>" method="get" id="filter_form" style="width:400px;">
                       <div class="row">
                           <div class="form-group col-md-6 col-xs-12">
                               <label>Ref</label><br>
                               <input type="" name="ref" class="form-control" value="<?= $sieve['ref'] ?? ''; ?>">
                           </div>


                           <div class="form-group col-md-6 col-xs-12">
                               <label>Buyer</label><br>
                               <input type="" name="user" placeholder="First or Last Name or email, phone ,username" class="form-control" value="<?= $sieve['user'] ?? ''; ?>">
                           </div>
                           <div class="form-group col-md-6 col-xs-12">
                               <label>Seller</label><br>
                               <input type="" name="seller" placeholder="First or Last Name or email, phone ,username" class="form-control" value="<?= $sieve['sellers'] ?? ''; ?>">
                           </div>

                       </div>


                       <div class="row">

                           <div class="form-group col-md-6">
                               <label>Status</label>
                               <select class="form-control" name="status">
                                   <option value="">Select</option>

                                   <?php foreach (Orders::$statuses_config['states'] as $state) : ?>
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
                               <input placeholder="Start" type="number" value="<?= $sieve['price']['start'] ?? ''; ?>" class="form-control" name="price[start]">
                           </div>


                           <div class=" form-group col-sm-6">
                               <label>* Price (To)</label>
                               <input type="number" placeholder="End " value="<?= $sieve['price']['end'] ?? ''; ?>" class="form-control" name="price[end]">
                           </div>
                       </div>

                       <!--    <div class="row">

                           <div class="form-group col-md-6 col-xs-12">
                               <label>Paid Status</label>
                               <select class="form-control" name="payment_status">
                                   <option value="">Select</option>
                                   <?php foreach (['unpaid' => 'UnPaid', 'paid' => 'Paid',] as $key => $value) : ?>
                                       <option <?= (isset($sieve['payment_status']) &&  $sieve['payment_status'] == $key) ? 'selected' : ''; ?> value="<?= $key; ?>">
                                           <?= $value; ?>
                                       </option>
                                   <?php endforeach; ?>
                               </select>
                           </div>

                           <div class="form-group col-md-6 col-xs-12">
                               <label>Payment Method</label>
                               <select class="form-control" name="payment_method">
                                   <option value="">Select</option>
                                   <?php foreach ($shop->available_payment_method as $key => $value) : ?>
                                       <option <?= (isset($sieve['payment_method']) && $sieve['payment_method'] == $value['name']) ? 'selected' : ''; ?> value="<?= $key; ?>">
                                           <?= $value['name']; ?>
                                       </option>
                                   <?php endforeach; ?>
                               </select>
                           </div>

                       </div>
 -->

                       <div class="row">
                           <div class=" form-group col-md-6 col-xs-6">
                               <label>* Ordered(From):</label>
                               <input placeholder="Start" type="date" value="<?= $sieve['ordered']['start_date'] ?? ''; ?>" class="form-control" name="ordered[start_date]">
                           </div>


                           <div class=" form-group col-md-6 col-xs-6">
                               <label>* Ordered (To)</label>
                               <input type="date" placeholder="End " value="<?= $sieve['ordered']['end_date'] ?? ''; ?>" class="form-control" name="ordered[end_date]">
                           </div>


                       </div>


                       <div class="form-group">
                           <button type="Submit" class="btn btn-primary">Submit</button>
                           <!-- <a  onclick="$('#filter_form').reset()">Reset</a> -->
                       </div>
                   </form>

               </div>
           </div>