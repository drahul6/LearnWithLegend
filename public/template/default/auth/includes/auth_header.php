<!DOCTYPE html>
<html dir="ltr" xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= @$page_description; ?>">
    <meta name="keywords" content="<?= @$page_keywords; ?>">
    <meta name="author" content="<?= @$project_name; ?>">
    <title><?= @$page_title; ?> | <?= project_name; ?></title>
    <link rel="apple-touch-icon" href="<?= $fav_icon; ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= $fav_icon; ?>">

    <meta property="og:image" content="<?= $logo; ?>/../preview.png" />

    <!-- Custom CSS -->
    <link href="<?= $this_folder; ?>/../assets/dist/css/style.min.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body>
    <div class="main-wrapper">
        <!-- ============================================================== -->
        <!-- Preloader - style you can find in spinners.css -->
        <!-- ============================================================== -->
        <div class="preloader">
            <div class="lds-ripple">
                <div class="lds-pos"></div>
                <div class="lds-pos"></div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- Preloader - style you can find in spinners.css -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Login box.scss -->
        <!-- ============================================================== -->
        <div class="auth-wrapper d-flex no-block justify-content-center align-items-center position-relative">
            <div class="auth-box row">
                <div class=" col-md-12 bg-white">
                    <div class="p-3">
                        <div class="text-center">
                            <a href="<?= domain; ?>"><img src="<?= $logo; ?>" alt="<?= project_name; ?>" style="height: 100px; "></a>
                        </div>