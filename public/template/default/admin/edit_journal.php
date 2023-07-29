<?php
$page_title = "Edit Journal";
include 'includes/header.php'; ?>

<style>
    #accounts_headers {
        display: none;
    }
</style>


<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <?php include 'includes/breadcrumb.php'; ?>

                <!-- <h3 class="content-header-title mb-0">Edit Journal</h3> -->
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
                <div class="card-content">
                    <div class="card-body">

                        <?php $this->view('accounts/ac_edit_journal', compact('journal')); ?>



                    </div>
                </div>
            </section>



        </div>
    </div>
</div>
<!-- END: Content-->

<?php include 'includes/footer.php'; ?>