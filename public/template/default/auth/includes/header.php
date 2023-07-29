<!DOCTYPE html>
<html ng-app="app" class="loading" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= @$page_description; ?>">
    <meta name="keywords" content="<?= @$page_keywords; ?>">
    <meta name="author" content="<?= $page_author; ?>">
    <title><?= @$page_title; ?> | <?= project_name; ?></title>




    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-9484468339621166" crossorigin="anonymous"></script>
    <!-- Custom CSS -->
    <link href="<?= $this_folder; ?>/../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <!-- <link href="<?= $this_folder; ?>/../assets/libs/chartist/dist/chartist.min.css" rel="stylesheet"> -->
    <link href="<?= $this_folder; ?>/../assets/extra-libs/jvector/jquery-jvectormap-2.0.2.css" rel="stylesheet" />
    <!-- Custom CSS -->
    <link href="<?= $this_folder; ?>/../assets/dist/css/style.min.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>

    <![endif]-->
    <script src="<?= $this_folder; ?>/../assets/libs/jquery/dist/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="<?= $asset; ?>/css/binary-tree.css">

    <script src="https://cdn.ckeditor.com/4.11.4/standard/ckeditor.js"></script>

</head>

<script src="<?= asset; ?>/angulars/angularjs.js"></script>
<script src="<?= asset; ?>/angulars/angular-sanitize.js"></script>

<script>
    let $base_url = "<?= domain; ?>";
    var app = angular.module('app', ['ngSanitize']);
</script>


<style>
    .dropdown-menu {
        max-height: 500px;
        overflow-y: scroll;
    }
</style>

<body>
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <!--     <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div> -->
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar" data-navbarbg="skin6">
            <nav class="navbar top-navbar navbar-expand-md">
                <div class="navbar-header" data-logobg="skin6">
                    <!-- This is for the sidebar toggle which is visible on mobile only -->
                    <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
                    <!-- ============================================================== -->
                    <!-- Logo -->
                    <!-- ============================================================== -->
                    <div class="navbar-brand">
                        <!-- Logo icon -->
                        <a href="<?= domain; ?>">
                            <b class="logo-icon">
                                <!--                                 <img  style="height: 50px;" src="<?= $logo; ?>" alt="homepage" class="dark-logo" />
                                <img  style="height: 50px;" src="<?= $logo; ?>" class="light-logo" alt="homepage" />
 -->
                            </b>
                            <!--End Logo icon -->
                            <!-- Logo text -->
                            <span class="logo-text">
                                <!-- dark Logo text -->
                                <img style="height: 60px;" src="<?= $logo; ?>" alt="homepage" class="dark-logo" />
                                <!-- Light Logo text -->
                                <img style="height: 60px;" src="<?= $logo; ?>" class="light-logo" alt="homepage" />
                            </span>
                        </a>
                    </div>
                    <!-- ============================================================== -->
                    <!-- End Logo -->
                    <!-- ============================================================== -->
                    <!-- ============================================================== -->
                    <!-- Toggle which is visible on mobile only -->
                    <!-- ============================================================== -->
                    <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="ti-more"></i></a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse collapse" id="navbarSupportedContent">
                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav float-left mr-auto ml-3 pl-1">
                        <!-- Notification -->
                        <li class="nav-item dropdown" style="display:none;">
                            <a class="nav-link  pl-md-3 position-relative" href="<?= domain; ?>/user/notifications" id="bell" aria-haspopup="true" aria-expanded="false">
                                <span><i data-feather="bell" class="svg-icon"></i></span>
                                <span class="badge badge-primary notify-no rounded-circle">
                                    <?php
                                    $unseen_notifications = Notifications::unseen_notifications($auth->id);
                                    echo $unseen_notifications->count(); ?>

                                </span>
                            </a>
                        </li>
                        <!-- End Notification -->

                        <li class="nav-item dropdown">
                            <a class="nav-lin custom-select form-control bg-white custom-radius custom-shadow border-0" href="https://unifyinglegends.com/legendsprojects">
                                Join our group <img src="<?= $logo; ?>/../legend.webp" style="border-radius: 14px;height: 27px;">
                            </a>

                        </li>
                    </ul>


                    <!-- ============================================================== -->
                    <!-- Right side toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav float-right">


                        <?php include_once 'template/default/composed/auth_dropdown.php';; ?>



                        <!-- ============================================================== -->
                        <!-- User profile and search -->
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="javascript:void(0)" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="<?= domain; ?>/<?= $auth->profilepic; ?>" alt="user" class="rounded-circle" width="40">
                                <span class="ml-2 d-none d-lg-inline-block"><span>Hi,</span> <span class="text-dark"><?= $auth->fullname; ?></span> <i data-feather="chevron-down" class="svg-icon"></i></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right user-dd animated flipInY">
                                <a class="dropdown-item">
                                    <?= $auth->activeStatus; ?>
                                    <!-- <span class="badge badge-secondary"><?= $auth->TheRank['name']; ?></span> -->
                                </a>

                                <a class="dropdown-item" href="<?= domain; ?>/user/profile"><i data-feather="user" class="svg-icon mr-2 ml-1"></i>
                                    My Profile</a>

                                <a class="dropdown-item" href="<?= domain; ?>/user/change-password"><i data-feather="lock" class="svg-icon mr-2 ml-1"></i>
                                    Change Password</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="<?= domain; ?>/login/logout"><i data-feather="power" class="svg-icon mr-2 ml-1"></i>
                                    Logout</a>

                            </div>
                        </li>
                        <!-- ============================================================== -->
                        <!-- User profile and search -->
                        <!-- ============================================================== -->
                    </ul>
                </div>
            </nav>
        </header>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->


        <aside class="left-sidebar" data-sidebarbg="skin6">
            <!-- Sidebar scroll-->
            <div class="scroll-sidebar" data-sidebarbg="skin6">
                <!-- Sidebar navigation-->
                <nav class="sidebar-nav">
                    <ul id="sidebarnav">

                        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="<?= domain; ?>/user/dashboard" aria-expanded="false"><i data-feather="home" class="feather-icon"></i><span class="hide-menu">Dashboard</span></a></li>
                        <li class="list-divider"></li>


                        <li class="nav-small-cap"><span class="hide-menu">Core</span></li>

                        <li class="sidebar-item"> <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <i data-feather="file-text" class="feather-icon"></i><span class="hide-menu">Marketplace </span></a>
                            <ul aria-expanded="false" class="collapse  first-level base-level-line">
                                <li class="sidebar-item"><a class="sidebar-link sidebar-link" href="<?= domain; ?>/user/marketplace">Shop</a></li>
                                <li class="sidebar-item"><a class="sidebar-link sidebar-link" href="<?= domain; ?>/user/orders">My Orders</a></li>
                                <li class="sidebar-item"><a class="sidebar-link sidebar-link" href="<?= domain; ?>/user/products">My Products</a></li>
                                <li class="sidebar-item"><a class="sidebar-link sidebar-link" href="<?= domain; ?>/user/sales">My Sales</a></li>

                            </ul>

                        </li>

                        <li class="sidebar-item"> <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <i data-feather="file-text" class="feather-icon"></i><span class="hide-menu">External Streams </span></a>
                            <ul aria-expanded="false" class="collapse  first-level base-level-line">
                                <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>">Home</a></li>


                                <li class="sidebar-item"> <a class="has-arrow sidebar-link" href="javascript:void(0)" aria-expanded="false"><span class="hide-menu">Online services</span></a>
                                    <ul aria-expanded="false" class="collapse second-level base-level-line">
                                        <!-- <li class="sidebar-item"><a href="<?= domain; ?>/user/marketplace" class="sidebar-link"><span class="hide-menu">Marketplace</span></a></li> -->
                                        <li class="sidebar-item"><a href="https://webtalklegends.com/" class="sidebar-link"><span class="hide-menu"> Webtalk legends</span></a></li>
                                        <!-- <li class="sidebar-item"><a href="https://www.legendslinks.com/" class="sidebar-link"><span class="hide-menu"> Legends links</span></a></li> -->
                                        <li class="sidebar-item"><a href="https://unifyinglegends.com/" class="sidebar-link"><span class="hide-menu"> Unifying Legends </span></a></li>

                                        <!-- <li class="sidebar-item"><a href="https://stackingwithlegends.com/" class="sidebar-link"><span class="hide-menu"> Stacking with Legends </span></a></li> -->
                                        <li class="sidebar-item"><a href="https://viirallegends.com/" class="sidebar-link"><span class="hide-menu"> Viiral Legends </span></a></li>
                                        <li class="sidebar-item"><a href="https://legends-projects.myspreadshop.com/" class="sidebar-link"><span class="hide-menu"> Merch Store </span></a></li>



                                    </ul>
                                </li>


                                <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/savings">Savings</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/loan-center">Loan center</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/pg/contact">Contact us</a></li>
                            </ul>
                        </li>


                        <!-- <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="<?= domain; ?>/user/site-walkthrough" aria-expanded="false"><i data-feather="home" class="feather-icon"></i><span class="hide-menu">Site Walkthrough</span></a></li> -->




                        <li class="sidebar-item"> <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <i data-feather="file-text" class="feather-icon"></i><span class="hide-menu">Basic Area </span></a>
                            <ul aria-expanded="false" class="collapse  first-level base-level-line">
                                <!-- <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/promotion_materials">Promotion materials</a></li> -->
                                <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/basic_members_training">Webtalk legends training</a></li>
                                <!-- <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/legends_links_training">Legends links training</a></li> -->
                                <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/unifying_legends_training">Unifying Legends training</a></li>

                                <!-- <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/swl_training">SWL training</a></li> -->
                                <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/viiral_legends_training">Viiral Legends training</a></li>
                            </ul>
                        </li>



                        <li class="sidebar-item"> <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <i data-feather="file-text" class="feather-icon"></i><span class="hide-menu">Premium Area </span></a>
                            <ul aria-expanded="false" class="collapse  first-level base-level-line">
                                <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/premium-members-training">Zoom with CEO</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/affiliate">Affiliate Marketing</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/video_marketing">Video Marketing</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/music_marketing">Music Marketing</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/personal-development">Personal Development</a></li>

                                <!-- <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/ai_art"> NFT’s & AI Art </a></li> -->



                            </ul>
                        </li>






                        <li class="sidebar-item"> <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <i data-feather="file-text" class="feather-icon"></i><span class="hide-menu">Verification </small>
                                </span></a>
                            <ul aria-expanded="false" class="collapse  first-level base-level-line">
                                <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/identity_verification">Identity Verification</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/affiliate_agreement">Affiliate Agreement</a></li>
                                <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/terms_and_condition">Terms and Conditions</a></li>
                            </ul>
                        </li>




                        <!-- <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="<?= domain; ?>/user/testimonies" aria-expanded="false"><i data-feather="home" class="feather-icon"></i><span class="hide-menu">Testimonies</span></a></li> -->
                        <li class="sidebar-item"> <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <i data-feather="file-text" class="feather-icon"></i><span class="hide-menu">My Wallet </span></a>
                            <ul aria-expanded="false" class="collapse  first-level base-level-line">
                                <li class="sidebar-item">
                                    <a href="<?= domain; ?>/user/wallet" class=" sidebar-link">
                                        <span class="hide-menu"> Wallet</span>
                                    </a>
                                </li>

                                <li class="sidebar-item">
                                    <a href="<?= domain; ?>/user/withdrawal-methods" class=" sidebar-link">
                                        <span class="hide-menu"> Withdrawal Methods</span>
                                    </a>
                                </li>

                            </ul>
                        </li>


                        <li class="sidebar-item"> <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                                <i data-feather="file-text" class="feather-icon"></i><span class="hide-menu">My Team </span></a>
                            <ul aria-expanded="false" class="collapse  first-level base-level-line">
                                <li class="sidebar-item">
                                    <a href="<?= domain; ?>/genealogy/direct_list/<?= $auth->username; ?>/all/enrolment/1" class=" sidebar-link">
                                        <span class="hide-menu"> Direct Referral</span>
                                    </a>
                                </li>


                                <li class="sidebar-item">
                                    <a href="<?= domain; ?>/genealogy/direct_list/<?= $auth->username; ?>/all/placement/1" class=" sidebar-link">
                                        <span class="hide-menu"> Forced Matrix</span>
                                    </a>
                                </li>


                                <li class="sidebar-item">
                                    <a href="<?= domain; ?>/genealogy/placement/<?= $auth->username; ?>/placement" class=" sidebar-link">
                                        <span class="hide-menu"> Forced Tree</span>
                                    </a>
                                </li>


                                <!-- 
                                <li><a class="menu-item" href="<?= domain; ?>/genealogy/placement_list/<?= $auth->username; ?>/1/placement/1">Direct Referral</a></li>
                                <li><a class="menu-item" href="<?= domain; ?>/genealogy/placement/<?= $auth->username; ?>/binary/2">Binary Tree</a>

                                </li>
                                <li><a class="menu-item" href="<?= domain; ?>/user/direct-ranks">Direct Ranks</a>
                            </li>
                                -->

                            </ul>
                        </li>



                        <li class="sidebar-item"> <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false"><i data-feather="grid" class="feather-icon"></i><span class="hide-menu">My Account </span></a>
                            <ul aria-expanded="false" class="collapse  first-level base-level-line">

                                <li class="sidebar-item">
                                    <a href="<?= domain; ?>/user/account_plan" class="sidebar-link">
                                        <span class="hide-menu"> Account Plans</span>
                                    </a>
                                </li>

                                <li class="sidebar-item">
                                    <a href="<?= domain; ?>/user/profile" class="sidebar-link">
                                        <span class="hide-menu"> My Profile</span>
                                    </a>
                                </li>


                                <li class="sidebar-item">
                                    <a href="<?= domain; ?>/user/change_password" class="sidebar-link">
                                        <span class="hide-menu"> Change Password</span>
                                    </a>
                                </li>


                            </ul>
                        </li>

                        <li class="list-divider"></li>
                        <li class="nav-small-cap"><span class="hide-menu">Communication</span></li>


                        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="<?= domain; ?>/user/support" aria-expanded="false"><i class="fa fa-phone"></i><span class="hide-menu">Support</span></a></li>

                        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="<?= domain; ?>/login/logout" aria-expanded="false"><i data-feather="log-out" class="feather-icon"></i><span class="hide-menu">Logout</span></a></li>
                    </ul>
                </nav>
                <!-- End Sidebar navigation -->
            </div>
            <!-- End Sidebar scroll-->
        </aside>


        <style>
            .container-fluid {
                padding: 10px !important;
            }




            #myIframe {
                width: 100%;
                border: 0px;
                height: 1700px;
            }
        </style>

        <script>
            class CountDown {
                constructor(id, seconds) {
                    let $now = new Date().add_secs(seconds);
                    let x_xxx = setInterval(function() {
                        // Find the distance between now an the count down date
                        let distance = $now.getTime() - new Date().getTime();
                        // Time calculations for days, hours, minutes and seconds
                        let days = Math.floor(distance / (1000 * 60 * 60 * 24));
                        let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));

                        let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        let seconds = Math.floor((distance % (1000 * 60)) / 1000);
                        document.getElementById(id).innerHTML = days + "d " + hours + "h " + minutes + "m " + seconds + "s ";
                        // If the count down is over
                        if (distance < 0) {
                            clearInterval(x_xxx);
                            document.getElementById(id).innerHTML = "TIMEOUT";
                        }
                    }, 1000);
                }
            }

            Date.prototype.add_secs = function($secs) {
                this.setTime(this.getTime() + ($secs * 1000));
                return this;
            }

            // Selecting the iframe element
            var iframe = document.getElementById("myIframe");

            // Adjusting the iframe height onload event
            iframe.onload = function() {
                iframe.style.height = iframe.contentWindow.document.body.scrollHeight + 'px';
            }
        </script>