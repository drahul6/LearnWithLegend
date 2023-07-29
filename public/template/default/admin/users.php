<?php
$page_title = "$page_title";
include 'includes/header.php'; ?>


<!-- BEGIN: Content-->
<div class="app-content content">
  <div class="content-wrapper">
    <div class="content-header row">
      <div class="content-header-left col-6  mb-2">
        <?php include 'includes/breadcrumb.php'; ?>

        <h3 class="content-header-title mb-0" style="display:inline;"><?= $page_title; ?></h3>
      </div>

      <div class="content-header-right col-6">
        <div class="btn-group float-right" role="group" aria-label="Button group with nested dropdown">
          <small class="float-right"><?= $note; ?></small>
        </div>
      </div>
    </div>
    <div class="content-body">

      <section id="video-gallery" class="card">
        <div class="card-header">

          <?php include_once 'template/default/composed/filters/users.php'; ?>
          <h4 class="card-title" style="display: inline;"></h4>


          <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
          <div class="heading-elements">
            <ul class="list-inline mb-0">
              <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
              <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
            </ul>
          </div>
        </div>
        <div class="card-content">
          <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>#sn</th>
                  <th>#Id</th>
                  <th>Name (Username)</th>
                  <th>Sponsor</th>
                  <th>Joined / Status</th>
                  <th>Action</th>
                </tr>
              </thead>

              <?php $i = 1;
              foreach ($users as $user) : ?>
                <tr>
                  <td><?= $i; ?> </td>
                  <td><?= $user->id; ?> </td>
                  <td>
                    <?= $user->DropSelfLink; ?><br>
                  </td>
                  <td><?= $user->Sponsor->DropSelfLink ?? ''; ?> (<?= $user->Sponsor->username ?? ''; ?>)</td>
                  <td><span class="badge badge-secondary"><?= date('M j, Y h:i:A', strtotime($user->created_at)); ?></span>
                    <br /><?= $user->activeStatus; ?>
                  </td>
                  <td>
                    <div class="dropdown">
                      <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-toggle="dropdown">
                      </button>
                      <div class="dropdown-menu">


                        <a class="dropdown-item" target="_blank" href="<?= $user->AdminViewUrl; ?>">
                          <span type='span' class='label label-xs label-primary'>View</span>
                        </a>

                        <!--  
                              <a class="dropdown-item" target="_blank" href="<?= $user->AdminEditUrl; ?>">
                                <span type='span' class='label label-xs label-primary'>Edit</span>
                              </a>

                              

                              <a class="dropdown-item" target="_blank" href="<?= $user->AdminEditSubscription; ?>">
                                <span type='span' class='label label-xs label-primary'>Edit Subscription</span>
                              </a>
                              -->


                        <?php if (!$user->has_verified_email()) : ?>
                          <a class="dropdown-item" href="javascript:void(0)'">
                            <span type='span' class='label label-xs label-primary'> <?= MIS::generate_form(
                                                                                      ['user_id' => $user->id],
                                                                                      "$domain/user_doc_crud/verify_email",
                                                                                      'Verify Email'

                                                                                    ); ?></span>
                          </a>
                        <?php endif; ?>




                        <a class="dropdown-item" href="javascript:void;" onclick="$confirm_dialog = 
                                  new ConfirmationDialog('<?= domain; ?>/admin/suspending_user/<?= $user->id; ?>')">
                          <span type='span' class='label label-xs label-primary'>Toggle Ban</span>
                        </a>





                      </div>
                    </div>

                  </td>
                </tr>

              <?php $i++;
              endforeach; ?>


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


<div id="new_category_app"></div>

<?php include 'includes/footer.php'; ?>