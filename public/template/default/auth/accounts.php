<?php
$page_title = "Change Password";
include 'includes/header.php';
?>

<div class="page-wrapper">

  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-7 align-self-center">
        <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Change Password</h3>
        <!-- 
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="">Blank</a>
                                    </li>
                                </ol>
                            </nav>
                        </div> -->
      </div>
      <!-- <div class="col-5 align-self-center">
                        <div class="customize-input float-right">
                            <select class="custom-select custom-select-set form-control bg-white border-0 custom-shadow custom-radius">
                                <option selected>Aug 19</option>
                                <option value="1">July 19</option>
                                <option value="2">Jun 19</option>
                            </select>
                        </div>
                    </div> -->
    </div>
  </div>


  <div class="container-fluid">


    <section id="video-gallery" class="card">

      <div class="card-content">
        <div class="card-body">



          <form method="post" action="<?= domain; ?>/user-profile/change_password" class="ajax_form" style="padding: 10px;">
            <?= @$this->csrf_field('change_password'); ?>
            <div class="form-group">
              <input required type="password" name="current_password" class="form-control" placeholder="Current Password">
              <span class="text-danger"><?= @$this->inputError('current_password'); ?></span>
            </div>

            <div class="form-group">
              <input required type="password" name="new_password" class="form-control" placeholder="New Password">
              <span class="text-danger"><?= @$this->inputError('new_password'); ?></span>
            </div>

            <div class="form-group">
              <input required type="password" name="confirm_password" class="form-control" placeholder="Confirm password">
              <span class="text-danger"><?= @$this->inputError('confirm_password'); ?></span>
            </div>

            <div class="row">
              <div class="col-sm-6">
                <button type="submit" class="btn btn-outline-dark  btn-block btn-flat">Submit</button>
              </div>
              <!-- /.col -->
            </div>
          </form>




        </div>
      </div>
    </section>


  </div>

  <?php include 'includes/footer.php'; ?>