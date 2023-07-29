<?php

use  v2\Shop\Shop;
use v2\Models\Wallet;
use v2\Models\PayoutWallet;
use v2\Models\Wallet\Classes\AccountManager;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;


class SubscriptionPlan extends Eloquent
{

	protected $fillable = [
		'name', //name
		'price',
		'hierarchy',
		'features',
		'availability',
		'details',
	];

	protected $table = 'account_plans';


	public static $benefits = [
		'traning_access' => [
			'title' => 'Access to Tranings',
		],
		'extras_access' => [
			'title' => 'Access to Extras',
		],
		'capped monthly earning: 20$' => [
			'title' => 'Access to Extras',
		],


	];


	public function scopePaidMembership($query)
	{
		return $query->available()->where('price', '>', 0);
	}


	public function getDetailsArrayAttribute()
	{
		if ($this->details == null) {
			return [];
		}

		return json_decode($this->details, true);
	}





	public static function default_sub()
	{
		return self::where('price', 0)->first();
	}


	public function getFinalcostAttribute()
	{
		return $this->price;
	}

	public function getPriceBreakdownAttribute()
	{
		$tax = 0.01 * 0 * $this->price;
		$breakdown = [
			'before_tax' => $this->price,
			'set_price' => $this->price,
			'total_percent_tax' => 0,
			'tax' =>  $tax,
			'type' =>  "exclusive",
			'total_payable' =>  $this->Finalcost,
		];

		return $breakdown;
	}


	public static function create_subscription_request($subscription_id, $user_id, $paid_at = null, $force = false)
	{

		DB::beginTransaction();

		try {

			$existing_requests = SubscriptionOrder::where('user_id', $user_id)
				->where('plan_id', $subscription_id)
				->latest('paid_at')
				->first();


			$user  			= User::find($user_id);
			$previous_sub 	= $user->subscription;
			$new_sub 		= self::find($subscription_id);


			//ensure this is not downgrade
			/* 	if ($force == false) {
				if ($new_sub->price  < $previous_price) {
					Session::putFlash('danger', "You cannot downgrade your subscription to {$new_sub->name}.");
					return json_encode([]);
				}
			} */


			/* 	if ($existing_requests != null) {
				if (!$existing_requests->is_expired() && ($existing_requests->payment_plan->id == $subscription_id)) {
					return;
				}
			} */


			//delete unuseful orders
			SubscriptionOrder::where('user_id', $user_id)->where('plan_id', '!=', $subscription_id)->where('paid_at', null)->delete();
			//cancel current subscription if automatic


			$paying_account = $user->getAccount('default');
			$payment = AccountManager::membership([
				"membership_account" => $paying_account->id,
				"amount" => $new_sub->price,
				"narration" => "membership $new_sub->name",
				"user_id" => $user_id,
			]);



			if (!$payment) {
				throw new Exception("Error Processing Request", 1);
			}


			$new_sub_order = SubscriptionOrder::create([
				'plan_id'  	=> $new_sub->id,
				'user_id' 		=> $user_id,
				'price'   		=> $new_sub->price,
				'payment_state' => 'manual',
				'payment_schedule' => json_encode([]),
				'details'		=> json_encode($new_sub),
			]);
			$new_sub_order->mark_paid();

			DB::commit();
			// $shop->goToGateway();

			return $new_sub_order;
		} catch (Exception $e) {
			DB::rollback();
		}

		return false;
	}



	public function is_available()
	{
		return (bool) ($this->availability == 'on');
	}



	public  function scopeavailable($query)
	{
		return $query->where('availability', 'on');
	}

	/* 	public static function available()
	{
		return self::where('availability', 'on');
	}
 */
}
