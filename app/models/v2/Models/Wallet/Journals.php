<?php

namespace v2\Models\Wallet;

use Upload;
use Exception;
use User, Config;
use v2\Traits\OrderableJournal;
use v2\Traits\HasDetails;
// use v2\Jobs\Jobs\SendEmailForNewTransaction;
use v2\Classes\ExchangeRate;
use v2\Filters\Traits\Filterable;
use v2\Filters\Filters\JournalsFilter;
use v2\Models\Wallet\JournalInvolvedAccounts;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;
use v2\Shop\Contracts\OrderInterface;

class Journals extends Eloquent implements OrderInterface
{
	use Filterable;
	use HasDetails;
	use OrderableJournal;

	protected $fillable = [
		'user_id',
		'company_id',
		'amount',
		'notes',
		'tag',
		'identifier',
		'details',
		'c_amount', //
		'currency',
		'status',
		'journal_date',
		'attached_files',
		'created_at',
	];

	protected $table = 'ac_account_journals';
	protected $connection = 'wallet';

	public static $statuses = [
		1 => [
			'key' => 'draft',
			'class' => 'dark',
		],
		2 => [
			'key' => 'pending',
			'class' => 'warning',
		],
		3 => [
			'key' => 'completed',
			'class' => 'success',
		],
		4 => [
			'key' => 'completed.', //final
			'class' => 'success',
		],
	];


	public static $query_config = [
		'filter_class' => JournalsFilter::class, //the filter class 
		'pass_mark' => 1,
		'name' => 'journals', //variable name for this
	];



	/**
	 * This contains validations rules.
	 * it will be used with the Validator classs.
	 * keys are names of form inputs POSTED while values are arrays 
	 * containing rules]
	 * @var [array]
	 */
	public static $validator_rules = [
		'notes' => [
			'required' => true,
			'max' =>  500,
		],

		'journal_date' => [
			'required' =>  true,
		],
		'involved_accounts' => [
			// 'required' => true,
		],

	];


	public static function getCompany()
	{
		return ChartOfAccount::getCompany();
	}


	public function hasTag($tag)
	{
		return $this->tag == "$tag";
	}

	public function getwithdrawalDetailsAttribute()
	{
		if (!$this->hasTag("withdrawal")) {
			return null;
		}


		$array =  $this->DetailsArray;
		$method =  $this->DetailsArray['withdrawal_method'];


		$line = $method['method'];
		$method_details = $method['details'];

		foreach ($method_details as $label => $value) {
			$line .= "<li>
						$label:
						$value
					</li>";
		}

		return $line;
	}

	public function getpayablesDetailsAttribute()
	{
		if (!$this->hasTag("withdrawal")) {
			return null;
		}


		$detail =  $this->DetailsArray['payables'];

		return $detail;
	}




	/**
	 * This removes lines items 
	 * attached to this journal
	 *
	 * @return void
	 */
	public function remove_line_items()
	{
		foreach ($this->involved_accounts as $old_involved_account) {

			$old_involved_account->delete_involved_account();
		}
	}

	public function is_reversible()
	{
		//published
		//was never a pending
		//not final
		return in_array($this->status, [3]);
	}

	public function reverseJournal()
	{
		if (!$this->is_reversible()) {
			return;
		}

		// die;
		DB::connection('wallet')->beginTransaction();

		$company = self::getCompany();

		try {

			$journal = [
				'user_id' => null,
				'company_id' => $company->id,
				'amount' => $this->amount,
				'c_amount' => $this->c_amount,
				'notes' => "#{$this->id}_at_{$this->journal_date}_reversed",
				'tag' => $this->tag . "rvsl",
				'identifier' => "$this->id#reverse",
				'currency' => $this->currency,
				'status' => 4,
				'journal_date' => $this->journal_date,
			];


			$journal =  self::create($journal);

			$involved_accounts = [];
			foreach ($this->involved_accounts as $line) {

				$amount = max($line->credit, $line->debit);
				$a_amount = max($line->a_credit, $line->a_debit);

				if ($line->isType('credit')) {
					$credit = 0;
					$debit = $line->credit;

					$a_credit = 0;
					$a_debit = $line->a_credit;
				} else {


					$credit = $line->debit;
					$debit = 0;

					$a_credit = $line->a_debit;
					$a_debit = 0;
				}


				$involved_account_a = [
					'journal_id' => $journal->id,
					'chart_of_account_id' => $line->chart_of_account_id,
					'description' => "reverse#{$this->id}t{$this->created_at}v{$this->journal_date}",
					'credit' => $credit,
					'debit' => $debit,
					'a_credit' => $a_credit,
					'a_debit' => $a_debit,

					'prior_balance' => $line->chart_of_account->current_balance,
					'a_prior_balance' => $line->chart_of_account->a_current_balance,
					'prior_available_balance' => $line->chart_of_account->available_balance,
					'a_prior_available_balance' => $line->chart_of_account->a_available_balance,

				];




				if ($line->increasedBalance()) {

					$line->chart_of_account->decrement('current_balance', $amount);
					$line->chart_of_account->decrement('available_balance', $amount);


					$line->chart_of_account->decrement('a_current_balance', $a_amount);
					$line->chart_of_account->decrement('a_available_balance', $a_amount);
					// -book

				} else {

					$line->chart_of_account->increment('current_balance', $amount);
					$line->chart_of_account->increment('available_balance', $amount);


					$line->chart_of_account->increment('a_current_balance', $a_amount);
					$line->chart_of_account->increment('a_available_balance', $a_amount);
				}


				$involved_account_b = [
					'post_balance'  =>  $line->chart_of_account->current_balance,
					'post_available_balance'  => $line->chart_of_account->available_balance,
					'a_post_balance'  => $line->chart_of_account->a_current_balance,
					'a_post_available_balance'  => $line->chart_of_account->a_available_balance,
				];

				$involved_accounts[] = array_merge($involved_account_a, $involved_account_b);
			}

			foreach ($involved_accounts as  $involved_account) {
				JournalInvolvedAccounts::create_involved_account($involved_account, $journal);
			}


			$this->update([
				'status' => 4,
				'tag' => "{$this->tag}_reversed",
				'identifier' => "{$this->identifier}_reversed"
			]);

			DB::connection('wallet')->commit();
			$this->refresh();
			return $this;
		} catch (\Throwable $th) {
			//throw $th;
			print_r($th->getMessage());
			DB::connection('wallet')->rollback();

			return false;
		}
	}



	public function declinePending()
	{
		if (!$this->is_pending()) {
			return;
		}



		DB::connection('wallet')->beginTransaction();

		$company = self::getCompany();

		try {

			$journal = [
				'user_id' => null,
				'company_id' => $company->id,
				'amount' => $this->amount,
				'notes' => "#{$this->id}_declined due at {$this->journal_date} ",
				'tag' => $this->tag,
				'identifier' => "$this->id#decline",
				'currency' => null,
				'status' => 4,
				'journal_date' => $this->journal_date,
			];


			$journal =  self::create($journal);

			$involved_accounts = [];
			foreach ($this->involved_accounts as $line) {

				$involved_account_a = [
					'journal_id' => $journal->id,
					'chart_of_account_id' => $line->chart_of_account_id,
					'description' => "decline#{$this->id}t{$this->created_at}v{$this->journal_date}",
					'credit' => 0,
					'debit' => 0,


					'prior_balance' => $line->chart_of_account->current_balance,
					'a_prior_balance' => $line->chart_of_account->a_current_balance,
					'prior_available_balance' => $line->chart_of_account->available_balance,
					'a_prior_available_balance' => $line->chart_of_account->a_available_balance,
				];

				if ($line->increasedBalance()) {

					$amount = max($line->credit, $line->debit);
					$line->chart_of_account->decrement('current_balance', $amount);

					$a_amount = max($line->a_credit, $line->a_debit);
					$line->chart_of_account->decrement('a_current_balance', $a_amount);

					// -book

				} else {


					$amount = max($line->credit, $line->debit);
					$line->chart_of_account->increment('available_balance', $amount);

					$a_amount = max($line->a_credit, $line->a_debit);
					$line->chart_of_account->increment('a_available_balance', $a_amount);

					// +avail



				}


				$involved_account_b = [
					'post_balance'  =>  $line->chart_of_account->current_balance,
					'post_available_balance'  => $line->chart_of_account->available_balance,
					'a_post_balance'  => $line->chart_of_account->a_current_balance,
					'a_post_available_balance'  => $line->chart_of_account->a_available_balance,
				];

				$involved_accounts[] = array_merge($involved_account_a, $involved_account_b);
			}


			foreach ($involved_accounts as  $involved_account) {
				JournalInvolvedAccounts::create_involved_account($involved_account, $journal);
			}

			$this->update(['status' => 4]);
			DB::connection('wallet')->commit();

			$this->refresh();
			return $this;
		} catch (\Throwable $th) {
			//throw $th;
			DB::connection('wallet')->rollback();

			return false;
		}
	}

	public function completePending()
	{

		if (!$this->is_pending()) {
			return;
		}


		DB::connection('wallet')->beginTransaction();

		$company = self::getCompany();

		try {

			$journal = [
				'user_id' => null,
				'company_id' => $company->id,
				'amount' => $this->amount,
				'notes' => "#{$this->id}_completed due at {$this->journal_date} ",
				'tag' => $this->tag,
				'identifier' => "$this->id#complete",
				'currency' => null,
				'status' => 4,
				'journal_date' => $this->journal_date,
			];


			$journal =  self::create($journal);

			$involved_accounts = [];
			foreach ($this->involved_accounts as $line) {

				$involved_account_a = [
					'journal_id' => $journal->id,
					'chart_of_account_id' => $line->chart_of_account_id,
					'description' => "complete#{$this->id}t{$this->created_at}v{$this->journal_date}",
					'credit' => 0,
					'debit' => 0,


					'prior_balance' => $line->chart_of_account->current_balance,
					'a_prior_balance' => $line->chart_of_account->a_current_balance,
					'prior_available_balance' => $line->chart_of_account->available_balance,
					'a_prior_available_balance' => $line->chart_of_account->a_available_balance,
				];

				if ($line->increasedBalance()) {
					$amount = max($line->credit, $line->debit);
					$line->chart_of_account->increment('available_balance', $amount);

					$a_amount = max($line->a_credit, $line->a_debit);
					$line->chart_of_account->increment('a_available_balance', $a_amount);

					// +avail

				} else {
					$amount = max($line->credit, $line->debit);
					$line->chart_of_account->decrement('current_balance', $amount);

					$a_amount = max($line->a_credit, $line->a_debit);
					$line->chart_of_account->decrement('a_current_balance', $a_amount);

					// -book
				}


				$involved_account_b = [
					'post_balance'  =>  $line->chart_of_account->current_balance,
					'post_available_balance'  => $line->chart_of_account->available_balance,
					'a_post_balance'  => $line->chart_of_account->a_current_balance,
					'a_post_available_balance'  => $line->chart_of_account->a_available_balance,
				];

				$involved_accounts[] = array_merge($involved_account_a, $involved_account_b);
			}


			foreach ($involved_accounts as  $involved_account) {
				JournalInvolvedAccounts::create_involved_account($involved_account, $journal);
			}

			$this->update(['status' => 4]);
			DB::connection('wallet')->commit();

			$this->refresh();
			return $this;
		} catch (\Throwable $th) {
			//throw $th;
			DB::connection('wallet')->rollback();

			return false;
		}
	}




	public function pend()
	{
		$this->refresh();

		$currency =  $this->currency == '' ? ChartOfAccount::$base_currency :  $this->currency;


		DB::connection('wallet')->beginTransaction();

		try {
			$journal_currency_amount  =  $this->involved_accounts->sum('credit');
			$journal_lines = ['currency' => $currency];

			foreach ($this->involved_accounts as $involved_account) {

				$chart_of_account =  $involved_account->chart_of_account;
				$chart_of_account->current_balance;

				$involved_account->update([
					'prior_balance' => $chart_of_account->current_balance,
					'a_prior_balance' => $chart_of_account->a_current_balance,

					'prior_available_balance' => $chart_of_account->available_balance,
					'a_prior_available_balance' => $chart_of_account->a_available_balance,
				]);




				$base_currency = $chart_of_account::$base_currency;
				//first take journal currency to base currency		
				$exchange = new ExchangeRate;
				$credit_conversion = $exchange->setFrom($currency)
					->setTo($base_currency)
					->setAmount($involved_account->credit)
					->getConversion();

				$involved_account->credit = $credit_conversion['destination_value'];

				$exchange = new ExchangeRate;
				$debit_conversion = $exchange->setFrom($currency)
					->setTo($base_currency)
					->setAmount($involved_account->debit)
					->getConversion();

				$involved_account->debit = $debit_conversion['destination_value'];

				$journal_lines['lines'][] = [
					"credit" => $credit_conversion,
					"debit" => $debit_conversion,
					"chart_of_account" => $chart_of_account->id,
				];




				//get and store the equivalent in accounts own currency
				$base_currency = $chart_of_account::$base_currency;
				$exchange = new ExchangeRate;
				$conversion = $exchange->setFrom($base_currency)
					->setTo($chart_of_account->currency)
					->setAmount($involved_account->credit)
					->getConversion();

				$exchange_data = [];
				$exchange_data['credit'] = $conversion;

				$a_credit = $conversion['destination_value'];
				$conversion = $exchange->setFrom($base_currency)
					->setTo($chart_of_account->currency)
					->setAmount($involved_account->debit)
					->getConversion();
				$a_debit = $conversion['destination_value'];
				$exchange_data['debit'] = $conversion;

				$chart_of_account->post(floatval($involved_account->credit), 'credit', 'pend');
				$chart_of_account->post(floatval($involved_account->debit), 'debit', 'pend');

				$details = [];
				$details['exchange'] = $exchange_data;


				$involved_account->update([
					'post_balance' => $chart_of_account->current_balance,
					'a_post_balance' => $chart_of_account->a_current_balance,

					'post_available_balance' => $chart_of_account->available_balance,
					'a_post_available_balance' => $chart_of_account->a_available_balance,

					'a_credit' => $a_credit,
					'a_debit' => $a_debit,
					'details' => $details
				]);
			}



			$amount = $this->DeducedAmount;
			$this->update([
				'status' => 2,
				'amount' => $amount,
				'c_amount' => $journal_currency_amount,
				'details' => ["journal_lines" => $journal_lines]
			]);

			DB::connection('wallet')->commit();

			//send email to users
			/* 
			foreach ($this->involved_accounts as $transaction) {
				if ($transaction->chart_of_account->owner == null) {
					continue;
				}

				SendEmailForNewTransaction::dispatch(compact('transaction'));
			} */

			$this->refresh();
			return $this;
		} catch (\Exception $th) {
			DB::connection('wallet')->rollback();
			return false;
			//throw $th;
		}
	}




	/**
	 * This publishes the journal
	 * permanently into the ledger
	 *
	 * @return void
	 */
	public function publish()
	{

		$this->refresh();
		$currency =  $this->currency == '' ? ChartOfAccount::$base_currency :  $this->currency;

		DB::connection('wallet')->beginTransaction();


		try {

			$journal_currency_amount  =  $this->involved_accounts->sum('credit');
			$journal_lines = ['currency' => $currency];

			foreach ($this->involved_accounts as $involved_account) {

				$chart_of_account =  $involved_account->chart_of_account;
				$chart_of_account->current_balance;

				$involved_account->update([
					'prior_balance' => $chart_of_account->current_balance,
					'a_prior_balance' => $chart_of_account->a_current_balance,

					'prior_available_balance' => $chart_of_account->available_balance,
					'a_prior_available_balance' => $chart_of_account->a_available_balance,
				]);


				$base_currency = $chart_of_account::$base_currency;


				//first take journal currency to base currency		
				$exchange = new ExchangeRate;
				$credit_conversion = $exchange->setFrom($currency)
					->setTo($base_currency)
					->setAmount($involved_account->credit)
					->getConversion();

				$involved_account->credit = $credit_conversion['destination_value'];

				$exchange = new ExchangeRate;
				$debit_conversion = $exchange->setFrom($currency)
					->setTo($base_currency)
					->setAmount($involved_account->debit)
					->getConversion();

				$involved_account->debit = $debit_conversion['destination_value'];

				$journal_lines['lines'][] = [
					"credit" => $credit_conversion,
					"debit" => $debit_conversion,
					"chart_of_account" => $chart_of_account->id,
				];



				$exchange_data = [];
				//get and store the equivalent in accounts own currency
				$exchange = new ExchangeRate;
				$conversion = $exchange->setFrom($base_currency)
					->setTo($chart_of_account->currency)
					->setAmount($involved_account->credit)
					->getConversion();

				$exchange_data['credit'] = $conversion;
				$a_credit = $conversion['destination_value'];



				$conversion = $exchange->setFrom($base_currency)
					->setTo($chart_of_account->currency)
					->setAmount($involved_account->debit)
					->getConversion();
				$a_debit = $conversion['destination_value'];
				$exchange_data['debit'] = $conversion;

				$chart_of_account->post(floatval($involved_account->credit), 'credit');
				$chart_of_account->post(floatval($involved_account->debit), 'debit');

				$details = [];
				$details['exchange'] = $exchange_data;


				$involved_account->update([
					'post_balance' => $chart_of_account->current_balance,
					'a_post_balance' => $chart_of_account->a_current_balance,

					'post_available_balance' => $chart_of_account->available_balance,
					'a_post_available_balance' => $chart_of_account->a_available_balance,

					'a_credit' => $a_credit,
					'a_debit' => $a_debit,
					'details' => $details
				]);
			}


			$amount = $this->DeducedAmount;

			$this->update([
				'status' => 3,
				'amount' => $amount,
				'c_amount' => $journal_currency_amount,
			]);

			$this->updateDetailsByKey('journal_lines', $journal_lines);

			DB::connection('wallet')->commit();

			//send email to users
			/* 
			foreach ($this->involved_accounts as $transaction) {
				if ($transaction->chart_of_account->owner == null) {
					continue;
				}

				SendEmailForNewTransaction::dispatch(compact('transaction'));
			} */

			$this->refresh();
			return $this;
		} catch (\Exception $th) {
			DB::connection('wallet')->rollback();
			return false;
			//throw $th;
		}
	}


	/**
	 * save this journal as draft
	 *
	 * @return void
	 */
	public function draft()
	{
		DB::connection('wallet')->beginTransaction();

		try {

			foreach ($this->involved_accounts as $involved_account) {

				$chart_of_account =  $involved_account->chart_of_account;
				$chart_of_account->current_balance;

				$involved_account->update([
					'prior_balance' => $chart_of_account->current_balance,
					'a_prior_balance' => $chart_of_account->a_current_balance,
				]);

				$base_currency = $chart_of_account::$base_currency;
				$exchange = new ExchangeRate;
				$conversion = $exchange->setFrom($base_currency)
					->setTo($chart_of_account->currency)
					->setAmount($involved_account->credit)
					->getConversion();

				$exchange_data = [];
				$exchange_data['credit'] = $conversion;

				$a_credit = $conversion['destination_value'];
				$conversion = $exchange->setFrom($base_currency)
					->setTo($chart_of_account->currency)
					->setAmount($involved_account->debit)
					->getConversion();
				$a_debit = $conversion['destination_value'];
				$exchange_data['debit'] = $conversion;


				$details = [];
				$details['exchange'] = $exchange_data;

				$involved_account->update([
					'a_credit' => $a_credit,
					'a_debit' => $a_debit,
					'details' => $details
				]);
			}
			DB::connection('wallet')->commit();
			$this->refresh();
			return $this;
		} catch (\Exception $th) {
			DB::connection('wallet')->rollback();

			return false;
			//throw $th;
		}
	}




	/**
	 * attempt to save
	 * the journal accroding to mode 
	 *
	 * @param integer $mode
	 * @return void
	 */
	public function attemptPublish($mode)
	{
		$journal = self::find($this->id);
		DB::beginTransaction();

		try {

			$modes = [
				1 => "draft",
				2 => "pend", //final
				3 => "publish", //final
			];

			$method = $modes[$mode];
			$response = $journal->$method();


			if (!$response) {
				throw new Exception("Error Processing Request $method failed", 1);
			}


			DB::commit();
			return true;
		} catch (\Throwable $th) {
			DB::rollback();
			print_r($th->getMessage());

			return false;
			//throw $th;
		}
	}

	/**
	 * Scope Journals in drafts
	 *
	 * @param  $query
	 * @return void
	 */
	public function scopeDrafts($query)
	{
		return $query->where('status', '<=', 1);
	}

	/**
	 * Scope pending Journals
	 *
	 * @param  $query
	 * @return void
	 */
	public function scopePending($query)
	{
		return $query->where('status', 2);
	}

	/**
	 * Scope Published Journals 
	 *
	 * @param [type] $query
	 * @return void
	 */
	public function scopePublished($query)
	{
		return $query->where('status', 3);
	}


	/**
	 * Determine if this journal can be declined
	 *
	 * @return boolean
	 */
	public function is_pending()
	{
		return in_array($this->status, [2]);
	}

	/**
	 * Determine if this journal can be declined
	 *
	 * @return boolean
	 */
	public function is_declinable()
	{
		return in_array($this->status, [2]);
	}




	/**
	 * Scope Journals belonging to a company
	 *
	 * @param  $query
	 * @param integer $company_id
	 * @return void
	 */
	public function scopeCompanyJournals($query, $company_id)
	{
		return $query->where('company_id', $company_id);
	}


	/**
	 * Generate Edit Link
	 *
	 * @return string
	 */
	public function geteditLinkAttribute()
	{
		return Config::domain() . "/journals/{$this->id}/edit";
	}

	/**
	 * Generate Decline Link
	 *
	 * @return string
	 */

	public function getdeclineLinkAttribute()
	{
		return Config::domain() . "/journals/{$this->id}/decline";
	}


	public function getcompleteLinkAttribute()
	{
		return Config::domain() . "/journals/{$this->id}/complete";
	}


	public function getreverseLinkAttribute()
	{
		return Config::domain() . "/journals/{$this->id}/reverse";
	}

	/**
	 * Generate view Link
	 *
	 * @return string
	 */
	public function getviewLinkAttribute()
	{
		return Config::domain() . "/journals/{$this->id}/view";
	}

	/**
	 * Get attached documents to this
	 * journal
	 *
	 * @return array
	 */
	public function getattachmentsAttribute()
	{
		if ($this->attached_files == null) {

			return [];
		}

		return json_decode($this->attached_files, true);
	}

	/**
	 * Determine if this journal is editable
	 * 
	 * @return boolean
	 */
	public function is_editable()
	{
		return in_array($this->status, [null, 1]);
	}

	/**
	 * Determine if this journal is published
	 *
	 * @return boolean
	 */
	public function is_published()
	{
		return in_array($this->status, [3, 4]);
	}

	/**
	 * Get displayable status (in html)
	 *
	 * @return void
	 */
	public function getpublishedStateAttribute()
	{
		$text = self::$statuses[$this->status]['key'];
		$class = self::$statuses[$this->status]['class'];
		return "<small style='font-size:7px;' class='badge badge-sm badge-$class'>$text</small>";
	}

	/**
	 * get the amount of this journal
	 *
	 * @return float
	 */
	public function getDeducedAmountAttribute()
	{
		$amount =  $this->involved_accounts->sum('credit');
		return $amount;
	}


	/**
	 * attach files to this journal
	 *
	 * @param array $files
	 * @return void
	 */
	public function upload_attachments(array $files)
	{
		$directory = 'uploads/journals_attachments';

		foreach ($files as $attribute => $attributes) {
			foreach ($attributes as $key => $value) {
				$refined_file[$key][$attribute] = $value;
			}
		}

		$i = 0;
		foreach ($refined_file as  $file) {

			$handle = new Upload($file);


			$file_type = explode('/', $handle->file_src_mime)[0];
			if (($file_type == 'image') || ($file_type == 'video') || true) {




				$handle->Process($directory);
				$file_path = $directory . '/' . $handle->file_dst_name;

				$attachments[$i] = $file_path;
			}
			$i++;
		}

		$this->update(['attached_files' => json_encode($attachments)]);

		return ($this);
	}

	/**
	 * Get the created_at date in "Y-m-d" format
	 *
	 * @return void
	 */
	public function getcreateddateAttribute()
	{
		return date("Y-m-d", strtotime($this->created_at));
	}


	/**
	 * Relates the invloved accounts(line items)
	 *
	 * @return void
	 */
	public function involved_accounts()
	{
		return $this->hasMany(JournalInvolvedAccounts::class, 'journal_id')->with(['chart_of_account']);
	}


	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function getDetailsAttribute($value)
	{

		return json_decode($value, true);
	}




	/**
	 * Mutators: Set the details attribute
	 *
	 * @param mixed $value
	 * @return void
	 */
	public function setDetailsAttribute($value)
	{
		$this->attributes['details'] = json_encode($value);
	}
}
