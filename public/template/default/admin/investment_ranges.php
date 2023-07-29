<?php
$page_title = "Investment Packages";
include 'includes/header.php';

use v2\Models\InvestmentPackage;; ?>


<!-- BEGIN: Content-->
<div class="app-content content">
  <div class="content-wrapper">
    <div class="content-header row">
      <div class="content-header-left col-6  mb-2">
        <?php include 'includes/breadcrumb.php'; ?>

        <h3 class="content-header-title mb-0">Investments Packages</h3>
      </div>

      <!--   <div class="content-header-right col-6">
        <div class="btn-group float-md-right" role="group" aria-label="Button group with nested dropdown">
          <a class="btn btn-outline-primary" href="<?= domain; ?>">
            + Create
          </a>
        </div>
      </div> -->
    </div>
    <div class="content-body">

      <?php $i = 1;
      foreach (InvestmentPackage::all() as $key => $investment) : ?>



        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header" data-toggle="collapse" data-target="#investment<?= $investment->id; ?>">
                <a href="javascript:void;" class="card-title">
                  <span class="badge badge-secondary">
                    Pack <?= $investment->pack_id; ?>
                  </span>
                  <?= $investment->name; ?></a>
                <div class="heading-elements">
                  <ul class="list-inline mb-0">
                    <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                    <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                  </ul>
                </div>

              </div>
              <div class="card-body row collapse show" id="investment<?= $investment->id; ?>">

                <form class="col-12 ajax_form" method="POST" action="<?= domain; ?>/admin/update_investment_package">

                  <div class="row">


                    <div class="form-group col-md-6">
                      <label>Name </label>
                      <input type="" name="name" required="" value="<?= $investment->name; ?>" class="form-control">
                    </div>

                    <input type="hidden" name="id" value="<?= $investment->id; ?>">

                    <!-- 
                    <div class="form-group col-md-4">
                      <label>Category </label>

                      <select class="form-control" required="" name="category">
                        <option value="">Select Category</option>
                        <?php foreach (InvestmentPackage::$categories as $key => $option) : ?>
                          <option value="<?= $key; ?>" <?= ($option['name'] == $investment->category) ? 'selected' : ''; ?>><?= $option['name']; ?></option>
                        <?php endforeach; ?>
                      </select>                   

                    </div> -->


                    <div class="form-group col-md-3">
                      <label>Capital (<?= $currency; ?>) </label><br>
                      <input type="" name="details[min_capital]" value="<?= @$investment->DetailsArray['min_capital']; ?>" required="" class="form-control">
                    </div>
<!-- 
                    <div class="form-group col-md-3">
                      <label>Max Capital (<?= $currency; ?>) </label><br>
                      <input type="" name="details[max_capital]" value="<?= @$investment->DetailsArray['max_capital']; ?>" required="" class="form-control">
                    </div> -->

                    <div class="form-group col-md-3">
                      <label> ROI (%) </label><br>
                      <input type="" name="details[roi_percent]" value="<?= @$investment->DetailsArray['roi_percent']; ?>" required="" class="form-control">
                    </div>


                    <div class="form-group col-md-3">
                      <label>Maturity (days) </label><br>
                      <input type="" name="details[maturity_in_days]" value="<?= @$investment->DetailsArray['maturity_in_days']; ?>" required="" class="form-control">
                    </div>



                    <div class="form-group col-md-2">
                      <label>Available </label><br>
                      <input type="checkbox" <?= ($investment->is_available()) ? 'checked' : ''; ?> name="availablity">
                    </div>


                  </div>



                  <div class="form-group">
                    <button type="submit" class="btn btn-success">Save</button>
                  </div>

                </form>

              </div>

            </div>
          </div>
        </div>

      <?php $i++;
      endforeach; ?>



    </div>
  </div>
</div>
<!-- END: Content-->

<?php include 'includes/footer.php'; ?>