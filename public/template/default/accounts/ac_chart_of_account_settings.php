<?php include 'inc/headers.php'; ?>

<div class="row">
  <div class="col-md-12">
    <h3><?= $chart_of_account->account_name; ?></h3>
    <small><?= $chart_of_account->owner->username ?? ''; ?></small>
    <small><?= $chart_of_account->OpenOrCloseStatus; ?></small>
  </div>
  <hr>

  <div class="col-md-4">
    <div class="card card-body">
      <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold mg-b-8">Opening Balance</h6>
      <div class="d-flex d-lg-block d-xl-flex align-items-end">
        <h3 class="tx-normal tx-rubik mg-b-0 mg-r-5 lh-1"><?= MIS::money_format(($chart_of_account->opening_balance)); ?></h3>
        <p class="tx-11 tx-color-03 mg-b-0"><span class="tx-medium tx-success"><?= $chart_of_account::$base_currency; ?> </p>
      </div>
      <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold mg-t-5">
        Account currency: <?= MIS::money_format(($chart_of_account->AccountOpeningBalance)); ?> <?= $chart_of_account->currency; ?></h6>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card card-body">
      <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold mg-b-8">Current Balance</h6>
      <div class="d-flex d-lg-block d-xl-flex align-items-end">
        <h3 class="tx-normal tx-rubik mg-b-0 mg-r-5 lh-1"><?= MIS::money_format(($chart_of_account->current_balance)); ?></h3>
        <p class="tx-11 tx-color-03 mg-b-0"><span class="tx-medium tx-success"><?= $chart_of_account::$base_currency; ?> </p>
      </div>
      <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold mg-t-5">
        Account currency: <?= MIS::money_format(($chart_of_account->AccountCurrentBalance)); ?> <?= $chart_of_account->currency; ?></h6>
    </div>
  </div>


  <div class="col-md-4">
    <div class="card card-body">
      <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold mg-b-8">Available Balance</h6>
      <div class="d-flex d-lg-block d-xl-flex align-items-end">
        <h3 class="tx-normal tx-rubik mg-b-0 mg-r-5 lh-1">
          <?= MIS::money_format(($chart_of_account->getAvailableBalance()['available_balance'])); ?>
        </h3>
        <p class="tx-11 tx-color-03 mg-b-0"><span class="tx-medium tx-success"><?= $chart_of_account::$base_currency; ?> </p>
      </div>
      <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold mg-t-5">
        Account currency: <?= MIS::money_format(($chart_of_account->getAvailableBalance()['conversion'])); ?> <?= $chart_of_account->currency; ?></h6>
    </div>
  </div>




  <div class="col-md-12">
    <div class="card ">
      <div class="card-header">Settings</div>
      <div class="card-body">
        <form id="create_chart_of_account_category_form" action="<?= domain; ?>/accounts/update_chart_of_account" method="post">
          <div class="form-group">
            <label>Account Type *</label>
            <select name="account_type" class="form-control">
              <?php foreach ($options as $base_category => $sub_categories) : ?>
                <optgroup label="<?= $base_category; ?>">
                  <?php foreach ($sub_categories as $sub_category) : ?>

                    <option value="<?= $sub_category['id']; ?>">
                      <?= $sub_category['name']; ?>
                    </option>

                  <?php endforeach; ?>
                </optgroup>
              <?php endforeach; ?>
            </select>


          </div>
          <input type="hidden" name="id" value="<?= $chart_of_account->id; ?>">
          <div class="form-group">
            <label>Account Name*</label>
            <input type="" name="name" value="<?= $chart_of_account->account_name; ?>" required="" class="form-control">
          </div>

          <div class="form-group">
            <label>Opening Balance*</label>
            <input type="" name="opening_balance" value="<?= $chart_of_account->opening_balance; ?>" required="" class="form-control">
          </div>

          <div class="form-group">
            <label>Currency *</label>
            <input type="" name="currency" value="<?= $chart_of_account->currency; ?>" required="" class="form-control">
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-danger">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>





</div>
<?php include 'inc/footers.php'; ?>