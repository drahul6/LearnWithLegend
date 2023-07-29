<?php
$page_title = "Support Messages";
include 'includes/header.php';
?>

<div class="page-wrapper">

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Messages</h3>

            </div>
            <div class="col-5 align-self-center">
                <div class="customize-input float-right">
                    <a class="btn btn-light" href="<?= domain; ?>/user/support">Support</a>
                </div>
            </div>
        </div>
    </div>


    <div class="container-fluid">
        <div class="row">

            <?php $template = Config::views_template();
            $person = 'user';
            $this->view('composed/support_chat', compact('person'));
                // require_once "template/$template/composed/support_chat.php";
            ; ?>


        </div>
    </div>

    <?php include 'includes/footer.php'; ?>