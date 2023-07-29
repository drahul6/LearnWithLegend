<?php

use v2\Models\InvestmentPackage;
use v2\Models\HotWallet;
use v2\Models\Wallet;
use v2\Models\HeldCoin;
use v2\Models\PayoutWallet;

use Illuminate\Database\Capsule\Manager as DB;


/**
 *
 */
class MembershipCorrectionController extends controller
{


    public function __construct()
    {
        die();

    }


    //SELECT * FROM `wallet` WHERE type='debit' and comment like "%purchased gold%"
    public function reset_all_debit_on_deposit_wallet($value='')
    {

        Wallet::Debit()->where('payment_method', '=', "account_plan")->update(['status'=>'cancelled']);

        $today = date("Y-m-d");
        $correct_debit = PayoutWallet::Debit()->where('earning_category', '=', "account_plan")
                            ->whereDate('created_at', $today)
                            ->where('comment','like', "%##%")
                            ->get()->pluck('user_id');


        echo "<pre>";

        $excluded_users= $correct_debit->toArray();
        print_r($excluded_users);

        $users =  User::where('account_plan', 2)->whereNotIn('id', $excluded_users)->get();


        print_r($users->count());

        $paid_at = date("Y-m-d", strtotime("+30 days"));

        //create debit for next 30days

        // return;

        DB::beginTransaction();

        try {
            
            foreach ($users as $key => $user) {
                $debits[] =    PayoutWallet::createTransaction(
                            'debit',
                            $user->id,
                            null,
                            '22.99',
                            'completed',
                            'account_plan',
                            'Purchased Gold Partner for 29.99 ##',
                            NULL,
                            NULL,
                            NULL,
                            NULL,
                            $paid_at,
                            NULL,
                            false
                        );
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            
        }

        print_r(count($debits));



    }


    public function create_new_subscription_for_those_on_gold($value='')
    {
        //delete all current subscription

        //create new ones for people on gold to expires and debit in 30days 
        
        # code...
    }




}