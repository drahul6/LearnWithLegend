<?php

namespace v2\Models;

use  v2\Models\Withdrawal;

use v2\Filters\Traits\Filterable;
use  v2\Traits\Wallet as BookRecords;
use v2\Shop\Contracts\OrderInterface;


use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Config, Notifications, Session, Exception, home;



/**
This represent
deposit wallet
 */
class Wallet extends Eloquent implements OrderInterface
{

	use  Filterable;
	use BookRecords {
		availableBalanceOnUser as TraitavailableBalanceOnUser;
		bookBalanceOnUser as TraitbookBalanceOnUser;
	}
	protected $fillable = [
		'user_id',
		'order_id',
		'admin_id',
		'upon_user_id',
		'amount',
		'payment_method',
		'payment_details',
		'payment_state',
		'paid_at',
		'type',
		'earning_category',
		'status',
		'identifier',
		'comment',
		'extra_detail'
	];


	protected $table = 'wallet';



	public  static $payment_types = [
		'paypal' => 'one_time',
		'coinpay' => 'one_time',
		'livepay' => 'one_time',
		'perfect_money' => 'one_time',
	];


	public static $wallet_classes = [

		'deposit' => [
			'name' => "Deposit Wallet",
			'class' => "v2\Models\Wallet",
		],

		'commission' => [
			'name' => "Commission Wallet",
			'class' => "v2\Models\Commission",
		],


	];

	public static $wallets = [
		'deposit' => [
			'name' => "Deposit Wallet",
			'class' => "v2\Models\Wallet",
			'category' => "deposit", //available balance
			'group' => "deposit",
		],

		'investment' => [
			'name' => "Wallet",
			'class' => "v2\Models\Wallet",
			'category' => "investment",
			'group' => "all", //debit
		],


		'commission' => [
			'name' => " Wallet",
			'class' => "v2\Models\Wallet",
			'category' => "commission"
		],


	];
	public $name_in_shop = 'deposit';


	public static $statuses = [
		'pending' => 'pending',
		'completed' => 'completed',
		'cancelled' => 'cancelled'
	];




	public static $types = [
		'credit' => 'credit',
		'debit' => 'debit',
	];





	public static function availableBalanceOnUser($user_id, $category = null, $as_at = null, $daterange = null)
	{
		$available_balance = self::TraitavailableBalanceOnUser($user_id, $category, $as_at, $daterange);


		$completed_withdrawal = Withdrawal::where('user_id', $user_id)->Completed()->sum('amount');
		$pending_withdrawal = Withdrawal::where('user_id', $user_id)->Pending()->sum('amount');

		$total_amount_withdrawn = $completed_withdrawal + $pending_withdrawal;

		$balance = $available_balance - $total_amount_withdrawn;


		return $balance;
	}


	public static function bookBalanceOnUser($user_id, $category = null, $as_at = null, $daterange = null)
	{
		$available_balance = self::TraitbookBalanceOnUser($user_id, $category, $as_at, $daterange);


		$completed_withdrawal = Withdrawal::where('user_id', $user_id)->Completed()->sum('amount');
		$pending_withdrawal = Withdrawal::where('user_id', $user_id)->Pending()->sum('amount');

		$total_amount_withdrawn = $completed_withdrawal + $pending_withdrawal;

		$balance = $available_balance - $completed_withdrawal;


		return $balance;
	}





	public function is_complete()
	{
		return $this->status == 'completed';
	}



	public function available_wallets($user = null)
	{
		$wallets = self::$wallets;

		if ($user == null) {
			return $wallets;
		}


		foreach ($wallets as $key => $wallet) {

			$class = $wallet['class'];

			if ($wallet['group'] == 'all') {

				$balance = $class::availableBalanceOnUser($user->id);

				if ($key == 'payout') {

					/*
					$completed_withdrawal = Withdrawal::where('user_id' , $user->id)->Completed()->sum('amount');
					$pending_withdrawal = Withdrawal::where('user_id' , $user->id)->Pending()->sum('amount');

					$total_amount_withdrawn = $completed_withdrawal + $pending_withdrawal ;
					$balance = $balance  - $total_amount_withdrawn;*/
				}
			} else {
				$balance = $class::availableBalanceOnUser($user->id, $wallet['category']);
			}
			$wallets[$key]['balance'] = round($balance, 2);
		}


		///remove 
		$showables = ['payout', 'deposit'];
		foreach ($wallets as $key => $wallet) {
			if (!in_array($key, $showables) || ($wallet['balance'] <= 0)) {
				unset($wallets[$key]);
			}
		}

		return $wallets;
	}





	public function getreverifyLinkAttribute()
	{
		$domain = Config::domain();
		$param = http_build_query([
			'item_purchased' => $this->name_in_shop,
			'order_unique_id' => $this->id,
			'payment_method' =>  $this->payment_method,
		]);



		return "$domain/shop/re_confirm_order/?$param";
	}




	public function user()
	{
		return $this->belongsTo('User', 'user_id');
	}


	public function getPaymentDetailsArrayAttribute()
	{
		if ($this->payment_details == null) {
			return [];
		}

		$details =  json_decode($this->payment_details, true);

		$details['approval'] =  json_decode($details['approval'], true);

		return $details;
	}



	public function getExtraDetailArrayAttribute()
	{
		if ($this->extra_detail == null) {
			return [];
		}

		return json_decode($this->extra_detail, true);
	}




	public function getHashRateAttribute()
	{
		$detail = $this->ExtraDetailArray;

		return $detail['hash_rate'] ?? '';
	}




	public function total_qty()
	{
		return 1;
	}



	public function getPriceBreakdownAttribute()
	{
		$percent_vat = 0;
		$tax = 0.01 * $percent_vat * $this->amount;
		$breakdown = [
			'before_tax' => $this->amount,
			'set_price' => $this->amount,
			'total_percent_tax' => $percent_vat,
			'tax' =>  $tax,
			'type' =>  "exclusive",
			'total_payable' =>  $this->amount,
		];

		return $breakdown;
	}

	public function setPaymentBreakdown(array $payment_breakdown, $order_id = null)
	{
		$this->update([
			'order_id' => $order_id,
			'payment_breakdown' => json_encode($payment_breakdown),
			'amount_payable' => $payment_breakdown['total_payable']['value'],
		]);

		return $this;
	}

	public function getBuyerAttribute()
	{
		if ($this->user_id != null) {

			return $this->user;
		}

		return $this->customer;
	}

	public function calculate_vat()
	{

		$setting = \SiteSettings::find_criteria('site_settings')->settingsArray;
		$vat_percent =  $setting['vat_percent'] ?? 0;

		$subtotal = $this->total_price();
		$vat = $vat_percent * 0.01 * $subtotal;


		$result = [
			'value' => $vat,
			'percent' => $vat_percent,
		];

		$result = [
			'value' => 0,
			'percent' => 0,
		];


		return $result;
	}
	public function total_tax_inclusive()
	{

		$breakdown = $this->PriceBreakdown;

		$tax = [
			'price_inclusive_of_tax' => $breakdown['total_payable'],
			'price_exclusive_of_tax' => $breakdown['set_price'],
			'total_sum_tax' => $breakdown['tax'],
		];

		return $tax;
	}


	public function total_price()
	{
		return $this->amount;
	}




	public function paymentMethod()
	{
		$payment_details = json_decode($this->payment_details, true);
		$gateway = str_replace("_", " ", $payment_details['gateway']);

		return $gateway;
	}

	public function TransactionID()
	{

		$payment_details = json_decode($this->payment_details, true);
		$id = $payment_details['ref'] ?? '';

		return $id;
	}

	public function getTransactionIDAttribute()
	{

		$payment_details = json_decode($this->payment_details, true);
		$id = $payment_details['ref'] ?? '';
		$gateway = str_replace("_", " ", $payment_details['gateway']);
		$method = "{$id}<br><span class='badge badge-primary'>{$gateway}</span>";

		return $method;
	}


	public function getInvoiceIDAttribute()
	{

		$payment_details = json_decode($this->payment_details, true);
		$method = "{$payment_details['ref']}";

		return $method;
	}



	public function mark_paid()
	{

		if ($this->is_paid()) {
			Session::putFlash('info', 'Deposit Completed');
			return false;
		}

		DB::beginTransaction();
		try {

			$this->update([
				'paid_at' => date("Y-m-d H:i:s"),
				'status' => 'completed',
			]);


			$currency = Config::currency();
			$url =  "user/scheme";
			$heading = $this->TransactionID . "  Deposit";
			$short_message = "See Details of Current Package.";

			$message = "Deposit of  $currency $this->amount completed";
			Notifications::create_notification(
				$this->user_id,
				$url,
				$heading,
				$message,
				$short_message
			);


			DB::commit();
			Session::putFlash('success', 'Order marked as completed');
			Shop::empty_cart_in_session();

			return true;
		} catch (Exception $e) {
			DB::rollback();
			print_r($e->getMessage());
			Session::putFlash('danger', 'Order could not mark as completed');
		}

		return false;
	}



	public function is_paid()
	{

		return (bool) ($this->paid_at != null);
	}




	public function generateOrderID()
	{

		$substr = substr(strval(time()), 7);
		$order_id = "PA{$this->id}D{$substr}";

		return $order_id;
	}




	public function getDepositPaymentStatusAttribute()
	{
		if ($this->paid_at != null) {

			$label = '<span class="badge badge-success">Paid</span>';
		} else {
			$label = '<span class="badge badge-danger">Unpaid</span>';
		}

		return $label;
	}


	public function setPayment($payment_method, array $payment_details)
	{


		$this->update([
			'payment_method' => $payment_method,
			'payment_state' => @$payment_details['payment_type'],
			'payment_details' => json_encode($payment_details),
		]);

		return $this;
	}




	public  function create_order($cart)
	{
		extract($cart);
		$new_payment_order = self::create([
			'user_id' 		=> $user_id,
			'amount'   		=> $amount,
			'details'		=> json_encode($payment_plan),
		]);

		return $new_payment_order;
	}






	public static function give_bonus($user_id, $upon_user_id = null)
	{

		$identifier = "$user_id welcome bonus";
		$amount = 500;
		$comment = "welcome bonus";
		try {

			$credit  = self::createTransaction(
				'credit',
				$user_id,
				$upon_user_id,
				$amount,
				'pending',
				'bonus',
				$comment,
				$identifier
			);
			return $credit;
		} catch (Exception $e) {

			print_r($e->getMessage());
			die();
		}
	}
}
