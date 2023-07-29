   <div class="dropdown">
       <button type="button" class="btn btn-dark btn-sm dropdown-toggle" data-toggle="dropdown">
           <i class="fa fa-filter"></i>
       </button>
       <div class="dropdown-menu" style="padding: 20px;">
           <form action="<?= $action ?? ''; ?>" method="get" id="filter_form">
               <div class="row">

                   <div class="form-group col-sm-6">
                       <label>Ref(ID)</label><br>
                       <input type="" name="journal[ref]" placeholder="ID (Ref)" class="form-control" value="<?= $sieve['journal']['ref'] ?? ''; ?>">
                   </div>



                   <div class="form-group col-md-6">
                       <label>Status</label>
                       <select class="form-control" name="journal[status]">
                           <option value="">Select</option>
                           <?php foreach ([1 => 'Draft', 2 => 'Pending', 3 => 'Published'] as $key => $value) : ?>
                               <option <?= (isset($sieve['journal']['status']) && ($sieve['journal']['status'] == (int)$key)) ? 'selected' : ''; ?> value="<?= $key; ?>">
                                   <?= $value; ?>
                               </option>
                           <?php endforeach; ?>
                       </select>
                   </div>


                   <div class="form-group col-md-6">
                       <label>Type</label>
                       <select class="form-control" name="line_items[type]">
                           <option value="">Select</option>
                           <?php foreach (['credit', 'debit'] as $key => $value) : ?>
                               <option <?= (isset($sieve['line_items']['type']) && ($sieve['line_items']['type'] == $value)) ? 'selected' : ''; ?> value="<?= $value; ?>">
                                   <?= $value; ?>
                               </option>
                           <?php endforeach; ?>
                       </select>
                   </div>



                   <div class="form-group col-sm-6">
                       <label>Description</label><br>
                       <input type="" name="line_items[notes]" placeholder="Description" class="form-control" value="<?= $sieve['line_items']['notes'] ?? ''; ?>">
                   </div>


               </div>




               <div class="row">
                   <div class=" form-group col-sm-6">
                       <label>* Amount (From):</label>
                       <input placeholder="Start" type="number" value="<?= $sieve['journal']['amount']['start'] ?? ''; ?>" class="form-control" name="journal[amount][start]">
                   </div>


                   <div class=" form-group col-sm-6">
                       <label>* Amount (To)</label>
                       <input type="number" placeholder="End " value="<?= $sieve['journal']['amount']['end'] ?? ''; ?>" class="form-control" name="journal[amount][end]">
                   </div>

               </div>


               <div class="row">
                   <div class=" form-group col-sm-6">
                       <label>* Value date (From):</label>
                       <input placeholder="Start" type="date" value="<?= $sieve['journal']['journal_date']['start_date'] ?? ''; ?>" class="form-control" name="journal[journal_date][start_date]">
                   </div>


                   <div class=" form-group col-sm-6">
                       <label>* Value date (To)</label>
                       <input type="date" placeholder="End " value="<?= $sieve['journal']['journal_date']['end_date'] ?? ''; ?>" class="form-control" name="journal[journal_date][end_date]">
                   </div>

               </div>

               <div class="row">
                   <div class=" form-group col-sm-6">
                       <label>* Trans date (From):</label>
                       <input placeholder="Start" type="date" value="<?= $sieve['journal']['created_at']['start_date'] ?? ''; ?>" class="form-control" name="journal[created_at][start_date]">
                   </div>


                   <div class=" form-group col-sm-6">
                       <label>* Trans date (To)</label>
                       <input type="date" placeholder="End " value="<?= $sieve['journal']['created_at']['end_date'] ?? ''; ?>" class="form-control" name="journal[created_at][end_date]">
                   </div>


               </div>


               <div class="form-group">
                   <button type="Submit" class="btn btn-primary">Submit</button>
                   <!-- <a  onclick="$('#filter_form').reset()">Reset</a> -->
               </div>
           </form>

       </div>
   </div>