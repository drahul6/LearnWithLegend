<?php
$user_ethereum = v2\Models\UserWithdrawalMethod::for($auth->id, 'ethereum');
$ethereum_details = @$user_ethereum->DetailsArray;; ?>
<div class="form-group">
    <label>Ethereum Address</label>
    <input type="" placeholder="Enter Ethereum Address" value="<?= $ethereum_details['ethereum_address'] ?? ''; ?>" name="details[ethereum_address]" required="" class="form-control">
</div>