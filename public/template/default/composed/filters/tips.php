       <div class="dropdown">
                          <button type="button" class="btn btn-dark btn-sm dropdown-toggle" data-toggle="dropdown">
                             <i class="fa fa-filter"></i>
                          </button>
                          <div class="dropdown-menu" style="padding: 20px; top: 40px !important;">
                            <form action="<?=$action ??'';?>" method="get" id="filter_form">

                                <div class="row">

                                  <div class="form-group col-md-12">
                                      <label>ID</label><br>
                                      <input type="" name="ref" class="form-control" placeholder="Enter ID" value="<?=$sieve['ref'] ?? '';?>">
                                  </div>
                                  


                                  <div class="form-group col-6">
                                      <label>Status</label>
                                      <select class="form-control" name="status">
                                          <option value="">Select</option>
                                          <?php foreach(v2\Models\Wallet::$statuses as $key => $value) :?>
                                              <option value="<?=$key;?>" <?=((isset($sieve['status'])) &&($sieve['status']===$key))?'selected':'';?>> <?=$value;?></option>
                                          <?php endforeach ; ?>
                                      </select>

                                  </div>

                                  <div class="form-group col-md-6">
                                      <label>Upon </label><br>
                                      <input type="" name="upon" placeholder="First, Last, Name, email, phone, or username" 
                                      class="form-control" value="<?=$sieve['upon'] ?? '';?>">
                                  </div>




                                  <div class="form-group col-6">
                                      <label>Type</label>
                                      <select class="form-control" name="type">
                                          <option value="">Select</option>
                                          <?php foreach(v2\Models\Wallet::$types as $key => $value) :?>
                                              <option value="<?=$key;?>" <?=((isset($sieve['type'])) &&($sieve['type']===$key))?'selected':'';?>> <?=$value;?></option>
                                          <?php endforeach ; ?>
                                      </select>

                                  </div>
                                  

                                  <div class="form-group col-md-6">
                                      <label>Comment </label><br>
                                      <input type="" name="comment" placeholder="Comment" 
                                      class="form-control" value="<?=$sieve['comment'] ?? '';?>">
                                  </div>




                                    <div class=" form-group col-6">
                                        <label>*  Date(From):</label>
                                        <input placeholder="Start" type="date" 
                                        value="<?=$sieve['registration']['start_date'] ??'';?>" 
                                        class="form-control" name="registration[start_date]">
                                    </div>


                                    <div class=" form-group col-6">
                                        <label>* Date (To)</label>
                                        <input type="date" placeholder="End "
                                            value="<?=$sieve['registration']['end_date']??'';?>" 
                                         class="form-control" name="registration[end_date]">
                                    </div>



                                      <div class=" form-group col-6">
                                          <label>*  Cleared(From):</label>
                                          <input placeholder="Start" type="date" 
                                          value="<?=$sieve['cleared']['start_date']??'';?>" 
                                          class="form-control" name="cleared[start_date]">
                                      </div>


                                      <div class=" form-group col-6">
                                          <label>* Cleared (To)</label>
                                          <input type="date" placeholder="End "
                                              value="<?=$sieve['cleared']['end_date']??'';?>" 
                                           class="form-control" name="cleared[end_date]">
                                      </div>

                                </div>


                                <div class="form-group">
                                    <button type="Submit" class="btn">Submit</button>
                                    <!-- <a  onclick="$('#filter_form').reset()">Reset</a> -->
                                </div>
                            </form>

                          </div>
                        </div>
                        