<?php
$page_title = "Withdrawal Methods";
include 'includes/header.php';
?>

<div class="page-wrapper">

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Withdrawal Methods</h3>
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

                    <?php foreach (v2\Models\UserWithdrawalMethod::$method_options as $key => $option) : ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header" data-toggle="collapse" data-target="#make_deposit<?= $option['name']; ?>">
                                        <span href="javascript:void;" class="card-title"><?= $option['name']; ?> Information</span>
                                        <div class="heading-elements">
                                            <ul class="list-inline mb-0">
                                                <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                                                <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                                            </ul>
                                        </div>

                                    </div>
                                    <div class="card-body  collapse show" id="make_deposit<?= $option['name']; ?>">

                                        <form class="col-12 ajax_form" method="POST" action="<?= domain; ?>/withdrawals/submit_withdrawal_information">

                                            <input type="hidden" name="method" value="<?= MIS::dec_enc('encrypt', $key); ?>">

                                            <?= $this->csrf_field(); ?>

                                            <?php

                                            $this->view($option['view'], [], true, true);; ?>

                                            <?= $this->use_2fa_protection(); ?>



                                            <div class="form-group">
                                                <button type="submit" class="btn btn-outline-secondary">Save</button>
                                            </div>

                                        </form>

                                    </div>

                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>





                </div>
            </div>
        </div>


    </div>

    <?php include 'includes/footer.php'; ?>