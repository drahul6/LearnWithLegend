  <section id="" class="card">

      <div class="card-content">
          <div class="card-body">

              <?php

                use Filters\Filters\UserFilter;

                $tree = User::$tree[$tree_key];
                $user_column = $tree['column'];

                $auth = $this->auth();
                $user = User::where('username', $username)->first();


                $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
                $per_page = 50;


                $query = $user->all_downlines_by_path($tree_key, false, $level_of_referral);

                $sieve = $_REQUEST;
                $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
                $skip = (($page - 1) * $per_page);

                $filter = new  UserFilter($sieve);
                $total = $query->count();
                $data = $query->Filter($filter)->count();

                $list = $query->Filter($filter)
                    ->offset($skip)
                    ->take($per_page)
                    ->get();  //filtered

                $note = MIS::filter_note(count($list), $data, $total,  $sieve,  1);


                $upline = User::where('mlm_id', $user->$user_column)->first();
                include_once 'template/default/composed/filters/team.php'; ?>

              <div class="row">
                  <div class="referral col-md-12" align="center">
                      <?php
                        if (@$upline['username'] == '') {
                            $upline_link = "#";
                        } else {
                            $upline_link = "$domain/genealogy/direct_list/$upline[username]/1/$tree_key";
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
                          <!-- <button onclick="copy_text('<?= $auth->referral_link(); ?>');" class="btn btn-sm btn-dark">Copy Link</button> -->
                          <button class="btn btn-dark dropdown-toggle btn-sm" type="button" data-toggle="dropdown">
                              Downline Level <span class="badge badge-danger"> <?= $level_of_referral; ?> </span>
                              <span class="caret"></span></button>
                          <ul class="dropdown-menu" style="max-height: 200px; overflow-y: scroll;">
                              <li>
                                  <a class="dropdown-item" href="<?= domain; ?>/genealogy/direct_list/<?= $user->username; ?>/all/<?= $tree_key; ?>">
                                      All Levels
                                  </a>
                              </li>
                              <?php for ($i = 1; $i <= 16; $i++) : ?>
                                  <li>
                                      <a class="dropdown-item" href="<?= domain; ?>/genealogy/direct_list/<?= $user->username; ?>/<?= $i; ?>/<?= $tree_key; ?>">
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
                                          <th>Level</th>
                                          <th>Username</th>
                                          <th>Joined</th>
                                          <!-- <th>Status</th> -->
                                      </thead>
                                      <tbody>
                                          <?php $i = 1;
                                            foreach ($list as $key => $downline) : ?>
                                              <tr>
                                                  <td><?= $i++; ?></td>
                                                  <td><?= $user->downline_level_of($downline, $tree_key) ?></td>
                                                  <td>
                                                      <a href="<?= domain; ?>/genealogy/direct_list/<?= $downline->username; ?>/1/<?= $tree_key; ?>">
                                                          <?= $downline->username; ?> </a>
                                                  </td>
                                                  <td>
                                                      <span class="badge"><?= date("M j, Y h:iA", strtotime($downline->created_at)); ?></span>
                                                  </td>
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
      <?= $this->pagination_links($total, $per_page); ?>
  </ul>