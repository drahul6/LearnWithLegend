<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="description" content="<?= @$page_description; ?>">
    <meta name="keywords" content="<?= @$page_keywords; ?>">
    <meta name="author" content="<?= @$project_name; ?>">
    <title><?= @$page_title; ?> | <?= project_name; ?></title>
    <link rel="apple-touch-icon" href="<?= $fav_icon; ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= $fav_icon; ?>">

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:image" content="<?= $logo; ?>/../preview.png" />

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <!-- jQuery library -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
    <!-- Popper JS -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <!-- Latest compiled JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body>
    <header>

        <nav class="navbar navbar-expand-md bg-dark navbar-dark fixed-top" style="padding-top: 0px;padding-bottom: 0px">


            <!-- Brand -->
            <a class="navbar-brand" href="#">
                <img style="height: 60px;" src="<?= $logo; ?>" alt="homepage" class="dark-logo" />

            </a>

            <!-- Toggler/collapsibe Button -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar links -->
            <div class="collapse navbar-collapse" id="collapsibleNavbar">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= domain; ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= domain; ?>/pg/contact">Contact us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= domain; ?>/register">Sign up</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= domain; ?>/login">Login</a>
                    </li>
                    <!--     <li class="nav-item">
                        <a class="nav-link" href="#">Link</a>
                    </li> -->
                </ul>

            </div>
        </nav>
        <style>
            .navbar-nav {
                font-size: 15px;
            }

            .main-content {

                margin-top: 59px;
                /* border: 1px solid red; */
                min-height: 100vh;
            }



            #myIframe {
                width: 100%;
                border: 0px;
                min-height: 1000px;
            }
        </style>
    </header>


    <main class="container-flui main-content">