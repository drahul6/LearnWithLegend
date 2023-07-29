<?php

use v2\Models\Wallet\AcBooksSettings;
use v2\Models\Wallet\BasicAccountType;
use v2\Models\Wallet\CompanyAccountType;
use v2\Models\Wallet\AcDashboardSettings;

include 'inc/headers.php'; ?>




<div class="panel-group col-md-12" style="margin-top: 10px;">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" href="#collapse290">Switch Business</a>
        <small>From <b><?= $this->admin()->company->name; ?></b></small>
      </h4>
    </div>
    <div id="collapse290" class="panel-collapse collapse in">
      <div class="panel-body">
        <div class="col-md-12">

          <form id="" action="<?= domain; ?>/accounts/switch_business" method="post">
            <div class="form-group">
              <label>Select Business *</label>

              <select class="form-control" name="company_id" required="">
                <option value="">Select Business</option>
                <?php foreach ($this->admin()->businesses as  $business) : ?>
                  <option value="<?= $business->id; ?>"><?= $business->name; ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="modal-footer">
              <button type="submit" class="btn btn-danger">Switch</button>
            </div>
          </form>
        </div>

      </div>
      <!-- <div class="panel-footer">Panel Footer</div> -->
    </div>
  </div>
</div>




<div class="panel-group col-md-12" style="margin-top: 10px;">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" href="#collapse21">Financial Periods</a>
      </h4>
    </div>
    <div id="collapse21" class="panel-collapse collapse in">
      <div class="panel-body">
        <div class="col-md-12">



          <table id="charts_of_accounts_table" class="table table-hover">
            <thead>
              <tr>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>*</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (AcBooksSettings::company($this->admin()->company_id)->get() as $period) : ?>

                <tr>
                  <td><?= $period->start_date; ?></td>
                  <td><b><?= $period->end_date; ?></b>
                  <td> <?= $period->ActivationStatus; ?></td>
                  <td>

                    <a onclick="$confirm_dialog = 
                                                      new ConfirmationDialog('<?= domain; ?>/accounts/activate_financial_period/<?= $period->id; ?>')" class="btn btn-primary btn-xs"> Activate</a>

                    <!--   <a onclick="$confirm_dialog = 
                                                      new ConfirmationDialog('<?//=domain;?>/accounts/delete_financial_period/<?= $period->id; ?>')" 
                                                      class="btn btn-danger btn-xs"><i class="fa fa-trash"></i> Delete</a> -->
                  </td>
                </tr>




              <?php endforeach; ?>

            </tbody>
          </table>



        </div>

      </div>
      <!-- <div class="panel-footer">Panel Footer</div> -->
    </div>
  </div>
</div>


<div class="panel-group col-md-12" style="margin-top: 10px;">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" href="#collapse22">Financial Period</a>
      </h4>
    </div>
    <div id="collapse22" class="panel-collapse collapse in">
      <div class="panel-body">
        <div class="col-md-12">

          <form id="" action="<?= domain; ?>/accounts/create_financial_period" method="post">
            <div class="form-group">
              <label>Starts *</label>
              <input required="" name="starts" type="date" class="form-control">
            </div>


            <div class="form-group">
              <label>Ends *</label>
              <input required="" name="ends" type="date" class="form-control">
            </div>
        </div>



        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Save</button>
        </div>
        </form>
      </div>

    </div>
    <!-- <div class="panel-footer">Panel Footer</div> -->
  </div>
</div>
</div>


<div class="panel-group col-md-12" style="margin-top: 10px;">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" href="#collapse1">Close For Financial Period</a>
      </h4>
    </div>
    <div id="collapse1" class="panel-collapse collapse in">
      <div class="panel-body">
        <?php
        $activated_period = AcBooksSettings::activated_period();; ?>


        <div class="col-md-12">
          <span>

            <?= date("M j, Y", strtotime($activated_period->start_date)); ?>
            -

            <?= date("M j, Y", strtotime($activated_period->end_date)); ?>

          </span>
          <br>
          <small class="text-danger">*This will close accounts for the current activated financial period
          </small>

          <a onclick="$confirm_dialog = 
                                    new ConfirmationDialog('<?= domain; ?>/accounts/close_for_financial_period/')">
            <div class="modal-footer">
              <button type="submit" class="btn btn-danger pull-left">Close</button>
            </div>
          </a>
        </div>

      </div>
      <!-- <div class="panel-footer">Panel Footer</div> -->
    </div>
  </div>
</div>



<div class="panel-group col-md-12" style="margin-top: 10px;">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" href="#collapse32">Open For Financial Period</a>
      </h4>
    </div>
    <div id="collapse32" class="panel-collapse collapse in">
      <div class="panel-body">


        <div class="col-md-12">
          <span>

            <?= date("M j, Y", strtotime($activated_period->start_date)); ?>
            -

            <?= date("M j, Y", strtotime($activated_period->end_date)); ?>

          </span>

          <br>
          <small class="text-danger">*This will close accounts for the current activated financial period
          </small>

          <a onclick="$confirm_dialog = 
                                    new ConfirmationDialog('<?= domain; ?>/accounts/open_for_financial_period/')">
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary pull-left">Open</button>
            </div>
          </a>

        </div>

      </div>
      <!-- <div class="panel-footer">Panel Footer</div> -->
    </div>
  </div>
</div>


<div class="panel-group col-md-12" style="margin-top: 10px;">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" href="#collapse34">Dashboard Metrics</a>
      </h4>
    </div>
    <div id="collapse34" class="panel-collapse collapse in">
      <div class="panel-body">


        <div class="col-md-12">
          <br>
          <small class="text-danger">*This will close accounts for the current activated financial period
          </small>

          <form action="<?= domain; ?>/accounts/create_dashbaord_metric" method="post">
            <div class="form-group">
              <label>Enter Label</label>
              <input type="" name="label" class="form-control">
            </div>
            <label>Select Accounts</label>
            <select multiple="" name="accounts_ids[]" class="form-control">
              <?php foreach ($output as $basic_account_id => $subcategories) :
                $base_account = BasicAccountType::find($basic_account_id);
              ?>
                <optgroup label="<?= $base_account->name; ?>">

                  <?php foreach ($subcategories as $subcategory_id => $accounts) :
                    $company_type = CompanyAccountType::find($subcategory_id);
                  ?>
                <optgroup style="margin-left: 30px" label="   <?= $company_type->name; ?>">

                  <?php foreach ($accounts as $account_id => $account) : ?>
                    <option value="<?= $account['id']; ?>">
                      <?= $account['account_name']; ?>
                    </option>

                  <?php endforeach; ?>

                </optgroup>
              <?php endforeach; ?>
              </optgroup>
            <?php endforeach; ?>
            </select>

            <div class="modal-footer">
              <button type="submit" class="btn btn-primary pull-left">Save</button>
            </div>
          </form>


          <table id="charts_of_accounts_table" class="table table-hover">
            <thead>
              <tr>
                <th></th>
                <th>Label</th>
                <th>Account(s)</th>
                <th>Status</th>
                <th>*</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1;
              foreach (AcDashboardSettings::for_company($this->admin()->company_id)->get() as $metric) : ?>

                <tr>
                  <td><?= $i++; ?></td>
                  <td><?= $metric->Label; ?></td>
                  <td>
                    <div class="dropdown">
                      <button class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown">Accounts
                        <span class="badge badge-default">
                          <?= $metric->Accounts->count(); ?>
                        </span>
                        <span class="caret"></span></button>
                      <ul class="dropdown-menu">
                        <?php foreach ($metric->Accounts as $account) : ?>
                          <li><a><?= $account->account_name; ?></a></li>
                        <?php endforeach; ?>
                      </ul>
                    </div>
                  <td> <?= $metric->State; ?></td>
                  <td>

                    <a onclick="$confirm_dialog = 
                                                      new ConfirmationDialog('<?= domain; ?>/accounts/delete_metric/<?= $metric->id; ?>')" class="btn btn-danger btn-xs"> Delete</a>
                  </td>
                </tr>




              <?php endforeach; ?>

            </tbody>
          </table>




        </div>

      </div>
      <!-- <div class="panel-footer">Panel Footer</div> -->
    </div>
  </div>
</div>

<?php include 'inc/footers.php'; ?>