           <div class="dropdown" style="display: inline;">
               <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown">
                   <i class="fa fa-filter"></i>
               </button>

               <div class="dropdown-menu" style="padding: 20px; ">

                   <form action="<?= $action; ?>" method="get" id="filter_form">
                       <div class="row">

                           <div class="form-group ">
                               <label>Paper Name</label><br>
                               <input type="" name="name" placeholder="Name of Paper" class="form-control" value="<?= $sieve['name'] ?? ''; ?>">
                           </div>


                           <div class="form-group">
                               <label>User </label><br>
                               <input type="" name="user" placeholder="First, Last, Name, email, phone, or username" class="form-control" value="<?= $sieve['user'] ?? ''; ?>">
                           </div>


                           <div class="form-group">
                               <label>ID</label><br>
                               <input type="" name="id" placeholder="ID" class="form-control" value="<?= $sieve['id'] ?? ''; ?>">
                           </div>

                       </div>


                       <div class="form-group">
                           <button type="Submit" class="btn btn-primary">Submit</button>
                       </div>
                   </form>

               </div>
           </div>