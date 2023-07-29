<?php

namespace v2\Models\Wallet;


use Illuminate\Database\Eloquent\Model as Eloquent;

class BasicAccountType extends Eloquent
{

	protected $fillable = [
		'name',
		'account_balance_type',
	];

	protected $table = 'ac_basic_account_types';
	protected $connection = 'wallet';


	public static function closables()
	{
		return self::where('has_opening_balance', 0);
	}


	public function getcodeAttribute()
	{
		return ($this->id * 1000);
	}


	public function scopeCreditBalances($query)
	{
		return $query->where('account_balance_type', 'credit_balance');
	}





	public function scopeDebitBalances($query)
	{
		return $query->where('account_balance_type', 'debit_balance');
	}



	public function has_opening_balance()
	{
		return ($this->has_opening_balance != 0);
	}




	public function is_credit_balance()
	{
		return ($this->account_balance_type == 'credit_balance');
	}


	public function is_debit_balance()
	{
		return ($this->account_balance_type == 'debit_balance');
	}
}
