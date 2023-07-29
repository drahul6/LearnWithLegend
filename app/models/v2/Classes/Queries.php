<?php

namespace v2\Classes;

use Illuminate\Database\Capsule\Manager as DB;

class Queries
{




    public function totalAmountShared()
    {
        $u =  DB::connection('wallet')->select("SELECT sum(amount) as total_paid_commission FROM `ac_account_journals` WHERE tag ='pools_commission'");
        return $u[0]->total_paid_commission;
    }


    public function getAllPremiumMembers()
    {

        $u =  DB::select("
        SELECT COUNT(*) total_premium
        FROM
        (SELECT 
        COUNT(*) FROM `subscription_payment_orders`
        WHERE
        `paid_at` IS NOT NULL 
        AND `plan_id` != 1
        AND `expires_at` > NOW()
        GROUP BY
        `user_id`) AS t");

        return $u[0]->total_premium;
    }
}
