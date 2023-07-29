<?php


namespace v2\Shop\Payments\Stripe;


use Stripe\StripeClient;
use Session;
use v2\Shop\Contracts\OrderInterface;
use v2\Shop\Contracts\PaymentMethodInterface;
use Exception, SiteSettings, Config, Redirect;

class Stripe  implements PaymentMethodInterface
{
	public $name = 'stripe';
	private $payment_type = 'one_time';
	private $mode;
	public $api_keys;
	public $stripe;
	public $order;

	protected static $currency = 'USD';

	function __construct()
	{

		$settings = SiteSettings::find_criteria('stripe_keys')->settingsArray;

		$this->mode = $settings['mode']['mode'];

		$this->api_keys =  $settings[$this->mode];
		$this->stripe = new StripeClient($this->api_keys['secret_key']);
	}


	public function setPaymentType($payment_type)
	{
		$this->payment_type = $payment_type;
		return $this;
	}


	public function goToGateway()
	{
		$payment_details = ($this->order->getPaymentDetails());;
		Redirect::to($payment_details['approval_url']);
	}


	public function paymentStatus()
	{
		return true;
	}



	public function reVerifyPayment()
	{
		return $this->verifyPayment();
	}



	public function verifyPayment()
	{
		$payment_details = $this->order->getPaymentDetails();
		$session_id = $payment_details['stripe_id'];
		$secret_key = $this->api_keys['secret_key'];

		try {
			$session = $this->stripe->checkout->sessions->retrieve(
				$session_id,
				[]
			);

			//confirm amount
			$expected_amount = $this->amountPayable();
			if ($expected_amount != $session->amount_total) {
				throw new Exception("We could not confirm amount paid", 1);
				return false;
			}


			//confirm currency
			if (strtolower(trim($payment_details['currency'])) != strtolower(trim($session->currency))) {
				throw new Exception("We could not confirm payment currency", 1);
				return false;
			}


			//confirm status
			if (strtolower(trim($session->payment_status)) != "paid") {
				throw new Exception("We could not confirm status as paid", 1);
				return false;
			}

			//confirm payment!        
			$result = (array)$session;
			$confirmation = ['status' => true];
			return compact('result', 'confirmation');
			Session::putFlash("success", "Payment successful");
		} catch (\Throwable $th) {
			Session::putFlash("danger", "{$th->getMessage()}");
			return false;
		}
	}


	public function setOrder(OrderInterface $order)
	{
		$this->order = $order;
		return $this;
	}


	public function amountPayable()
	{
		$amount =  $this->order->total_price()  * 100; //because amount is in cent

		return  round($amount, 2);
	}

	private function makeOneTimePayment()
	{

		$payment_method = $this->name;
		$order_ref = $this->order->generateOrderID();

		$user = $this->order->Buyer;
		$domain = Config::domain();

		$amount_payable = $this->amountPayable();

		$callback_param = http_build_query([
			'item_purchased' => $this->order->name_in_shop,
			'order_unique_id' => $this->order->id,
			'payment_method' => $this->name,
		]);


		if ($_ENV['APP_ENV'] == 'local') {
			$domain = "https://example.com";
		}



		$callback_url = "{$domain}/shop/callback?$callback_param";
		$cancel_url = "{$domain}/user/wallet";

		$currency = strtolower(self::$currency);

		// Create payment with valid API context
		try {

			$price = $this->stripe->prices->create([
				'unit_amount' => $amount_payable,
				'currency' => $currency,
				"product_data" => [
					"name" => "Order #$order_ref",
				],
			]);

			$checkout = $this->stripe->checkout->sessions->create([
				'success_url' => $callback_url,
				'cancel_url' => $cancel_url,
				'line_items' => [
					[
						'price' => $price->id,
						'quantity' => 1,
					],
				],
				"customer_email" => trim($user->email),
				'mode' => 'payment',
				"metadata" => []
			]);



			$payment_details = [
				'gateway' => $this->name,
				'currency' => $currency,
				'payment_type' => $this->payment_type,
				'item_purchased' => $this->order->name_in_shop,
				'ref' => $order_ref,
				'stripe_id' => $checkout->id,
				'order_unique_id' => $this->order->id,
				"approval_url" 	 =>  $checkout->url,
				"amount" 	 =>  $this->amountPayable(),
			];


			// Redirect the customer to $approvalUrl
			$this->order->setPayment($payment_method, $payment_details);
		} catch (\Exception $ex) {
			// die($ex);

		}


		return $this;
	}



	public function fetchAgreement()
	{

		$agreement_id = $this->order->PaymentDetailsArray['agreement_id'];

		try {

			$agreement = "";

			$array = current((array) current((array) $agreement)['agreement_details']);

			$response = [
				'next_billing_date' => $array['next_billing_date'],
				'last_payment_date' => @$array['last_payment_date'],
				'agreement_id' => $agreement_id
			];

			return $response;
		} catch (Exception $e) {
		}
	}


	public function cancelAgreement()
	{

		try {
		} catch (Exception $e) {
		}
	}


	public function executeAgreement()
	{


		try {


			return false;
			$confirmation = ['status' => true];
			return compact('result', 'confirmation');
		} catch (Exception $e) {
		}
	}


	private function makeSubscriptionPayment()
	{

		$payment_method = $this->name;
		$order_ref = $this->order->generateOrderID();
		$price_breakdown = $this->order->total_tax_inclusive();
		$user = $this->order->user;
		$domain = Config::domain();




		$subscription = null;
		$plan = null;
		$subscription_id =  null;


		$agreement = null;


		$id = $this->order->payment_plan->getPlanId('paypal');

		$approvalUrl =	null;

		$payment_details = [
			'gateway' => $this->name,
			'payment_type' => $this->payment_type,
			'payment_state' => 'automatic',
			'ref' => $order_ref,
			'order_unique_id' => $this->order->id,
			"approval_url" 	 =>  $approvalUrl,
			"amount" 	 =>  $this->amountPayable(),
		];

		$this->order->setPayment($payment_method, $payment_details);

		return $this;
	}


	public function initializePayment()
	{
		$actions = [
			'one_time' => 'makeOneTimePayment',
			'subscription' => 'makeSubscriptionPayment',
		];

		$method = $actions[$this->payment_type];

		return $this->$method();
	}

	public function attemptPayment()
	{


		if ($this->order->is_paid()) {
			throw new Exception("This Order has been paid with {$this->order->payment_method}", 1);
		}


		/* 
		if ($this->order->getPaymentDetails()['payment_method'] != $this->name) {
			throw new Exception("This Order is not set to use {$this->name} payment method", 1);
		}
 */

		if ($this->order->getPaymentDetails() == null) {
			throw new Exception("This Order is not ready to use {$this->name} payment method", 1);
		}

		$payment_details = ($this->order->getPaymentDetails());

		return $payment_details;
	}
}
