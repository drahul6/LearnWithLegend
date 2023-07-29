<?php
$page_title = "Support";
include 'includes/header.php';
?>

<div class="page-wrapper">

  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-7 align-self-center">
        <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Support
          <span class="badge badge-primary"><?= $tickets->count(); ?></span>
        </h3>
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
      <div class="col-5 align-self-center">
        <div class="customize-input float-right">
          <a class="btn btn-light btn-sm" href="<?= domain; ?>/user/contact-us">+New Ticket</a>

          <!-- 
                            <select class="custom-select custom-select-set form-control bg-white border-0 custom-shadow custom-radius">
                                <option selected>Aug 19</option>
                                <option value="1">July 19</option>
                                <option value="2">Jun 19</option>
                            </select> -->
        </div>
      </div>
    </div>
  </div>


  <div class="container-fluid">


    <div class="card">
      <div class="card-content collapse show">
        <div class="card-body card-dashboard">
          <table id="payment-histor" class="table table-striped table-bordered zero-configuration">
            <tbody>

              <?php foreach ($tickets as $key => $ticket) : ?>
                <tr>
                  <td style="padding: 0px;">
                    <div class="col-md-12 custom-green" style="padding: 0px;">
                      <div class="alert custom-green   mb-2" role="alert" style="margin:0px !important; ">
                        <a href="<?= $ticket->UserLink; ?>"> <small class="float-left">
                            <?= $ticket->displayStatus; ?>
                            <span class="label badge"><?= date('M j, Y h:iA', strtotime($ticket->created_at)); ?></span>
                          </small></a>
                        <strong class="float-right">

                          <?= $ticket->closeButton; ?>
                        </strong><br>
                        <strong><a href="<?= $ticket->UserLink; ?>">Ticket ID: <?= $ticket->code; ?></a></strong>

                        <br>
                        <small>Subject:
                          <?= $ticket->subject_of_ticket; ?>
                        </small>
                      </div>
                    </div>

                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if ($tickets->count() == 0) : ?>
                <tr class="text-center">
                  <td>Your tickets will appear here</td>
                </tr>
              <?php endif; ?>
            </tbody>

          </table>


        </div>
      </div>
    </div>
    <ul class="pagination">
      <?= $this->pagination_links($data, $per_page); ?>
    </ul>




  </div>

  <?php include 'includes/footer.php'; ?>