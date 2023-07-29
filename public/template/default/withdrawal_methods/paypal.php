<?php
$user_paypal = v2\Models\UserWithdrawalMethod::for($auth->id, 'paypal');
$paypal_details = @$user_paypal->DetailsArray;; ?>


<div class="form-group">
    <label>Email Address</label>
    <input type="email" placeholder="Enter Paypal Email Address" value="<?= $paypal_details['email_address'] ?? ''; ?>" name="details[email_address]" required="" class="form-control">
</div>