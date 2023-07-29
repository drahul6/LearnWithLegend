<?php
$page_title = "Monthly Pool";
include 'includes/header.php';

use v2\Models\Wallet\Classes\AccountManager;

$pools_summary = AccountManager::getPoolsSummary();; ?>


<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <?php include 'includes/breadcrumb.php'; ?>

                <h3 class="content-header-title mb-0">Monthly Pool</h3>
            </div>

            <!--  <div class="content-header-right col-md-6 col-12">
            <div class="btn-group float-md-right" role="group" aria-label="Button group with nested dropdown">
              <div class="btn-group" role="group">
                <button class="btn btn-outline-primary dropdown-toggle dropdown-menu-right" id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="ft-settings icon-left"></i> Settings</button>
                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1"><a class="dropdown-item" href="card-bootstrap.html">Bootstrap Cards</a><a class="dropdown-item" href="component-buttons-extended.html">Buttons Extended</a></div>
              </div><a class="btn btn-outline-primary" href="full-calender-basic.html"><i class="ft-mail"></i></a><a class="btn btn-outline-primary" href="timeline-center.html"><i class="ft-pie-chart"></i></a>
            </div>
          </div> -->
        </div>
        <div class="content-body">
            <section id="video-gallery" class="card">
                <div class="card-header">
                    <h4 class="card-title">Monthly Pool</h4>
                    <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                    <div class="heading-elements">
                        <ul class="list-inline mb-0">
                            <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                            <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-body row">

                        <div class="col-lg-4 col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Pool</h4>

                                    <ul class="list-style-none mb-0">
                                        <li>
                                            <span class="text-muted">Total members</span>
                                            <span class="text-dark float-right font-weight-medium"><?= MIS::format_to_thousand_unit($pools_summary['total_persons']); ?></span>
                                        </li>
                                        <li class="mt-3">
                                            <span class="text-muted">This months pools </span>
                                            <span class="text-dark float-right font-weight-medium"><?= $pools_summary['this_month_pools']; ?></span>
                                        </li>
                                        <li class="mt-3">
                                            <span class="text-muted">Value per person</span>
                                            <span class="text-dark float-right font-weight-medium"><?= $pools_summary['value_per_person']; ?></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>


                        <datalist id="usernames">
                        </datalist>
                        <div class="col-lg-6 col-md-12">

                            <form method="post" id="submit_manual_credit" class="card" action="<?= domain; ?>/admin/donate_to_pool">
                                <div class="row">

                                    <div class="form-group col-md-6">
                                        <label>Amount</label>
                                        <input type="number" step="0.01" min="0" name="amount" class="form-control" required="">
                                    </div>


                                    <div class="form-group col-md-6">
                                        <label>Type</label>
                                        <select class="form-control" required="" name="type">
                                            <option value="">Select Type</option>
                                            <option value="credit">Credit</option>
                                            <option value="debit">Debit</option>
                                        </select>
                                    </div>


                                    <div class="form-group col-md-12">
                                        <label>Comment</label>
                                        <textarea id="donation_comment" class="form-control" required="" name="comment" rows="3" name=""></textarea>
                                    </div>


                                    <div class="form-group col-md-12">

                                        <button type="button" class="btn btn-outline-primary" onclick="$confirm_dialog=new DialogJS(submit_form, [], 'Are you sure you want to continue? This action is not reversible.')">Submit</button>
                                    </div>

                                </div>


                            </form>
                        </div>

                        <script>
                            CKEDITOR.replace('donation_comment');


                            submit_form = function() {
                                $('#submit_manual_credit').submit();
                            }

                            populate_option = function($query) {

                                console.log($query.length);

                                if ($query.length < 3) {
                                    return;
                                }

                                $.ajax({
                                    type: "POST",
                                    url: "<?= domain; ?>/admin/search/" + $query,
                                    data: null,
                                    success: function(data) {

                                        $('#usernames').html(data.line);
                                        console.log(data);
                                    },
                                    error: function(data) {},
                                    complete: function() {}
                                });


                            }
                        </script>



                    </div>
                </div>
            </section>


            <!--   <section id="video-gallery" class="card">
        <div class="card-header">
          <h4 class="card-title">blank</h4>
          <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
              <div class="heading-elements">
                <ul class="list-inline mb-0">
                    <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                    <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                </ul>
            </div>
        </div>
      </section> -->


        </div>
    </div>
</div>
<!-- END: Content-->

<?php include 'includes/footer.php'; ?>