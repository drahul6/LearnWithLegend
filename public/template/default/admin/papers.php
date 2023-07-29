<?php
$page_title = "Papers";
include 'includes/header.php'; ?>


<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <?php include 'includes/breadcrumb.php'; ?>

                <h3 class="content-header-title mb-0">Papers</h3>
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
                    <?php include_once 'template/default/composed/filters/papers.php'; ?>
                    <h4 class="card-title"></h4>
                    <small class="float-right"><?=$note;?></small>
                </div>
                <div class="card-content table-responsive">
                    <div class="card-body">
                        <table id="" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#Ref</th>
                                    <th>User</th>
                                    <th>Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($papers as $paper) : ?>
                                    <tr>
                                        <td><?= $paper->id; ?></td>
                                        <td>
                                            <?= $paper->editor->DropSelfLink; ?>
                                        </td>
                                        <td><?= $paper->name; ?><br><?= $paper->ApprovedStatus; ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-toggle="dropdown">
                                                </button>
                                                <div class="dropdown-menu">

                                                    <a href="<?= domain; ?>" class="dropdown-item">
                                                        Open Stats
                                                        <!-- appearance.
                                total purchase
                                no of purchase each week of operation
                                those that purchase etc
                                 -->
                                                    </a>
                                                    <!-- <li><a href="#">JavaScript</a></li> -->

                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                            </tbody>
                        </table>





                    </div>
                </div>
            </section>


            <ul class="pagination">
                <?= $this->pagination_links($data, $per_page); ?>
            </ul>


        </div>
    </div>
</div>
<!-- END: Content-->

<?php include 'includes/footer.php'; ?>