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


                       <div class="form-group col-md-6">
                           <label>Status</label>
                           <select class="form-control" name="status">
                               <option value="">Select</option>
                               <?php foreach ([1 => 'Draft', 2 => 'Pending', 3 => 'Published'] as $key => $value) : ?>
                                   <option <?= (isset($sieve['status']) && ($sieve['status'] == (int)$key)) ? 'selected' : ''; ?> value="<?= $key; ?>">
                                       <?= $value; ?>
                                   </option>
                               <?php endforeach; ?>
                           </select>
                       </div>



                       <div class="form-group col-sm-12">
                           <label>Notes</label><br>
                           <input type="" name="notes" placeholder="Description" class="form-control" value="<?= $sieve['notes'] ?? ''; ?>">
                       </div>


                   </div>




                   <div class="row">
                       <div class=" form-group col-sm-6">
                           <label>* Journal date (From):</label>
                           <input placeholder="Start" type="date" value="<?= $sieve['journal_date']['start_date'] ?? ''; ?>" class="form-control" name="journal_date[start_date]">
                       </div>


                       <div class=" form-group col-sm-6">
                           <label>* Journal date (To)</label>
                           <input type="date" placeholder="End " value="<?= $sieve['journal_date']['end_date'] ?? ''; ?>" class="form-control" name="journal_date[end_date]">
                       </div>


                   </div>

                   <div class="row">
                       <div class=" form-group col-sm-6">
                           <label>* date (From):</label>
                           <input placeholder="Start" type="date" value="<?= $sieve['created_at']['start_date'] ?? ''; ?>" class="form-control" name="created_at[start_date]">
                       </div>


                       <div class=" form-group col-sm-6">
                           <label>* date (To)</label>
                           <input type="date" placeholder="End " value="<?= $sieve['created_at']['end_date'] ?? ''; ?>" class="form-control" name="created_at[end_date]">
                       </div>


                   </div>


                   <div class="form-group">
                       <button type="Submit" class="btn btn-primary">Submit</button>
                       <!-- <a  onclick="$('#filter_form').reset()">Reset</a> -->
                   </div>
               </form>

           </div>
       </div>