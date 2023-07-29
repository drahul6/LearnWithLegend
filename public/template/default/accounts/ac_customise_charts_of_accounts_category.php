<?php
include 'inc/headers.php';

use v2\Models\Wallet\BasicAccountType;
?>

<div>
    <h4 class="mg-b-0 tx-spacing--1">Customise Accouts</h4>
</div>

<div class="row">


    <div class="btn-group">
        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#create_chart_of_account">
            + New Account
        </button>
    </div>




    <table id="charts_of_accounts_table" class="table table-hover" style="margin-top: 20px;">
        <thead>
            <tr>
                <th>ID</th>
                <th>ACCOUNT NAME</th>
                <th>BASIC CATEGORY(code)</th>
                <th>DATE</th>
                <th>*</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($company_account_types as $custom_type) : ?>

                <tr>
                    <td><?= $custom_type->id; ?></td>
                    <td><?= $custom_type->name; ?></td>
                    <td><b><?= $custom_type->basic_account->name; ?></b>
                        (<?= ($custom_type->basic_account->code); ?>)</td>
                    <td> <?= $custom_type->created_at->format('M j, Y g:i A'); ?></td>

                    <td>
                        <a href="<?= domain; ?>/accounts/edit-customised-account/<?= $custom_type->id; ?>" class="btn btn-primary btn-xs"><i class="fa fa-pencil"></i> Edit</a>
                        <a onclick="$confirm_dialog = 
                                    new ConfirmationDialog('<?= domain; ?>/accounts/delete_customised_account/<?= $custom_type->id; ?>')" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i> Delete</a>
                    </td>
                </tr>




            <?php endforeach; ?>

        </tbody>
    </table>





    <!-- Modal -->
    <div id="create_chart_of_account" class="modal fade " style="display: ;" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Create Account</h4>
                </div>
                <div class="modal-body">
                    <form id="create_chart_of_account_category_form">
                        <div class="form-group">
                            <label>Account Type *</label>
                            <select name="account_type" required="" class="form-control">

                                <option value="">Select Basic Account</option>
                                <?php foreach (BasicAccountType::all() as $basic_account) : ?>

                                    <option value="<?= $basic_account->id; ?>"><?= $basic_account->name; ?></option>

                                <?php endforeach; ?>

                            </select>
                        </div>

                        <div class="form-group">
                            <label>Account Category(Name)*</label>
                            <input type="" name="name" required="" class="form-control">
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger">Save</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>



        <script>
            $("body").on("submit", "#create_chart_of_account_category_form", function(e) {
                e.preventDefault();


                dataString = $("#create_chart_of_account_category_form").serialize();

                $.ajax({
                    type: "POST",
                    url: $base_url + "/accounts/create_chart_of_accounts_categories/",
                    data: dataString,
                    cache: false,
                    success: function(data) {
                        console.log(data);

                        window.notify();

                        if (typeof(data) == 'object') {

                            window.location.href = window.location.href;
                        }


                    },
                    error: function(data) {}
                });
            });
        </script>
    </div>

    <?php include 'inc/footers.php'; ?>

</div>