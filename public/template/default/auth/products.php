<?php
$page_title = "My Products";
include 'includes/header.php';
?>

<div class="page-wrapper">

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h3 style="display:inline;" class="page-title text-truncate text-dark font-weight-medium mb-1">My Products
                </h3>


                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href=""><?= $note ?? ""; ?></a>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center">
                <div class="customize-input float-right">
                    <div class="btn-group">
                        <a href="<?= domain; ?>/user/create_product" class="btn btn-outline- custom-shadow custom-radius">+ Create Product</a>
                    </div>

                </div>

            </div>
        </div>
    </div>


    <div class="container-fluid">
        <div class="col-12">

            <table class="table table-striped">
                <?php
                $i = 1;
                // $data = 1;
                // $per_page = 1;
                foreach ($products as $product) : ?>
                    <tr>
                        <div class="alert bg-white custom-shadow  alert-dismissible mb-2 " role="alert">
                            <!--<span style="margin-right:2px;"><?= $i++; ?>)</span>-->
                            <!-- <strong> Items x Qty: ds</strong> -->
                            <strong style="text-transform: capitalize;"> <?= $product->name ?? 'New Product'; ?>
                                <small class="float- "><?= $product->DisplayableStatus; ?> </small>
                            </strong>
                            <small class="badge badge-light"><?= $product->type_of_product; ?></small>
                            <br>
                            <small class="float- ">Edited: <?= date("M.j.y h:ia", strtotime($product->updated_at)); ?></small>
                            <div style="position: absolute;top: 10px;right: 25px;">
                                <small>Pool:<?= $product->data['pools_commission'] ?? 0; ?>%</small>
                                <small>Price:$<?= $product->price ?? '0'; ?></small><br>

                                <div class="btn-group btn-group-sm text-small">
                                    <a href="<?= $domain; ?>/user/edit_product/<?= $product->id; ?>" class="badge badge-light">Edit</a>
                                    <a onclick="$confirm_dialog=new ConfirmationDialog('<?= $product->UserDeleteLink; ?>','Pls confirm delete <?= $product->name ?? ''; ?>')" class="badge badge-light fas fa-trash-alt"> </a>
                                </div>
                            </div>
                        </div>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>



            <?php if (count($products) == 0) : ?>
                <div class="alert alert-white text-center">
                    Your products will show here.
                </div>
            <?php endif; ?>
            <ul class="pagination pagination-sm">
                <?= $this->pagination_links($data, $per_page); ?>
            </ul>
        </div>



    </div>
    <?php include 'includes/footer.php'; ?>