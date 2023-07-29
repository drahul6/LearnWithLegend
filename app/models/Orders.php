<?php



use v2\Jobs\Job;
use v2\Shop\Shop;
use v2\Models\Customer;
use v2\Traits\HasStatus;
use  Filters\QueryFilter;
use v2\Filters\Traits\Filterable;
use v2\Models\Wallet\ChartOfAccount;
use v2\Shop\Contracts\OrderInterface;
use v2\Jobs\Jobs\SendEmailForPaidOrder;
use Illuminate\Database\Eloquent\Builder;
use v2\Models\Wallet\Classes\AccountManager;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;
use v2\Models\Wallet\Journals;

class Orders extends Eloquent implements OrderInterface
{
	use Filterable;
	use HasStatus;

	protected $fillable = [
		'user_id',
		'identifier',
		'amount_payable',
		'payment_method',
		'payment_details',
		'payment_proof',
		'buyer_order',
		'status',
		'customer_id',
		'round',
		'paid_at',
		'settled_at',
		'sellers_ids',

	];

	protected $table = 'orders';

	public $name_in_shop = 'product';




	//during purchase
	public static $available_payment_methods =  [
		'bank_transfer' => [
			'method' => 'Bank Transfer',
			'word' => 'Pay Now (Bank Transfer)',
		],
		'rave' => [
			'method' => 'Rave ',
			'word' => 'Pay Now (Rave)',
		]
	];

	//order stages, 1-initialized, 2-paid, 3-completed, 4-settled 0-cancelled
	public static $statuses_config = [
		'use' => 'hierarchy',  //can be name or hierachy e.g draft or 1
		'column' => 'status',
		'push_url' => 'accounts/push_gen',  //ulr to update changes
		'use_hierarchy' => false,
		'states' => [
			[
				'name' => 'initialized', //name of status e.g completed
				'hierarchy' => 1, //the hierachy  int e.g 1
				'color' => 'secondary',    //the color e.g warning
				'after_set' => null, // a function that will be called after setting this status
				'before_set' => null, // a function that will be called before setting this status
				'is_final' => false, // this status cannot be reversed
			],

			[
				'name' => 'processing',
				'hierarchy' => 2,
				'color' => 'warning',
				'after_set' => null,
				'before_set' => null,
				'is_final' => false,
			],
			[
				'name' => 'delivered',
				'hierarchy' => 3,
				'color' => 'info',
				'after_set' => null,
				'before_set' => null,
				'is_final' => false,
			],

			[
				'name' => 'disputed',
				'hierarchy' => 4,
				'color' => 'info',
				'after_set' => null,
				'before_set' => null,
				'is_final' => false,
			],

			[
				'name' => 'settled',
				'hierarchy' => 5,
				'color' => 'success',
				'after_set' => null,
				'before_set' => null,
				'is_final' => true,
			],
			[
				'name' => 'cancelled',
				'hierarchy' => 0,
				'color' => 'danger',
				'after_set' => null,
				'before_set' => null,
				'is_final' => false,
			],

		],
	];



	public function getBookIdAttribute()
	{
		$id = "{$this->id}-{$this->user_id}-{$this->sellers_ids}-{$this->identifier}";
		return $id;
	}


	function createSupportTicket()
	{
		$buyer = $this->Buyer;
		$seller = $this->seller;


		$payment_details = json_decode($this->payment_details, true);
		$ref = $payment_details['ref'];

		$support_ticket = SupportTicket::updateOrCreate(
			[
				"code" => $ref
			],
			[
				'subject_of_ticket' => "New Order #$this->id, $this->TransactionID",
				'user_id' => "$buyer->id, $seller->id",
				"code" => $ref,
				"status" => 1,
			]
		);

		return $support_ticket;
	}

	public function getDownloadLinkAttribute()
	{
		$domain = Config::domain();
		$link = "$domain/user/download_request/$this->id";

		return $link;
	}

	public function getAdminDownloadLinkAttribute()
	{
		$domain = Config::domain();
		$link = "$domain/admin/download_request/$this->id";

		return $link;
	}





	public function getUserMarkAsReceivedLinkAttribute()
	{
		$domain = Config::domain();
		$items = $this->order_detail();
		$item_id = $items[0]['id'];

		$link = "$domain/user/mark_order_as_received/{$this->id}";

		return $link;
	}


	public function getAdminMarkAsReceivedLinkAttribute()
	{
		$domain = Config::domain();
		$link = "$domain/admin/mark_order_as_received/{$this->id}";

		return $link;
	}


	public function getSupportLinkAttribute($user = null)
	{
		$user = $user ?? 'user';

		$domain = Config::domain();
		$link = "$domain/$user/order_support?id=$this->id";
		return $link;
	}



	public function getUserMarkAsDeliveredLinkAttribute()
	{
		$domain = Config::domain();
		$link = "$domain/user/mark_order_as_delivered/$this->id";

		return $link;
	}


	public function getUserDisputeLinkAttribute()
	{
		$domain = Config::domain();
		$link = "$domain/user/mark_order_as_disputed/$this->id";

		return $link;
	}



	public function getAdminMarkAsDeliveredLinkAttribute()
	{
		$domain = Config::domain();
		$link = "$domain/admin/mark_order_as_delivered/$this->id";

		return $link;
	}


	public function getAdminDisputeLinkAttribute()
	{
		$domain = Config::domain();
		$link = "$domain/admin/mark_order_as_disputed/$this->id";

		return $link;
	}


	public function getShippingDetailAttribute()
	{
		$detail =  json_decode($this->buyer_order, true);
		return $detail['$shipping_details'];
	}

	public function settle()
	{

		//confirm there was escrow for this order
		$escrow_identifier = "ecommerce_order#$this->BookId";
		$settlement_identifier = "ecommerce_settlement#$this->id";
		$escrow = Journals::where('identifier', $escrow_identifier)->where('status', 3)->first();
		$settlement = Journals::where('identifier', $settlement_identifier)->where('status', 3)->first();



		if ((!$this->is_paid()) || ($escrow == null) || ($settlement != null)) {
			return;
		}

		//settle debit ecommerce, credit seller, credit pools commission
		$ecommerce_account = ChartOfAccount::find(AccountManager::journal_second_legs('ecommerce_escrow'));


		$cost = $escrow->c_amount;

		//debit ecommerce
		$involved_accounts[] = [
			"journal_id" => "",
			"chart_of_account_id" => $ecommerce_account->id,
			"chart_of_account_number" => $ecommerce_account->account_number,
			"description" => "settlement for order #{$this->id}",
			"credit" => 0,
			"debit" => round($cost, 2),
		];

		$item = $this->order_detail()[0];
		$seller = User::find($item['user_id']);
		$seller_account =  $seller->getAccount('default');



		$ecom_settings = SiteSettings::ecommerceSettings();
		$min_pools_commission =  $ecom_settings['min_pools_commission'];
		$pools_commission = max($item['data']['pools_commission'] ?? 0, $min_pools_commission);


		$amount_to_pools = $pools_commission * 0.01 * $cost;
		$amount_to_seller = $cost - $amount_to_pools;



		//credit seller account
		$involved_accounts[] = [
			"journal_id" => "",
			"chart_of_account_id" => $seller_account->id,
			"chart_of_account_number" => $seller_account->account_number,
			"description" => "sale {$item['market_details']['name']}#{$item['id']} on order #{$this->id}",
			"credit" => round($amount_to_seller, 2),
			"debit" => 0,
			"details" => [
				"product_id" => $item['id'],
			],

		];

		$pools_account = ChartOfAccount::find(AccountManager::journal_second_legs('monthly_pools_prize_account'));


		//credit pools_commission account
		$involved_accounts[] = [
			"journal_id" => "",
			"chart_of_account_id" => $pools_account->id,
			"chart_of_account_number" => $pools_account->account_number,
			"description" => "pools comm for {$item['market_details']['name']}#{$item['id']} on order #{$this->id}",
			"credit" => round($amount_to_pools, 2),
			"debit" => 0,
			"details" => [
				"product_id" => $item['id'],
			],

		];

		$currency = Config::currency();

		$today = date("Y-m-d");

		$journal = [
			"company_id" => 1,
			"notes" => "ecommerce settlement #{$this->id}",
			"currency" => "USD",
			"c_amount" => $cost,
			"amount" => $cost,
			"status" => 3,
			"journal_date" => $today,
			"tag" => "ecommerce_order",
			"identifier" => "ecommerce_settlement#{$this->id}",
			"user_id" => $this->user_id,
			"details" => [
				"order_id" => $this->id,
			],
			"involved_accounts" => $involved_accounts,
		];

		$total_cost = collect($involved_accounts)->sum('debit');

		if (!$ecommerce_account->hasSufficientBalanceFor($total_cost, $currency)) {
			Session::putFlash("danger", "insufficient Funds");
			// throw new Exception("Insufficient Balance", 1);
			return false;
		}


		$journal_model = AccountManager::postJournal($journal);

		return $journal_model;
	}




	public function mark_order_as_delivered()
	{
		return $this->markAs('delivered');
	}


	public function mark_order_as_disputed()
	{
		return $this->markAs('disputed');
	}



	public function mark_order_as_received()
	{
		//mark as received

		DB::beginTransaction();

		try {
			$settlement = $this->settle();
			if ($settlement == null) {
				throw new Exception("Error Processing Request", 1);
			}

			$this->markAs('settled');
			DB::commit();

			return true;
		} catch (\Throwable $th) {
			DB::rollback();
		}

		return false;
	}

	public static function generateIdentifier($array)
	{
		$json_lean_lines = json_encode($array);
		$crc32 = sprintf('%u', crc32("$json_lean_lines"));

		return $crc32;
	}


	public function delivery_details()
	{

		return "";
		$this->payment = $this->payment;
		$this->payment_details = $this->paymentDetailArray;

		$controller = new controller;

		$details = $this->order_detail();
		$linked_detail = array_map(function ($detail) use ($controller) {

			$ad = new stdClass;

			$round = $detail['round']['round'];

			$responsive = true;
			$is_delivery = true;

			if ($ad != null) {

				$game = [];
				$detail['round'] = $game;
				// $ad_detail = $ad->getDetails;
				$ad->detail = $ad->getDetails;
			} else {
				return;
			}

			$detail['view'] = $controller->buildView('composed/ad', compact('ad', 'responsive', 'is_delivery', 'round'), true, true);
			return $detail;
		}, $details);


		return $linked_detail;
	}



	public function after_payment_url($admin = false)
	{
		$domain = Config::domain();

		$id = MIS::dec_enc('encrypt', $this->id);

		$url = "$domain/user/order/$id";
		return $url;
	}

	public function payment_links()
	{

		$domain = Config::domain();
		$link = '';
		foreach (self::$available_payment_methods as $key => $method) {

			$checkout_param = http_build_query([
				'item_purchased' => $this->name_in_shop,
				'order_unique_id' => $this->id,
				'payment_method' =>  $key,
			]);

			$link .= "<li><a href='$domain/shop/checkout?$checkout_param' class='dropdown-item'> $method[word] </a></li>";
		}

		return $link;
	}


	public function invoice()
	{

		$summary = [];


		foreach ($this->order_detail() as $key => $line) {


			$rate = $line['round']['cost'];
			$amount = $line['qty'] *  $rate;
			$tax = $line['market_details']['tax'] ?? 0;

			$unit_tax = $tax['breakdown']['tax_payable'] ?? 0;
			$line_tax = $unit_tax * $line['qty'];
			$print_tax = "$line_tax 
			<br><small> {$tax['breakdown']['total_percent_tax']}% {$tax['pricing']} </small>";

			// $before_tax = $tax['breakdown']['before_tax'] * $line['qty'];
			$before_tax = $rate * $line['qty'];

			$summary[] = [

				'item' => $line['paper']['name'],
				'description' => $line['round']['intro'],
				'rate' => $rate,
				'print_tax' => $print_tax,
				'line_tax' => $line_tax,
				'before_tax' => $before_tax,
				'tax' => $tax,
				'qty' => $line['qty'],
				'amount' => $amount,
			];
		}



		$subtotal = collect($summary)->sum('amount');
		$total_tax = collect($summary)->sum('line_tax');

		$total_before_tax = collect($summary)->sum('before_tax');
		$total_after_tax = $subtotal;


		$lines =  [
			'subtotal' => [
				'name' => 'Sub Total Before Tax',
				'value' => $total_before_tax,
			],
			'tax' => [
				'name' => 'Tax',
				'value' => $total_tax,
			],
			'grand_total' => [
				'name' => 'Grand Total',
				'value' => $subtotal,
			],

			'total_payable' => [
				'name' => 'Total Payable',
				'value' => $subtotal,
			],
		];

		$extra_lines = [

			'total_before_tax' => [
				'name' => 'Sub Total Before Tax',
				'value' => $total_before_tax,
			],

			'total_after_tax' => [
				'name' => 'Sub Total Before Tax',
				'value' => $total_after_tax,
			],
		];

		$full_lines = array_merge($lines, $extra_lines);

		$subtotal = [
			'subtotal' => null,
			'lines' => $lines,
			// 'lines' => $this->PaymentBreakdownArray,
			'total' => null,
			'full_lines' => $full_lines,
		];


		$invoice = [
			'order_id' => $this->TransactionID,
			'invoice_id' => $this->TransactionID,
			'order_date' => $this->created_at,
			'payment_status' => $this->payment,
			'summary' => $summary,
			'subtotal' => $subtotal,
		];

		return $invoice;
	}


	public  function getInvoice()
	{

		$controller = new \controller;
		$order = $this;
		$view  =	$controller->buildView('composed/invoice', compact('order'), true);

		$mpdf = new \Mpdf\Mpdf([
			'margin_left' => 5,
			'margin_right' => 5,
			'margin_top' => 10,
			'margin_bottom' => 20,
			'margin_header' => 10,
			'margin_footer' => 10
		]);

		$src = Config::logo();

		$company_name = \Config::project_name();
		$logo = \Config::domain() . "/" . \Config::logo();
		$mpdf->AddPage('P');
		$mpdf->SetProtection(array('print'));
		$mpdf->SetTitle("{$company_name}");
		$mpdf->SetAuthor($company_name);
		// $mpdf->SetWatermarkText("{$company_name}");
		$mpdf->watermarkImg($src);
		$mpdf->showWatermarkText = true;
		$mpdf->watermark_font = 'DejaVuSansCondensed';
		$mpdf->watermarkTextAlpha = 0.1;
		$mpdf->SetDisplayMode('fullpage');

		$date_now = (date('Y-m-d H:i:s'));

		$mpdf->SetFooter("Date Generated: " . $date_now . " - {PAGENO} of {nbpg}");


		$mpdf->WriteHTML($view);
		$mpdf->Output("invoice#$order->id.pdf", \Mpdf\Output\Destination::INLINE);
	}




	public function getcreatedAttribute()
	{
		$date = date("M j, Y", strtotime($this->created_at));
		return "<span class='badge  badge-success'>$date</span><br>" . $this->ApprovedStatus;
	}



	public static function open($row_ids, $primary_key)
	{
		$orders = self::whereIn($primary_key, $row_ids)->get();
		$control = new controller;
		$view = $control->buildView('composed/order_detail', compact('orders'), true);

		return compact('view');
	}




	public function upload_payment_proof($file)
	{

		$directory 	= 'uploads/images/payment_proof';
		$handle  	= new Upload($file);

		if (explode('/', $handle->file_src_mime)[0] == 'image') {

			$handle->Process($directory);
			$original_file  = $directory . '/' . $handle->file_dst_name;

			(new Upload($this->payment_proof))->clean();
			$this->update(['payment_proof' => $original_file]);
		}
	}




	public function getpaymentDetailArrayAttribute()
	{
		return  json_decode($this->payment_details, true);
	}

	public static function user_orders($user_id)
	{ //return only paid for now

		$orders = self::Paid()->where('user_id', $user_id)->latest();

		return $orders;
	}



	public function scopePaid($query)
	{
		return $query->where('paid_at', '!=', null);
	}

	public function scopeUnPaid($query)
	{
		return $query->where('paid_at', '=', null);
	}


	public function generateOrderID()
	{

		$substr = substr(strval(time()), 7);
		$order_id = "LWL{$this->id}G{$substr}";

		return $order_id;
	}


	public function getPaymentDetails()
	{
		return $this->paymentDetailArray;
	}


	public function setPayment($payment_method, array $payment_details)
	{

		// DB::beginTransaction();

		$this->update([
			'payment_method' => $payment_method,
			'payment_details' => json_encode($payment_details),
		]);

		return $this;
	}

	public function customer()
	{
		return $this->belongsTo(Customer::class, 'customer_id');
	}



	public function is_free()
	{
		return $this->total_price() == 0;
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

	public function getPaymentBreakdownArrayAttribute()
	{
		return json_decode($this->payment_breakdown, true);
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




	public function create_order($cart)
	{

		DB::beginTransaction();
		$controller = new controller;
		$game_date = date("Y-m-d");

		try {

			$total = $cart['$total'];
			$amount_payable = $total;


			if ($amount_payable == 0) {
				throw new Exception("Error Processing Request", 1);
			}


			$new_order = Orders::updateOrCreate(
				['id' => $_SESSION['shop_checkout_id'] ?? null],
				[
					// 'user_id'		 => $controller->auth()->id,
					'buyer_order'	 => json_encode($cart['$items']),
					'amount_payable' => $amount_payable,
					'game_date' => $game_date,
					'customer_id' => $game_date,
				]
			);


			DB::commit();
			Session::putFlash('success', "Order Created Successfully. ");
			$_SESSION['shop_checkout_id'] = $new_order->id;
			// $this->empty_cart_in_session();

			return  $new_order;
		} catch (Exception $e) {

			DB::rollback();
			Session::putFlash('danger', "We could not create your order.");
			// Redirect::back();
		}
	}


	public function getBuyerAttribute()
	{
		if ($this->user_id != null) {

			return $this->user;
		}

		return $this->customer;
	}


	public function getPaidStatusAttribute()
	{
		if ($this->paid_at) {

			return '<span class="badge badge-sm badge-success">Paid</span>';
		}

		return '<span class="badge badge-sm badge-danger">Unpaid</span>';
	}


	public function getpaymentstatusAttribute()
	{
		return $this->PaidStatus;
	}


	public function getTransactionIDAttribute()
	{
		$payment_details = json_decode($this->payment_details, true);
		$ref = $payment_details['ref'];
		$gateway = $payment_details['gateway'];

		$currency = $payment_details['currency'] ?? Config::currency();
		$amount = $this->amount_payable;
		$method = "{$ref}-{$amount}";

		//<br><span class='badge badge-primary'>{$gateway}-{$currency}</span>
		return $method;
	}

	public function getValuePaidAttribute()
	{
		return "$this->PaymentCurrency$this->AmountPaid";
	}

	public function getAmountPaidAttribute()
	{
		$payment_details = ($this->payment_details);
		$method = $payment_details['amount'];
		return $method;
	}
	public function getPaymentCurrencyAttribute()
	{
		$payment_details = ($this->payment_details);
		$method = $payment_details['currency'];
		return $method;
	}


	public function getOrderIdAttribute()
	{
		$payment_details = json_decode($this->payment_details,  true);
		$method = "{$payment_details['ref']}";

		return $method;
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


	public function download()
	{
		$detail = $this->order_detail();

		$detail_obj = collect($detail)->groupBy('scheme');


		$files = [];

		foreach ($detail as $key => $item) {

			$license_keys = LicenseKey::where('order_id', $this->id)
				->where('purchased_product_id', $item['id'])
				->get()->pluck('license_key');

			$detail[$key]['license'] = $license_keys->toArray();
		}

		$mpdf = new \Mpdf\Mpdf([
			'margin_left' => 15,
			'margin_right' => 15,
			'margin_top' => 10,
			'margin_bottom' => 20,
			'margin_header' => 10,
			'margin_footer' => 10
		]);


		$company_name = Config::project_name();

		$mpdf->SetProtection(array('print'));
		$mpdf->SetTitle("{$company_name}");
		$mpdf->SetAuthor($company_name);
		$mpdf->SetWatermarkText("{$company_name}");
		$mpdf->showWatermarkText = true;
		$mpdf->watermark_font = 'DejaVuSansCondensed';
		$mpdf->watermarkTextAlpha = 0.1;
		$mpdf->SetDisplayMode('fullpage');

		$date_now = date('Y-m-d H:i:s');

		$mpdf->SetFooter("Date Generated: " . $date_now . " - {PAGENO} of {nbpg}");


		$user = $this->user;

		foreach ($detail as $key => $item) {

			$text = "<p style='font-size:20px; text-decoration:underline;'>{$item['name']} License Key(s)</p>";
			foreach ($item['license'] as $i => $license_key) {
				$i++;
				$text .= "$i) $license_key <br>";
			}

			$instruction = Products::find($item['id'])->instruction;

			$instruction = str_replace("[FIRSTNAME]", "<b>$user->firstname</b>", $instruction);
			$instruction = str_replace("[LASTNAME]", "<b>$user->lastname</b>", $instruction);
			$instruction = str_replace("[FULLNAME]", "<b>$user->fullname</b>", $instruction);
			$instruction = str_replace("[USERNAME]", "<b>$user->username</b>", $instruction);


			$mpdf->AddPage();
			$mpdf->WriteFixedPosHTML($instruction, 30, 30, 150, 250, 'auto');
			$mpdf->AddPage();
			$mpdf->WriteFixedPosHTML($text, 30, 30, 150, 250, 'auto');
		}

		$file_name = "{$this->user->firstname}-order{$this->id}.pdf";
		$mpdf->Output("$file_name", \Mpdf\Output\Destination::DOWNLOAD);
	}


	public function user()
	{

		return $this->belongsTo('User', 'user_id');
	}



	public static function mark_items_paid($row_ids, $primary_key)
	{

		$query = self::whereIn($primary_key, $row_ids)->UnPaid();
		$count = $query->count();
		if ($count < 1) {
			return;
		}

		DB::beginTransaction();

		try {

			$query->update(['paid_at' => date("Y-m-d H:i:s")]);
			DB::commit();
			Session::putFlash("success", "{$count} row(s) marked paid");
		} catch (Exception $e) {
			DB::rollback();
			Session::putFlash("danger", "Failed");
		}

		$orders = self::whereIn($primary_key, $row_ids)->get()->keyBy($primary_key);

		return compact('orders');
	}

	public function mark_paid()
	{

		if ($this->is_paid()) {
			return;
		}

		DB::beginTransaction();
		try {

			$this->update([
				'paid_at' => date("Y-m-d H:i:s"),
			]);


			$email_job = (new SendEmailForPaidOrder)->setUpWith($this);
			Job::schedule($email_job);

			DB::commit();
			Shop::empty_cart_in_session();
			Session::putFlash("success", "Order initiated successfully");

			return true;
		} catch (Exception $e) {
			DB::rollback();
			return false;
		}
	}

	public function mark_as_settled()
	{
		$this->markAs('settled');
	}



	private function give_upline_sale_commission()
	{
		return;
	}

	public function getAmountPayableAttribute($value)
	{
		return $value;
		return $amount_payable;
	}




	public function getpaymentAttribute()
	{
		if ($this->paid_at) {

			return '<span class="badge badge-success">Paid</span>';
		}

		return '<span class="badge badge-danger">Unpaid</span>';
	}



	public function getdateAttribute()
	{
		return date("M d, Y", strtotime($this->created_at));
	}



	public function seller()
	{
		return $this->belongsTo(User::class, 'sellers_ids');
	}




	public function has_item($item_id)
	{

		foreach ($this->order_detail() as $key => $item) {
			if ($item['id'] ==  $item_id) {
				return true;
			}
		}
		return false;
	}

	public function scopeSoldBy($query, $editor_id)
	{

		$identifier1 = <<<ELL
"editor_id":$editor_id

ELL;

		$identifier1 = trim($identifier1);

		$identifier2 = <<<ELL
"editor_id":"$editor_id"

ELL;

		$identifier2 = trim($identifier2);


		$query->whereRaw(
			"(buyer_order like ? 
										OR buyer_order like ? )",

			array(
				"%$identifier1%",
				"%$identifier2%"
			)
		);

		return $query;
	}



	public function items_sold_by_editor($editor_id)
	{
		$order_detail = $this->order_detail();


		$order_details = array_filter($order_detail, function ($item) use ($editor_id) {

			if ($item['editor_id'] == $editor_id) {
				return true;
			}
		});

		$count = count($order_details);

		foreach ($order_details as $order) {
			$total_price[] = $order['price'] * $order['qty'];
		}

		$total =  array_sum($total_price);


		$response = compact('order_details', 'count', 'total');

		return $response;
	}


	public function getSecsToExpiryAttribute()
	{
		$secs = strtotime($this->DueDate) - time();
		return max(0, $secs);
	}

	public function getDueDateAttribute()
	{
		$execution_period = SiteSettings::ecommerceSettings()['max_order_execution_period'];
		$due_date = date("M j, Y", strtotime("{$this->paid_at} +$execution_period"));

		return $due_date;
	}

	public function isPhysical()
	{
		$items = $this->order_detail();
		$item = $items[0];

		return $item['type_of_product'] == 'physical';
	}

	public function order_detail()
	{
		$detail = 	json_decode($this->buyer_order, true);


		return $detail['$items'] ?? $detail;
	}

	public function total_item()
	{

		$orders =  $this->order_detail();


		return count($orders);
	}

	public function total_qty()
	{

		$orders =  $this->order_detail();
		foreach ($orders as $order) {

			$total_qty[] = $order['qty'];
		}

		return array_sum($total_qty);
	}



	public function total_price()
	{

		$orders =  $this->order_detail();
		foreach ($orders as $order) {

			$total_price[] = $order['price'] * $order['qty'];
		}

		$total =  array_sum($total_price);

		return $total;
	}


	public function is_paid()
	{

		return (bool) ($this->paid_at != null);
	}


	public function delete_order(array $ids)
	{
		foreach ($ids as $key => $id) {
			$order = self::find($id);
			if ($order != null) {

				try {
					$order->delete();
				} catch (Exeception $e) {
				}
			}
		}
		return true;
	}
}
