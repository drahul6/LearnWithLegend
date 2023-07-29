<?php


use v2\Models\Wallet\ChartOfAccount;
use v2\Models\Wallet\BasicAccountType;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Company extends Eloquent
{

	protected $fillable = [
		'organisation_id',
		'name',
		'address',
		'office_email',
		'office_phone',
		'company_description'
	];

	protected $table = 'companies';




	public function get_trial_balance($as_of_date)
	{
		return ChartOfAccount::get_trial_balance($this, $as_of_date);
	}
}
