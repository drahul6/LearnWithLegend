<?php

namespace v2\Models\Wallet;

use v2\Models\Wallet\Journals;
use v2\Filters\Traits\Filterable;
use v2\Models\Wallet\ChartOfAccount;
use Illuminate\Database\Eloquent\Model as Eloquent;

class JournalInvolvedAccounts extends Eloquent
{

	use Filterable;

	protected $fillable = [
		'journal_id',
		'chart_of_account_id',
		'description',
		'contact',
		'tax',
		'credit',
		'debit',
		'prior_balance',
		'post_balance',
		'prior_available_balance',
		'post_available_balance',
		'a_credit',
		'a_debit',
		'a_prior_balance',
		'a_post_balance',
		'a_prior_available_balance',
		'a_post_available_balance',
		'details',
		'created_at',

	];

	protected $table = 'ac_involved_accounts';
	protected $connection = 'wallet';
	protected $appends = ['chart_of_account_number'];




	public function updateDetailsByKey($key, $value)
	{
		$details = $this->DetailsArray;
		$details[$key] = $value;
		return $this->update(['details' => $details]);
	}


	public function getDetailsArrayAttribute()
	{
		if ($this->details == null) {
			return [];
		}

		return json_decode($this->details, true);
	}



	public function increasedBalance()
	{

		if (
			$this->chart_of_account->is_credit_balance() && $this->Type == 'credit'
			|| $this->chart_of_account->is_debit_balance() && $this->Type == 'debit'
		) {
			return true;
		}
		return false;
	}

	public function decreasedBalance()
	{
		return !$this->increasedBalance();
	}


	public function getTypeAttribute()
	{
		$type = $this->credit == 0 ? 'debit' : 'credit';
		$this->attributes['type'] = $type;
		return $type;
	}

	public function isType($post)
	{
		return $this->$post > 0;
	}


	public function get_second_leg()
	{
		return self::where('journal_id', $this->journal_id)->where('id', '!=', $this->id)->first();
	}



	public function getformattedAmountsAttribute()
	{
		$figures = [
			'credit',
			'debit',
			'prior_balance',
			'post_balance',
			'prior_available_balance',
			'post_available_balance',
			'a_credit',
			'a_debit',
			'a_prior_balance',
			'a_post_balance',
			'a_prior_available_balance',
			'a_post_available_balance',
			'amount',
			'a_amount',
		];

		$response = [];
		foreach ($figures as $key => $figure) {
			if ($this->prior_balance < 0) {
				$value =   "(" . number_format(abs($this->prior_balance), 2) . ")";
				$response[$figure] = $value;
			} else {
				$value =  number_format($this->prior_balance, 2);
				$response[$figure] = $value;
			}
		}

		$amount_figures = [
			'amount' => max($this->credit, $this->debit),
			'a_amount' => max($this->a_credit, $this->a_debit),
		];


		$currency =  ($this->chart_of_account['CurrencySymbol']);
		foreach ($amount_figures as $key => $figure) {
			$figure = $figure == '' ? 0 : $figure;
			$sign = $this->increasedBalance() ? "+"	: "-";
			$color = $this->increasedBalance() ? "green"	: "tomato";
			$value = "<span style=color:$color>{$sign}$currency{$figure}</span>";
			$response[$key] = $value;
		}

		return $response;
	}

	public function getformattedPriorBalanceAttribute()
	{
		if ($this->prior_balance < 0) {
			return  "(" . number_format(abs($this->prior_balance), 2) . ")";
		} else {
			return number_format($this->prior_balance, 2);
		}
	}


	public function getformattedPostBalanceAttribute()
	{
		if ($this->post_balance < 0) {
			return  "(" . number_format(abs($this->post_balance), 2) . ")";
		} else {

			return number_format($this->post_balance, 2);
		}
	}


	public function getformattedPostAvailBalanceAttribute()
	{
		if ($this->post_available_balance < 0) {
			return  "(" . number_format(abs($this->post_available_balance), 2) . ")";
		} else {
			return number_format($this->post_available_balance, 2);
		}
	}



	/**
	 * [$validator_rules this contains validations rules.
	 * it will be used with the Validator classs.
	 * keys are names of form inputs POSTED while values are arrays 
	 * containing rules]
	 * @var [array]
	 */
	public static $validator_rules = [

		'credit' => [
			'numeric' =>  true,
		],

		'debit' => [
			'numeric' =>  true,
		],

		'description' => [
			'required' =>  true,
			'max' =>  500,
			'min' =>  2,
		],

		'chart_of_account_id' => [
			//will be checked in controller manually
		],

	];



	/**
	 * Reverse this line
	 *
	 * @return void
	 */
	public function reverse_entry()
	{
		$chart_of_account = $this->chart_of_account;


		$chart_of_account->reverse_line($this, 'credit');
		$chart_of_account->reverse_line($this,  'debit');




		//We will do more here like reconcile the current balance of the chart
		//of account. Based on whether 
		//1) this is a credit or a debit post
		//2) chart of account is a credit or debit balance

	}


	/**
	 * Delete this from DB
	 *
	 * @return void
	 */
	public function delete_involved_account()
	{
		$this->delete();
	}

	/**
	 * Creates involved account
	 *
	 * @param array $involved_account
	 * @param Journals $journal
	 * @return void
	 */
	public static function create_involved_account($involved_account, $journal)
	{
		$involved_account['journal_id'] = $journal->id;
		return self::create($involved_account);
	}

	/**
	 * Relates the chart of account 
	 *
	 * @return void
	 */
	public function chart_of_account()
	{
		return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
	}

	/**
	 * Relates the journal
	 *
	 * @return 
	 */
	public function journal()
	{
		return $this->belongsTo(Journals::class, 'journal_id');
	}

	/**
	 * Accessor: Gets the amount attribute
	 *
	 * @return void
	 */
	public function getAmountAttribute()
	{
		$amount = $this->credit == 0 ? $this->debit : $this->credit;
		$this->attributes['amount'] = $amount;
		return $amount;
	}

	public function getAAmountAttribute()
	{
		$a_amount = max($this->a_credit, $this->a_debit);
		$this->attributes['a_amount'] = $a_amount;
		return $a_amount;
	}


	/**
	 * Accessor: Gets the chart_of_account_number attribute
	 *
	 * @return integer
	 */
	public function getChartOfAccountNumberAttribute()
	{
		return $this->attributes['chart_of_account_number'] = $this->chart_of_account->account_number;
	}

	/**
	 * Mutators: Set the credit attribute
	 *
	 * @param mixed $value
	 * @return void
	 */
	public function setCreditAttribute($value)
	{
		$this->attributes['credit'] = $value == '' ? 0 : $value;
	}

	/**
	 * Mutators: Set the debit attribute
	 *
	 * @param mixed $value
	 * @return void
	 */
	public function setDebitAttribute($value)
	{
		$this->attributes['debit'] = $value == '' ? 0 : $value;
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
