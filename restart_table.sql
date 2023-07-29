-- for wallet table
TRUNCATE ac_involved_accounts;
TRUNCATE ac_account_journals;
UPDATE `ac_charts_of_accounts`
SET a_current_balance = 0,
    `current_balance` = 0,
    `a_opening_balance` = 0,
    `a_available_balance` = 0,
    `opening_balance` = 0,
    `available_balance` = 0;
delete from ac_charts_of_accounts
where owner_id is not null;
-- for regular table
--  TRUNCATE subscription_payment_orders
-- DELETE FROM `users` WHERE id != 1;