<?php
$page_title = "Direct Referrals";
include 'includes/header.php';


$upline = User::where('mlm_id', $user->$user_column)->first();; ?>

<div class="page-wrapper">

  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-6 align-self-center">
        <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Direct Referrals</h3>


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
      <div class="col-6">
        <div class="customize-input">
          <?= $note; ?>
        </div>
      </div>
    </div>
  </div>


  <div class="container-fluid">

    <section id="video-gallery" class="card">

      <div class="card-content">
        <div class="card-body">

          <?php
          $sieve = $list['sieve'];
          include_once 'template/default/composed/filters/team.php'; ?>

          <div class="row">
            <div class="referral col-md-12" align="center">
              <?php
              if ($upline['username'] == '') {
                $upline_link = "#";
              } else {
                $upline_link = "$domain/genealogy/placement_list/$upline[username]/1/$tree_key";
              }; ?>
              <a href="<?= $upline_link; ?>">
                <img src="<?= domain; ?>/<?= $user->profilepic; ?>" style="border-radius: 70%;height: 50px;" data-toggle="tooltip" title="Upline: <?= ucfirst($upline['lastname']); ?> <?= ucfirst($upline['firstname']); ?>">
                <?php if ($user->id == $this->auth()->id) : ?>
                  <h4>Me</h4>
                <?php else : ?>
                  <h4><?= $user->lastname; ?> <?= $user->firstname; ?>
                  </h4>
                <?php endif; ?>
              </a>

              <div class="dropdown">
                <button onclick="copy_text('<?= $auth->referral_link(); ?>');" class="btn btn-sm btn-dark">Copy Link</button>
                <button class="btn btn-dark dropdown-toggle btn-sm" type="button" data-toggle="dropdown" style="display: none;">
                  Downline Level <span class="badge badge-danger"> <?= $level_of_referral; ?> </span>
                  <span class="caret"></span></button>
                <ul class="dropdown-menu" style="max-height: 200px; overflow-y: scroll;">
                  <?php for ($i = 1; $i <= 1; $i++) : ?>
                    <li>
                      <a class="dropdown-item" href="<?= domain; ?>/genealogy/placement_list/<?= $user->username; ?>/<?= $i; ?>/<?= $tree_key; ?>">
                        Level <?= $i; ?>
                      </a>
                    </li>
                  <?php endfor; ?>
                </ul>
              </div>
              <br>
            </div>

          </div>

          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-body">

                  <div class="table-responsive">
                    <table id="myTabl" class="table table-">
                      <thead>
                        <th>Sn</th>
                        <th>Username</th>
                        <th>Joined</th>
                        <!-- <th>Status</th> -->
                      </thead>
                      <tbody>
                        <?php $i = 1;
                        foreach ($list['list'] as $key => $downline) : ?>
                          <tr>
                            <td><?= $i++; ?></td>
                            <td>
                              <a href="<?= domain; ?>/genealogy/placement_list/<?= $downline->username; ?>/1/<?= $tree_key; ?>">
                                <?= $downline->username; ?> </a>
                            </td>
                            <td><?= date("M j, Y h:iA", strtotime($downline->created_at)); ?></td>
                            <!-- <td><?= $downline->activeStatus; ?></td> -->
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>


                </div>
              </div>
            </div>
          </div>


        </div>
      </div>
    </section>

    <ul class="pagination">
      <?= $this->pagination_links($list['total'], $per_page); ?>
    </ul>





  </div>

  <?php include 'includes/footer.php'; ?>