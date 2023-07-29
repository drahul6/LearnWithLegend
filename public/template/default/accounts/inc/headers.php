<!DOCTYPE html>
<html ng-app='app' lang="en" ng-cloak>

<head>

  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">


  <meta name="description" content="<?= @$page_description; ?>">
  <meta name="keywords" content="<?= @$page_keywords; ?>">
  <meta name="author" content="<?= $page_author; ?>">
  <title><?= @$page_title; ?> | <?= project_name; ?></title>
  <link rel="apple-touch-icon" href="<?= $logo; ?>">
  <link rel="shortcut icon" type="image/x-icon" href="<?= $logo; ?>">

  <!-- vendor css -->
  <link href="<?= asset; ?>/guest/lib/%40fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="<?= asset; ?>/guest/lib/ionicons/css/ionicons.min.css" rel="stylesheet">

  <!-- DashForge CSS -->
  <link rel="stylesheet" href="<?= asset; ?>/guest/assets/css/dashforge.css">
  <link rel="stylesheet" href="<?= asset; ?>/guest/assets/css/dashforge.dashboard.css">
</head>

<!-- angularjs -->
<script src="<?= asset; ?>/angulars/angularjs.js"></script>
<script src="<?= asset; ?>/angulars/angular-sanitize.js"></script>
<script>
  let $base_url = "<?= domain; ?>";
  var app = angular.module('app', ['ngSanitize']);
</script>
<script src="<?= asset; ?>/guest/lib/jquery/jquery.min.js"></script>

<body class="page-profile">

  <header class="navbar navbar-header navbar-header-fixed" id="accounts_headers">
    <a href="#" id="mainMenuOpen" class="burger-menu"><i data-feather="menu"></i></a>
    <div class="navbar-brand">
      <a href="<?= domain; ?>" class="df-logo"><?= project_name; ?><span></span></a>
    </div><!-- navbar-brand -->
    <div id="navbarMenu" class="navbar-menu-wrapper">
      <div class="navbar-menu-header">
        <a href="<?= domain; ?>" class="df-logo"><?= project_name; ?><span></span></a>
        <a id="mainMenuClose" href="#"><i data-feather="x"></i></a>
      </div><!-- navbar-menu-header -->
      <ul class="nav navbar-menu">
        <li class="nav-label pd-l-20 pd-lg-l-25 d-lg-none">Main Navigation</li>

        <!--         <li class="nav-item active"><a href="<?= domain; ?>" class="nav-link"><i data-feather="box"></i> Home </a></li>
-->
        <li class="nav-item"><a class="nav-link" href="<?= domain; ?>/accounts/dashboard">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= domain; ?>/accounts/customise-charts-of-accounts-category">Customise Account</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= domain; ?>/accounts/chart-of-accounts">Chart of Accounts</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= domain; ?>/journals/lists">Manual Journals</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= domain; ?>/trial-balance/subcat">Trial Balance</a></li>
        <!--           <li  class="nav-item"><a class="nav-link" href="<?= domain; ?>/accounts/books_settings">Books Settings</a></li>
 -->
      </ul>
    </div><!-- navbar-menu-wrapper -->

    <div class="navbar-right">
      <?php if ($this->admin()) : ?>
        <div class="dropdown dropdown-profile show">
          <a href="#" class="dropdown-link" data-toggle="dropdown" data-display="static" aria-expanded="">
            <?= $admin->fullname; ?> <div class="avatar avatar-sm"><img src="<?= domain; ?>/<?= $admin->profilepic; ?>" class="rounded-circle" alt=""></div>
          </a><!-- dropdown-link -->
          <!--  <div class="dropdown-menu dropdown-menu-right tx-13 show">
            <h6 class="tx-semibold mg-b-5"><?= $admin->fullname; ?></h6>
            <p class="mg-b-25 tx-12 tx-color-03">Administrator</p>
                       <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item"> Forum</a>
            <a href="#" class="dropdown-item"> Forum</a>
            <a href="#" class="dropdown-item"> Forum</a>
          
          </div> -->
          <!-- dropdown-menu -->
        </div>
      <?php else : ?>
      <?php endif; ?>
    </div>

    <div class="navbar-right" style="display: none;">



      <a id="navbarSearch" href="#" class="search-link"><i data-feather="search"></i></a>
      <div class="dropdown dropdown-message">
        <a href="#" class="dropdown-link new-indicator" data-toggle="dropdown">
          <i data-feather="message-square"></i>
          <span>5</span>
        </a>
        <div class="dropdown-menu dropdown-menu-right">
          <div class="dropdown-header">New Messages</div>
          <a href="#" class="dropdown-item">
            <div class="media">
              <div class="avatar avatar-sm avatar-online"><img src="<?= asset; ?>/guest/assets/img/img6.jpg" class="rounded-circle" alt=""></div>
              <div class="media-body mg-l-15">
                <strong>Socrates Itumay</strong>
                <p>nam libero tempore cum so...</p>
                <span>Mar 15 12:32pm</span>
              </div><!-- media-body -->
            </div><!-- media -->
          </a>
          <a href="#" class="dropdown-item">
            <div class="media">
              <div class="avatar avatar-sm avatar-online"><img src="<?= asset; ?>/guest/assets/img/img8.jpg" class="rounded-circle" alt=""></div>
              <div class="media-body mg-l-15">
                <strong>Joyce Chua</strong>
                <p>on the other hand we denounce...</p>
                <span>Mar 13 04:16am</span>
              </div><!-- media-body -->
            </div><!-- media -->
          </a>
          <a href="#" class="dropdown-item">
            <div class="media">
              <div class="avatar avatar-sm avatar-online"><img src="<?= asset; ?>/guest/assets/img/img7.jpg" class="rounded-circle" alt=""></div>
              <div class="media-body mg-l-15">
                <strong>Althea Cabardo</strong>
                <p>is there anyone who loves...</p>
                <span>Mar 13 02:56am</span>
              </div><!-- media-body -->
            </div><!-- media -->
          </a>
          <a href="#" class="dropdown-item">
            <div class="media">
              <div class="avatar avatar-sm avatar-online"><img src="<?= asset; ?>/guest/assets/img/img9.jpg" class="rounded-circle" alt=""></div>
              <div class="media-body mg-l-15">
                <strong>Adrian Monino</strong>
                <p>duis aute irure dolor in repre...</p>
                <span>Mar 12 10:40pm</span>
              </div><!-- media-body -->
            </div><!-- media -->
          </a>
          <div class="dropdown-footer"><a href="#">View all Messages</a></div>
        </div><!-- dropdown-menu -->
      </div><!-- dropdown -->
      <div class="dropdown dropdown-notification">
        <a href="#" class="dropdown-link new-indicator" data-toggle="dropdown">
          <i data-feather="bell"></i>
          <span>2</span>
        </a>
        <div class="dropdown-menu dropdown-menu-right">
          <div class="dropdown-header">Notifications</div>
          <a href="#" class="dropdown-item">
            <div class="media">
              <div class="avatar avatar-sm avatar-online"><img src="<?= asset; ?>/guest/assets/img/img6.jpg" class="rounded-circle" alt=""></div>
              <div class="media-body mg-l-15">
                <p>Congratulate <strong>Socrates Itumay</strong> for work anniversaries</p>
                <span>Mar 15 12:32pm</span>
              </div><!-- media-body -->
            </div><!-- media -->
          </a>
          <a href="#" class="dropdown-item">
            <div class="media">
              <div class="avatar avatar-sm avatar-online"><img src="<?= asset; ?>/guest/assets/img/img8.jpg" class="rounded-circle" alt=""></div>
              <div class="media-body mg-l-15">
                <p><strong>Joyce Chua</strong> just created a new blog post</p>
                <span>Mar 13 04:16am</span>
              </div><!-- media-body -->
            </div><!-- media -->
          </a>
          <a href="#" class="dropdown-item">
            <div class="media">
              <div class="avatar avatar-sm avatar-online"><img src="<?= asset; ?>/guest/assets/img/img7.jpg" class="rounded-circle" alt=""></div>
              <div class="media-body mg-l-15">
                <p><strong>Althea Cabardo</strong> just created a new blog post</p>
                <span>Mar 13 02:56am</span>
              </div><!-- media-body -->
            </div><!-- media -->
          </a>
          <a href="#" class="dropdown-item">
            <div class="media">
              <div class="avatar avatar-sm avatar-online"><img src="<?= asset; ?>/guest/assets/img/img9.jpg" class="rounded-circle" alt=""></div>
              <div class="media-body mg-l-15">
                <p><strong>Adrian Monino</strong> added new comment on your photo</p>
                <span>Mar 12 10:40pm</span>
              </div><!-- media-body -->
            </div><!-- media -->
          </a>
          <div class="dropdown-footer"><a href="#">View all Notifications</a></div>
        </div><!-- dropdown-menu -->
      </div><!-- dropdown -->
      <div class="dropdown dropdown-profile">
        <a href="#" class="dropdown-link" data-toggle="dropdown" data-display="static">
          <div class="avatar avatar-sm"><img src="<?= asset; ?>/guest/assets/img/img1.png" class="rounded-circle" alt=""></div>
        </a><!-- dropdown-link -->
        <div class="dropdown-menu dropdown-menu-right tx-13">
          <div class="avatar avatar-lg mg-b-15"><img src="<?= asset; ?>/guest/assets/img/img1.png" class="rounded-circle" alt=""></div>
          <h6 class="tx-semibold mg-b-5">Katherine Pechon</h6>
          <p class="mg-b-25 tx-12 tx-color-03">Administrator</p>

          <a href="#" class="dropdown-item"><i data-feather="edit-3"></i> Edit Profile</a>
          <a href="page-profile-view.html" class="dropdown-item"><i data-feather="user"></i> View Profile</a>
          <div class="dropdown-divider"></div>
          <a href="page-help-center.html" class="dropdown-item"><i data-feather="help-circle"></i> Help Center</a>
          <a href="#" class="dropdown-item"><i data-feather="life-buoy"></i> Forum</a>
          <a href="#" class="dropdown-item"><i data-feather="settings"></i>Account Settings</a>
          <a href="#" class="dropdown-item"><i data-feather="settings"></i>Privacy Settings</a>
          <a href="page-signin.html" class="dropdown-item"><i data-feather="log-out"></i>Sign Out</a>
        </div><!-- dropdown-menu -->
      </div><!-- dropdown -->
    </div><!-- navbar-right -->
    <div class="navbar-search">
      <div class="navbar-search-header">
        <input type="search" class="form-control" placeholder="Type and hit enter to search...">
        <button class="btn"><i data-feather="search"></i></button>
        <a id="navbarSearchClose" href="#" class="link-03 mg-l-5 mg-lg-l-10"><i data-feather="x"></i></a>
      </div><!-- navbar-search-header -->
      <div class="navbar-search-body">
        <label class="tx-10 tx-medium tx-uppercase tx-spacing-1 tx-color-03 mg-b-10 d-flex align-items-center">Recent Searches</label>
        <ul class="list-unstyled">
          <li><a href="dashboard-one.html">modern dashboard</a></li>
          <li><a href="app-calendar.html">calendar app</a></li>
          <li><a href="<?= asset; ?>/guest/collections/modal.html">modal examples</a></li>
          <li><a href="<?= asset; ?>/guest/components/el-avatar.html">avatar</a></li>
        </ul>

        <hr class="mg-y-30 bd-0">

        <label class="tx-10 tx-medium tx-uppercase tx-spacing-1 tx-color-03 mg-b-10 d-flex align-items-center">Search Suggestions</label>

        <ul class="list-unstyled">
          <li><a href="dashboard-one.html">cryptocurrency</a></li>
          <li><a href="app-calendar.html">button groups</a></li>
          <li><a href="<?= asset; ?>/guest/collections/modal.html">form elements</a></li>
          <li><a href="<?= asset; ?>/guest/components/el-avatar.html">contact app</a></li>
        </ul>
      </div><!-- navbar-search-body -->
    </div><!-- navbar-search -->
  </header>


  <style type="text/css">
    .select-currency {
      border: none;
      height: 30px;
    }
  </style>

  <div class="content content-fixed content-auth">
    <div class="container">