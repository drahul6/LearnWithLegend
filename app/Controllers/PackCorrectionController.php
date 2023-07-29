<?php

use v2\Models\InvestmentPackage;
use v2\Models\HotWallet;
use v2\Models\HeldCoin;
use v2\Models\PayoutWallet;

use Illuminate\Database\Capsule\Manager as DB;


/**
 *
 */
class PackCorrectionController extends controller
{


    public function __construct()
    {
        die();
    }


    public function prepapre_to_update_schedule()
    {


    	DB::beginTransaction();

    	try {
	    		$update =	HotWallet::Category('investment')->where('cost', '!=', null)->update(['split_at'=> null]);
    		DB::commit();
    	} catch (Exception $e) {
    		DB::rollback();
    		
    		print_r($e->getMessage());
    	}


    }


    public function update_schedule()
    {


    	echo "<pre>";

        $per_page = 50; 

	    $packs =	HotWallet::Category('investment')->Credit()->where('cost', '!=', null)->where('split_at', null)->take($per_page)->get();


	    foreach ($packs as $key => $pack) {

		    $investment_id = $pack->ExtraDetailArray['investment']['id'];



	    	$capital = $pack->ExtraDetailArray['capital'];

	    	$investment = InvestmentPackage::find($investment_id);
	    	 
	    	$investment->setAmount($capital);



	    	  $schedule = $investment->spread('weekly', false, $pack->paid_at);
	    	  // print_r($schedule);

	    	  $i_details = $pack->ExtraDetailArray;

	    	  $i_details['spread'] = $schedule['spread'];
	    	  $i_details['split_dates'] = $schedule['split_dates'];


	    	  DB::beginTransaction();

	    	  try {
	    	  	

		    	  $pack->update([
		    	      'extra_detail' => json_encode($i_details),
		    	      'split_at' => $schedule['split_dates'][0]

		    	  ]);
	
		    	  InvestmentPackage::setRoi($pack->id);
                  $count[] = $pack->id;

                  // break;
	    	  	DB::commit();
	    	  } catch (Exception $e) {

	    	  	print_r($e->getMessage());
	    	  	DB::rollback();
	    	  }


	    }

        print_r($count);

    }

    //correct all credits in payout from hot wallet, turn balance to zero
    public function correct_payout_wallet()
    {


    	    	///all the debit that occured so we can (before we) split 80/20 rule
    	    	$payout_wallet_splits = PayoutWallet::Category('hot_wallet')->Credit()->Paid()->Completed()
    	    	->whereRaw("('identifier' not like '#payoutwrong%')")
    	    	->get()

    	    	;


    	    	echo "<pre>";
    	    	print_r($payout_wallet_splits->count());
    	    	echo "<br>";

                // return;

    	$today = date("Y-m-d");

    	    	DB::beginTransaction();
    	    	try {


    	    		foreach ($payout_wallet_splits as $key => $wrong_credit) {

    	    			$comment = "Reversal of {$wrong_credit->id} #wrong";
    	    			$identifier = "#payoutwrong{$wrong_credit->id}";
    	    			$credit =PayoutWallet::createTransaction(
    	    				'debit',
    	    				$wrong_credit->user_id,
    	    				null,
    	    				$wrong_credit->amount,
    	    				'completed',
    	    				'hot_wallet',
    	    				$comment,
    	    				$identifier, 
    	    				null, 
    	    				null,
    	    				null,
    	    				$today,
    	    				null,
    	    				false
    	    			);


    	    			if ($credit == false) {
    	    				continue;
    	    			}

    	    			$successful_debits[]=1;

    	    		}

    	    		DB::commit();

    	    	} catch (Exception $e) {
    	    		DB::rollback();

    	    	}


    	    	print_r(array_sum($successful_debits));


    	


    }


    //correct all credits in cold wallet from hot wallet, turn balance to zero
    public function correct_cold_wallet()
    {       

            $per_page =50;

    	    	///all the debit that occured so we can (before we) split 80/20 rule
    	$cold_wallet_splits = HeldCoin::Category('hot_wallet')->Credit()->Paid()->Completed()
    	    	->whereRaw("'identifier' not like '#coldwrong%'")
    	    	// ->take($per_page)
                ->get()
    	    	;


    	    	echo "<pre>";
    	    	print_r($cold_wallet_splits->count());
    	    	echo "<br>";

    	$today = date("Y-m-d");

    	    	DB::beginTransaction();
    	    	try {


    	    		foreach ($cold_wallet_splits as $key => $wrong_credit) {

    	    			$comment = "Reversal of {$wrong_credit->id} #wrong";
    	    			$identifier = "#coldwrong{$wrong_credit->id}";
    	    			$credit =HeldCoin::createTransaction(
    	    				'debit',
    	    				$wrong_credit->user_id,
    	    				null,
    	    				$wrong_credit->amount,
    	    				'completed',
    	    				'hot_wallet',
    	    				$comment,
    	    				$identifier, 
    	    				null, 
    	    				null,
    	    				null,
    	    				$today,
    	    				null,
    	    				false
    	    			);


    	    			if ($credit == false) {
    	    				continue;
    	    			}

    	    			$successful_debits[]=1;

    	    		}

    	    		DB::commit();

    	    	} catch (Exception $e) {
    	    		DB::rollback();

    	    	}


    	    	print_r(array_sum($successful_debits));


    }

    //correct all credits in cold wallet from hot wallet, turn balance to zero
    public function correct_hot_wallet()
    {
    	$today = date("Y-m-d");
        $per_page= 50;

    	///all the debit that occured so we can (before we) split 80/20 rule
    	$investment_roi_debit = HotWallet::Category('investment')->Debit()->Paid()->Completed()->where('cost', null)
    	    	->whereRaw("'identifier' not like '#hotwronginvestmentdebit%'")
                ->where('comment', 'like', '%split%')
                ->take($per_page)
    	->get();






        echo $investment_roi_debit->count();

        // return;
        echo "<br>";
        // return;

        // return;

    	DB::beginTransaction();
    	try {


    		foreach ($investment_roi_debit as $key => $wrong_debit) {
                // break;

    			$comment = "Reversal of {$wrong_debit->id} #wrong";
    			$identifier = "#hotwronginvestmentdebit{$wrong_debit->id}";
    			$credit =HotWallet::createTransaction(
    				'credit',
    				$wrong_debit->user_id,
    				null,
    				$wrong_debit->amount,
    				'completed',
    				'investment',
    				$comment,
    				$identifier, 
    				null, 
    				null,
    				null,
    				$today
    			);


    			if ($credit == false) {
    				continue;
    			}

    			$successful_credits[]=1;

    		}

    		DB::commit();

    	} catch (Exception $e) {
    		DB::rollback();

    	}


        print_r(array_sum($successful_credits));

echo "<br>";





        //all hot wallet credit from investments
        

        //SELECT * FROM `wallet_for_hot_wallet` WHERE earning_category='hot_wallet' and type='credit' ORDER BY `id` ASC



    	$hotwallet_roi_credit = HotWallet::Category('hot_wallet')->Credit()->Paid()->Completed()->where('cost', null)
    	    	->whereRaw("'identifier' not like '#hotwronginvestmentcredit%'")->take($per_page)->get();
    	;

        echo $hotwallet_roi_credit->count();

    		foreach ($hotwallet_roi_credit as $key => $wrong_credit) {

    	// break;

    	DB::beginTransaction();
    	try {


    			$comment = "Reversal of {$wrong_credit->id} #wrong";
    			$identifier = "#hotwronginvestmentcredit{$wrong_credit->id}";
    			$debit =HotWallet::createTransaction(
    				'debit',
    				$wrong_credit->user_id,
    				null,
    				$wrong_credit->amount,
    				'completed',
    				'hot_wallet',
    				$comment,
    				$identifier, 
    				null, 
    				null,
    				null,
    				$today,
    				null,
    				false
    			);


    			if ($debit == false) {


    				continue;
    			}

    			$successful_debits[]=1;


    		DB::commit();

    	} catch (Exception $e) {
    		DB::rollback();

    	}
    }

        print_r(array_sum($successful_debits));


    echo "<br>";

        //the ones that go to coldwallet


        $hotwallet_credit_to_coldwallet = HotWallet::Category('hot_wallet')->Debit()->Paid()->Completed()->where('cost', null)
                ->whereRaw("'identifier' not like '#hotwronginvestmentcredit%'")
                ->where('comment','like', '%move%')
                ->take($per_page)->get();
        ;

        echo $hotwallet_credit_to_coldwallet->count();

            foreach ($hotwallet_credit_to_coldwallet as $key => $wrong_credit) {

        // break;

        DB::beginTransaction();
        try {


                $comment = "Reversal of {$wrong_credit->id} #wrong";
                $identifier = "#hotwronginvestmentcredit{$wrong_credit->id}";
                $credit =HotWallet::createTransaction(
                    'credit',
                    $wrong_credit->user_id,
                    null,
                    $wrong_credit->amount,
                    'completed',
                    'hot_wallet',
                    $comment,
                    $identifier, 
                    null, 
                    null,
                    null,
                    $today,
                    null,
                    false
                );


                if ($credit == false) {
                    continue;
                }

                $successful_credit[]=1;


            DB::commit();

        } catch (Exception $e) {
            DB::rollback();

        }
    }




print_r(array_sum($successful_credit));



/*
    	print_r($investment_roi_debit->count());
print_r(array_sum($successful_credits));
    	
    	echo "<br>";
    	echo "<br>";

    	print_r($hotwallet_roi_credit->count());

print_r(array_sum($successful_debits));*/
    	
    }

    
    
    public function index()
    {





    }


}


?>