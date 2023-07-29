<?php


$page_title = " Referrals";
include 'includes/header.php'; ?>

<div class="page-wrapper">

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-6 align-self-center">
                <h3 class="page-title text-truncate text-dark font-weight-medium mb-1"> Referrals</h3>

                <!-- 
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="">Level <?= $level; ?></a>
                            </li>
                        </ol>
                    </nav>
                </div> -->
            </div>
            <div class="col-6">
                <div class="customize-input">
                    <?= $note ?? ''; ?>
                </div>
            </div>
        </div>
    </div>


    <div class="container-fluid">

        <?= $this->view("composed/gynealogy_list", compact('username', 'level_of_referral', 'tree_key'), true, true); ?>

    </div>

    <?php include 'includes/footer.php'; ?>