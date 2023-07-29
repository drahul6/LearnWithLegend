<?php

namespace v2\Models\Wallet;


use MIS;
use User;
use Company;
use Session;
use Exception;
use SiteSettings;
use v2\Classes\ExchangeRate;
use v2\Models\Wallet\Journals;
use v2\Filters\Traits\Filterable;
use v2\Filters\Filters\JournalsFilter;
use v2\Models\Wallet\BasicAccountType;
use v2\Models\Wallet\CompanyAccountType;
use v2\Filters\Filters\ChartOfAccountFilter;
use v2\Models\Wallet\JournalInvolvedAccounts;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;
use v2\Filters\Filters\JournalInvolvedAccountsFilter;

class ChartOfAccount extends Eloquent
{

	use Filterable;


	protected $fillable = [
		'company_customised_account_id',
		'owner_id',
		'user_id',
		'company_id',
		'opening_balance',
		'current_balance',
		'available_balance',
		'currency',
		'a_opening_balance',
		'a_current_balance',
		'a_available_balance',
		'account_name',
		'account_code',
		'account_number',
		'tag',
		'details',
		'description',
	];

	protected $table = 'ac_charts_of_accounts';
	protected $connection = 'wallet';

	/* tags 
	withdrawal
	deposit
	membership
	commission
	
	*/
	/**
	 * This is the home(base) currency for accounting purpose
	 *
	 * @var string
	 */
	public static $base_currency = 'USD';

	/**
	 * Enforce that only available balance can leave
	 * an account
	 * 
	 * @var boolean
	 */
	public static $enforce_balance_suffiency = false;

	/**
	 * Configuration to use InvokeQuery from Fileterable
	 *
	 * @var array
	 */
	public static $query_config = [
		'filter_class' => ChartOfAccountFilter::class, //the filter class 
		'pass_mark' => 1,
		'name' => 'bank_accounts', //variable name for this
	];


	/**
	 * This contains validations rules.
	 * it will be used with the Validator classs.
	 * keys are names of form inputs POSTED while values are arrays 
	 * containing rules]
	 * @var array
	 */
	public static $validator_rules = [
		'opening_balance' => [
			'numeric' =>  true,
		],

		'account_type' => [
			'required' =>  true,
		],

		'username' => [
			'exist' => User::class . "|username",
		],

		'account_name' => [
			'required' =>  true,
		],
	];





	/**
	 * Get the Company 
	 *
	 * @return 
	 */
	public static function getCompany()
	{
		return Company::find(1);
	}

	public static function round($float)
	{
		return round($float, 2);
	}
	/**
	 * Get the Account Period
	 *
	 * @return void
	 */
	public static function getAccountPeriod()
	{
		$account_period = [
			'start_date' => '2022-05-01',
			'end_date' => date("Y-m-d"),
		];

		return $account_period;
	}

	/**
	 * Retrieve an account using its account number
	 *
	 * @param integer $account_number The account number of the account
	 * @return mixed
	 */
	public static function findNumber($account_number)
	{
		return self::where('account_number', $account_number)->first();
	}





	/**
	 * Get account balance at date
	 *
	 * @param string $date
	 * @return void
	 */
	public function get_balance($date = null)
	{
		$date = $date == null ? date("Y-m-d") : $date;

		$default_period = self::getAccountPeriod();
		$default_start_date = $default_period['start_date'];

		$sieve = [
			'journal_date' => [
				'start_date' => $default_start_date,
				'end_date' => $date,
			],
			'status' => '2,3,4'
			// 'balance_mode'=> 'available_balance',
		];



		$last_post = $this->last_post($sieve);

		if ($last_post == null) {

			$balance_formatted = self::account_format($this->current_balance);
			$balance =  $this->current_balance;
			$available_balance =  $this->available_balance;
			$account = [
				"balance" => $this->a_current_balance,
				"available_balance" => $this->a_available_balance,
			];
		} else {

			$balance_formatted =  $last_post->formattedPostBalance;
			$balance =  $last_post->post_balance;
			$available_balance =  $last_post->post_available_balance;

			$account = [
				"balance" => $last_post->a_post_balance,
				"available_balance" => $last_post->a_post_available_balance,
			];
		}


		$response = compact('balance_formatted', 'balance', 'available_balance');

		$r2 = [
			"base" => array_merge(["currency" => $this::$base_currency], $response),
			"account_currency" => array_merge([
				"currency" => $this->currency,
				"currency_symbol" => $this->CurrencySymbol
			], $account)
		];


		$final = array_merge($response, $r2);
		return $final;
	}


	/**
	 * Get Trial Balance for a company as at date
	 *
	 * @param  $company
	 * @param string $as_of_date
	 * @return void
	 */
	public static function get_trial_balance($company, $as_of_date)
	{

		$charts_of_accounts  =  self::for_company($company->id);
		$basic_accounts      =    BasicAccountType::orderBy('name')->get();

		foreach ($basic_accounts as  $basic_account) {

			$trial_balance[$basic_account->id]['basic_account'] = $basic_account->toArray();
			$i = 0;
			foreach ($charts_of_accounts->get() as $chart_of_account) {
				$i++;
				if ($chart_of_account->custom_account_type->basic_account->id == $basic_account->id) {


					$balance = $chart_of_account->get_balance($as_of_date);

					if ($chart_of_account->is_credit_balance()) {
						$chart_of_account->credit_balance =  $balance['balance_formatted'];
						$chart_of_account->raw_credit_balance =  $balance['balance'];
					} else {

						$chart_of_account->debit_balance =  $balance['balance_formatted'];
						$chart_of_account->raw_debit_balance =  $balance['balance'];
					}

					$trial_balance[$basic_account->id]['accounts'][$chart_of_account->id] = $chart_of_account->toArray();
				}
			}
		}


		foreach ($trial_balance as $basic_account_type_id => $value) {
			if (@$value['accounts'] == null) {
				unset($trial_balance[$basic_account_type_id]);
			}
		}


		$sorted_into_subcategories = [];
		foreach ($trial_balance as $basic_account_id => $basic_accounts) {
			foreach ($basic_accounts['accounts'] as $key => $value) {
				if ($value['company_customised_account_id']) {
					$sorted_into_subcategories[$basic_account_id][$value['company_customised_account_id']][] = $value;
				}
			}
		}


		$response = compact('trial_balance', 'sorted_into_subcategories');
		return $response;
	}


	/**
	 * Set the Opening balance for this account
	 *
	 * @param float $amount
	 * @return void
	 */
	public function setOpeningBalance($amount)
	{
		//convert this to the home base currency
		$base_currency = self::$base_currency;
		$exchange = new ExchangeRate;
		$conversion = $exchange->setFrom($this->currency)
			->setTo($base_currency)
			->setAmount($amount)
			->getConversion();

		$destination = $conversion['destination_value'];

		return $this->update([
			'opening_balance' => round($destination, 2),
			'current_balance' => round($destination, 2),
			'available_balance' => round($destination, 2),
			'a_opening_balance' => round($amount, 2),
			'a_current_balance' => round($amount, 2),
			'a_available_balance' => round($amount, 2),
		]);
	}

	/**
	 * Get exchange from home base currency 
	 * to this account currency
	 *
	 * @param float $amount
	 * @return array
	 */
	public function getConversion($amount)
	{
		$base_currency = self::$base_currency;
		$exchange = new ExchangeRate;
		$conversion = $exchange->setFrom($base_currency)
			->setTo($this->currency)
			->setAmount($amount)
			->getConversion();
		return $conversion;
	}


	/**
	 * To override the orignal attribute set
	 * @param  [type] $value [description]
	 * @return string        [description]
	 */
	public function getCurrenAttribute($value)
	{
		return $this->attributes['currency'] = 'GBP';
	}

	/**
	 * Get the symbol of this account currency
	 *
	 * @return string
	 */
	public function getCurrencySymbolAttribute()
	{
		$currencies = [
			"USD" => [
				"symbol" => "&dollar;"
			],
		];

		$symbol = $currencies[$this->currency]['symbol'];
		return $symbol;
	}


	/**
	 * Get opening balance in this account currency
	 *
	 * @return void
	 */
	public function getAccountOpeningBalanceAttribute()
	{
		$conversion = $this->getConversion($this->opening_balance);
		$destination = $conversion['destination_value'];
		return $destination;
	}

	/**
	 * get current balance in this account currency
	 *
	 * @return void
	 */
	public function getAccountCurrentBalanceAttribute()
	{
		$conversion = $this->getConversion($this->current_balance);
		$destination = $conversion['destination_value'];
		return $destination;
	}

	/**
	 * Relates to the User who is the account Holder
	 *
	 * @return void
	 */
	public function owner()
	{
		return $this->belongsTo(User::class, 'owner_id');
	}

	/**
	 * This updates the opening balance 
	 * for this account 
	 *
	 * @param float $amount
	 * @return void
	 */
	public function subtract_opening_balance($amount)
	{

		try {

			$this->decrement('opening_balance', $amount);
			$this->decrement('current_balance', $amount);
			$transactions = $this->transactions();

			foreach ($transactions as  $transaction) {

				$transaction->decrement('prior_balance', $amount);
				$transaction->decrement('post_balance', $amount);
			}

			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * This adds to the opening balance of this account
	 *
	 * @param float $amount
	 * @return void
	 */
	public function add_to_opening_balance($amount)
	{
		try {

			$this->increment('opening_balance', $amount);
			$this->increment('current_balance', $amount);
			$transactions = $this->transactions();

			foreach ($transactions as  $transaction) {

				$transaction->increment('prior_balance', $amount);
				$transaction->increment('post_balance', $amount);
			}

			return true;
		} catch (Exception $e) {
			return false;
		}
	}


	/**
	 * Determine if journals can be posted 
	 * into this account or not
	 *
	 * @return boolean
	 */
	public function is_open()
	{
		return true;
	}


	/**
	 * Get displayable open status
	 *
	 * @return void
	 */
	public function getOpenOrCloseStatusAttribute()
	{
		if ($this->is_open()) {
			return  "<span class='badge badge-success' >Open</span>";
		} else {

			return  "<span class='badge badge-danger' >Close</span>";
		}
	}

	/**
	 * Get Export Link
	 *
	 * @return void
	 */
	public function getExportLinkAttribute()
	{
		$q = $_SERVER['QUERY_STRING'];
		parse_str($q, $q_array);
		unset($q_array['url']);
		$q_string = http_build_query($q_array);

		$domain = \Config::domain();
		$url = "$domain/accounts/export_transaction_to_pdf/$this->id?$q_string";
		return $url;
	}




	/**
	 * Determine if available balance
	 * is sufficient for the amount
	 *
	 * @param float $amount, must 
	 * @return boolean
	 */
	public function hasSufficientBalanceFor($amount, $currency = null)
	{
		$currency = $currency ?? self::$base_currency;

		//take currency to base amount
		$exchange = new ExchangeRate;
		$conversion = $exchange->setFrom($currency)
			->setTo(self::$base_currency)
			->setAmount($amount)
			->getConversion();

		$base_amount = $conversion['destination_value'];


		$balance = $this->get_balance();

		$available_balance = $balance['base']['available_balance'];

		if ($base_amount > $available_balance) {
			return false;
		}

		return true;
	}




	function pendingTransaction($post_type, $amount, $a_amount)
	{

		switch ($post_type) {
			case 'credit':

				if ($this->is_credit_balance()) {

					$this->increment('current_balance', $amount);
					$this->increment('a_current_balance', $a_amount);

					/* $this->increment('available_balance', $amount);
					$this->increment('a_available_balance', $a_amount); */
				} else {

					/* 					
					$this->decrement('current_balance', $amount);
					$this->decrement('a_current_balance', $a_amount);
 					*/

					$this->decrement('available_balance', $amount);
					$this->decrement('a_available_balance', $a_amount);
				}

				break;

			case 'debit':


				if ($this->is_debit_balance()) {

					$this->increment('current_balance', $amount);
					$this->increment('a_current_balance', $a_amount);
					/* 
					$this->increment('available_balance', $amount);
					$this->increment('a_available_balance', $a_amount); */
				} else {

					/* $this->decrement('current_balance', $amount);
					$this->decrement('a_current_balance', $a_amount); */



					$this->decrement('available_balance', $amount);
					$this->decrement('a_available_balance', $a_amount);
				}


				break;

			default:
				# code...
				break;
		}
	}



	function declinePendingTransaction()
	{
	}


	function instantTransaction($post_type, $amount, $a_amount)
	{

		switch ($post_type) {
			case 'credit':

				if ($this->is_credit_balance()) {

					$this->increment('current_balance', $amount);
					$this->increment('a_current_balance', $a_amount);

					$this->increment('available_balance', $amount);
					$this->increment('a_available_balance', $a_amount);
				} else {


					$this->decrement('current_balance', $amount);
					$this->decrement('a_current_balance', $a_amount);


					$this->decrement('available_balance', $amount);
					$this->decrement('a_available_balance', $a_amount);
				}

				break;

			case 'debit':


				if ($this->is_debit_balance()) {

					$this->increment('current_balance', $amount);
					$this->increment('a_current_balance', $a_amount);


					$this->increment('available_balance', $amount);
					$this->increment('a_available_balance', $a_amount);
				} else {

					$this->decrement('current_balance', $amount);
					$this->decrement('a_current_balance', $a_amount);


					$this->decrement('available_balance', $amount);
					$this->decrement('a_available_balance', $a_amount);
				}


				break;

			default:
				# code...
				break;
		}
	}



	/**
	 * Post Journal 
	 *
	 * @param float $amount The Amount to be posted
	 * @param string $post_type  Whether 'credit' or 'debit'
	 
	 */
	public function post($amount, $post_type, $kind_of_post = 'instant')
	{
		$base_currency = self::$base_currency;
		$exchange = new ExchangeRate;
		$conversion = $exchange->setFrom($base_currency)
			->setTo($this->currency)
			->setAmount($amount)
			->getConversion();

		$a_amount = $conversion['destination_value'];

		DB::beginTransaction();


		try {


			$arrays = [
				"instant" => "instantTransaction",
				"pend" => "pendingTransaction",
				"complete_pending" => "completePendingTransaction",
				"decline_pending" => "declinePendingTransaction",
			];

			$method = $arrays[$kind_of_post];
			if (method_exists($this, $method)) {
				$this->$method($post_type, $amount, $a_amount);
			}


			DB::commit();
			return true;
		} catch (\Throwable $th) {
			DB::rollback();
			return false;
			//throw $th;
		}
	}

	/**
	 * Returns the accounting format for a real number
	 * 
	 * @param float $value  The real number to format
	 */
	public static function account_format($value)
	{
		if ($value < 0) {
			return  "(" . number_format(abs($value), 2) . ")";
		} else {

			return number_format($value, 2);
		}
	}

	/**
	 * Determine if account is a credit balance account
	 *
	 * @return boolean
	 */
	public function is_credit_balance()
	{
		return ($this->custom_account_type->basic_account->is_credit_balance());
	}

	/**
	 * Determine if account is a debit balance account
	 *
	 * @return boolean
	 */
	public function is_debit_balance()
	{
		return ($this->custom_account_type->basic_account->is_debit_balance());
	}

	/**
	 * Retrieve the last post into this account
	 *
	 * @param array $sieve
	 * @return mixed
	 */
	public function last_post($sieve = [])
	{
		$response = $this->transactions(2, 1, $sieve, [], 'DESC');

		$last_transaction = $response['last_transaction'];

		$response = $last_transaction->first() ?? null;
		return $response;
	}


	/**
	 * Get line items into this account
	 *
	 * @param array $journal_sieve  Filter for journal
	 * @param array $line_items_sieve Filter for involved accounts
	 * @return void
	 */
	public function getPosts($journal_sieve, $line_items_sieve)
	{
		return $this->transactions(100, 1, $journal_sieve, $line_items_sieve);
	}

	/**
	 * Fetch transactions (line items or involved accounts) 
	 * into this account
	 *
	 * @param integer $per_page
	 * @param integer $page
	 * @param array $sieve   Filter for journals
	 * @param array $line_items_sieve Filter for line items
	 * @return mixed
	 */
	public function transactions($per_page = 50, $page = 1, $sieve = [], $line_items_sieve = [], $order_by = "ASC")
	{

		$default_sieve = [
			"status" => '2,3,4'
		];

		// $sieve = array_merge($sieve, $default_sieve);
		$sieve = array_merge($default_sieve, $sieve);

		$page = $page ?? 1;
		$skip = (($page - 1) * $per_page);

		$filter = new JournalsFilter($sieve);

		$query = Journals::select('*', DB::raw("ac_account_journals.id as journal_id"))->CompanyJournals($this->company_id);
		$journals = $query->Filter($filter);


		$line_items_filter =  new JournalInvolvedAccountsFilter($line_items_sieve);
		$line_items = JournalInvolvedAccounts::where('chart_of_account_id', $this->id)->Filter($line_items_filter);
		$total = $line_items->count();


		$line_items->joinSub($journals, 'journals', function ($join) {
			$join->on('ac_involved_accounts.journal_id', '=', 'journals.id');
		})->orderBy('journals.id', $order_by);

		$data = $line_items->count(); //total in this filter
		$transactions = $line_items
			->offset($skip)
			->take($per_page)
			->get();  //filtered

		$query = $line_items->offset($skip)
			->take($per_page);


		$last_transaction = $line_items
			->offset($skip)
			->latest('ac_involved_accounts.id')
			// ->latest('ac_involved_accounts.id')
			->take($per_page)
			->get();  //filtered


		//prepare the journal date_range
		$account_period = self::getAccountPeriod();
		$start_date = $sieve['journal_date']['start_date'] ?? $account_period['start_date'];
		$end_date = $sieve['journal_date']['end_date'] ?? $account_period['end_date'];
		$journal_date = compact('start_date', 'end_date');
		$date_note = "$start_date to $end_date";


		$balance_bf = ($transactions->first()->formattedPriorBalance ?? 0);
		$available_balance_bf = ($transactions->first()->a_prior_available_balance ?? 0);
		$balance_bf_raw = $transactions->first()->prior_balance ?? 0;



		$note = MIS::filter_note($transactions->count(), $data, $total,  $sieve, 1);

		$total_credit = $transactions->sum('credit');
		$total_debit = $transactions->sum('debit');

		$closing_balance = $last_transaction->first()->post_balance ?? 0;

		$response = compact(
			'total_credit',
			'total_debit',
			'page',
			'per_page',
			'transactions',
			'query',
			'note',
			'sieve',
			'journal_date',
			'date_note',
			'data',
			'available_balance_bf',
			'balance_bf',
			'balance_bf_raw',
			'closing_balance',
			'last_transaction'
		);

		return get_defined_vars();
	}


	/**
	 * Scope accounts belonging to the supplied company
	 *
	 * @param  $query
	 * @param integer $company_id
	 * @return void
	 */
	public function scopefor_company($query, $company_id)
	{
		return $query->where('company_id', $company_id);
	}


	/**
	 * The result is used at chart of  account creation page
	 * to populate the account select options for  account type
	 * 
	 * @param integer  $company_id 
	 * @return mixed
	 */
	public static function charts_of_account_options_at_creation($company_id)
	{
		$charts = CompanyAccountType::for_company($company_id)
			->get()->groupBy('basic_account_id');

		foreach ($charts as $basic_account_type_id => $charts_of_accounts) {
			$basic_name = BasicAccountType::find($basic_account_type_id)->name;
			foreach ($charts_of_accounts as $key => $chart_of_account) {


				$refined_output[$basic_name][] = $chart_of_account->toArray();
			}
		}

		return $refined_output ?? [];
	}

	/**
	 * 
	 * The result is used at chart of  account settings page
	 * to populate the account select options for  accounts type
	 *
	 * @param [type] $company_id
	 * @param [type] $chart_of_account_id
	 * @return array
	 */
	public static function charts_of_account_options_at_edit($company_id, $chart_of_account_id)
	{

		$charts = CompanyAccountType::for_company($company_id)
			->get()->groupBy('basic_account_id');

		$chart_of_account_in_edit = ChartOfAccount::find($chart_of_account_id);


		foreach ($charts as $basic_account_type_id => $charts_of_accounts) {
			$basic_account = BasicAccountType::find($basic_account_type_id);
			$basic_name =  $basic_account->name;

			if (($basic_account->is_credit_balance()) !=
				($chart_of_account_in_edit->is_credit_balance())
			) {
				continue;
			} elseif (($basic_account->is_debit_balance()) !=
				($chart_of_account_in_edit->is_debit_balance())
			) {
				continue;
			}

			foreach ($charts_of_accounts as $key => $chart_of_account) {

				$refined_output[$basic_name][] = $chart_of_account->toArray();
			}
		}

		return ($refined_output);
	}

	/**
	 * The result is used at journal creation page
	 * to populate the account select options for involved accounts
	 *
	 * @param integer $company_id
	 * @return void
	 */
	public static function charts_of_account_options($company_id)
	{
		$charts = ChartOfAccount::for_company($company_id)->get();


		foreach ($charts as $key => $chart_of_account) {

			$basic_name = $chart_of_account->custom_account_type->basic_account->name;

			if ($chart_of_account->is_open()) {
				$refined_output[$basic_name][] = $chart_of_account->toArray();
			}
		}

		return ($refined_output);
	}

	/**
	 * Generate account code
	 *
	 * @param integer $company_id
	 * @param mixed $company_account_type The ID of the company account type account
	 * @return mixed
	 */
	public static function generate_account_code($company_id, $company_account_type)
	{
		$basic_account_ids = CompanyAccountType::where('company_id', $company_id)
			->where('basic_account_id', $company_account_type->basic_account_id)
			->get()
			->pluck('id')
			->toArray();

		$count = self::for_company($company_id)->whereIn('company_customised_account_id', $basic_account_ids)->count();
		$code = ($company_account_type->basic_account->id *  1000) + $count + 1;
		return $code;
	}


	/**
	 * Generate Account number
	 *
	 * @param integer $company_id
	 * @param mixed $company_account_type
	 * @return mixed
	 */
	public static function generate_account_number($company_id, $company_account_type)
	{
		$basic_account_ids = CompanyAccountType::where('company_id', $company_id)
			->where('basic_account_id', $company_account_type->id)
			->get()
			->pluck('id')
			->toArray();

		$count = self::for_company($company_id)->whereIn('company_customised_account_id', $basic_account_ids)->count();
		$code = ($company_account_type->basic_account->id *  1000) + $count + 1;
		$padding = random_int(10000, 99999);
		$number  = str_pad($code, '10', $padding);
		return $number;
	}


	/**
	 * Relates the company account tyoe
	 *
	 */
	public function custom_account_type()
	{
		return $this->belongsTo(CompanyAccountType::class, 'company_customised_account_id')->with(['basic_account']);
	}

	/**
	 * to be deleted
	 *
	 * @return void
	 */
	public function getaccounttypeAttribute()
	{
		return $this->custom_account_type->basic_account;
	}

	public function setAccountNameAttribute($value)
	{
		$this->attributes['account_name'] = strtolower($value);
	}
}
