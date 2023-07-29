<?php
$page_title = "Withdrawal history";
include 'includes/header.php';
?>

<div class="page-wrapper">

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Withdrawal history</h3>
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


                    <div class=" list-group">
                        <?php if ($withdrawals->isEmpty()) : ?>
                            <div class="text-center">Your Withdrawals will show here</div>
                        <?php endif; ?>





                        <?php foreach ($withdrawals as $withdrawal) :
                            $detail = $withdrawal->ExtraDetailArray;
                        ?>
                            <a href="javascript:void(0)" class="list-group-item list-group-item-action flex-column align-items-start">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">#<?= $withdrawal->id; ?> <?= $withdrawal->withdrawal_method->method; ?></h5>
                                    <small><?= date("M j, Y h:ia", strtotime($withdrawal->created_at)); ?></small>
                                </div>
                                <p class="mb-1"><?= $currency; ?><?= $withdrawal->amount; ?></p>
                                <small><?= $withdrawal->DisplayStatus; ?></small>
                            </a>
                        <?php endforeach; ?>

                    </div>

                </div>
            </div>
        </div>


    </div>

    <?php include 'includes/footer.php'; ?>