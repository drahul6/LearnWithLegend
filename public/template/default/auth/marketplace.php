<?php
$page_title = "Marketplace";
include 'includes/header.php';
?>

<div class="page-wrapper">

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Marketplace</h3>

                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href=""><?= $note; ?></a>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 text-right">
                <?php include_once 'template/default/composed/filters/shop.php'; ?>
            </div>
        </div>
    </div>


    <div class="container-fluid">


        <?php include "$template/composed/shop/shop.php"; ?>






        <!-- 
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    ewe


                </div>
            </div>
        </div> -->


    </div>

    <?php include 'includes/footer.php'; ?>