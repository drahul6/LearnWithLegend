<?php include 'inc/headers.php'; ?>


<div class="">
    <form id="create_chart_of_account_category_form" action="<?= domain; ?>/accounts/update_customised_account" method="post">
        <div class="form-group">
            <label>Account Type *</label>
            <select name="account_type" required="" class="form-control">

                <option value="">Select Basic Account</option>
                <?php foreach ($basic_accounts as $basic_account) : ?>

                    <option value="<?= $basic_account->id; ?>" <?= ($basic_account->id == $customised_account->basic_account_id) ? 'selected' : ''; ?>>
                        <?= $basic_account->name; ?>
                    </option>

                <?php endforeach; ?>

            </select>
        </div>
        <input type="hidden" name="customised_account_id" value="<?= $customised_account->id; ?>">
        <div class="form-group">
            <label>Account Name*</label>
            <input type="" name="name" value="<?= $customised_account->name; ?>" required="" class="form-control">
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-danger">Save</button>
        </div>
    </form>
</div>


<?php include 'inc/footers.php'; ?>