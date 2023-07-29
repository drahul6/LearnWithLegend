<?php
$page_title = "Affiliate Agreement";
include 'includes/header.php';
?>

<div class="page-wrapper">

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Affiliate Agreement</h3>
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

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?= CMS::fetch('affiliate_agreement'); ?>
                    <!-- <iframe id="myIframe" src="https://onedrive.live.com/embed?cid=3B7B87099F764B86&resid=3B7B87099F764B86%219981&authkey=AIz4dNKma-nzDj8&em=2" width="476" height="288" frameborder="0" scrolling="no"></iframe> -->
                </div>
            </div>
        </div>


    </div>

    <?php include 'includes/footer.php'; ?>