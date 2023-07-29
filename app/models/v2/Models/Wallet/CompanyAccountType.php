<?php

namespace v2\Models\Wallet;


use v2\Models\Wallet\BasicAccountType;
use Illuminate\Database\Eloquent\Model as Eloquent;

class CompanyAccountType extends Eloquent
{

	protected $fillable = [
		'name',
		'company_id',
		'basic_account_id',
	];

	protected $table = 'ac_company_account_type';
	protected $connection = 'wallet';


	/**
	 * [$validator_rules this contains validations rules.
	 * it will be used with the Validator classs.
	 * keys are names of form inputs POSTED while values are arrays 
	 * containing rules]
	 * @var [array]
	 */
	public static $validator_rules = [
		'name' => [
			'required' =>  true,
			'unique' =>  CompanyAccountType::class,
		],

		'account_type' => [
			'required' =>  true,
		],
	];




	/**
	 * [for_company this fetches the custom account types(categories) for a company
	 * e.g Petty Cash may be an Expense(debit balance) to Company A 
	 * and not exist at all for company B
	 * @param  [int] $company_id [id of the company]
	 * @return Eloquent instance
	 */
	public  function scopefor_company($query, $company_id)
	{
		return	$query->where('company_id', $company_id);
	}








	/**
	 * [basic_account this fetches the related model(BasicAccountType)
	 *  instance using the basic_account_id]
	 * @return Eloquent instance
	 */
	public function basic_account()
	{
		return $this->belongsTo(BasicAccountType::class, 'basic_account_id');
	}
}
