<?php

namespace v2\Models\Wallet;


use v2\Models\Wallet\ChartOfAccount;
use Illuminate\Database\Eloquent\Model as Eloquent;

class AcDashboardSettings extends Eloquent
{

	protected $fillable = ['company_id', 'name', 'accounts_ids', 'status'];

	protected $table = 'ac_dashboard_settings';
	protected $connection = 'wallet';


	public function getChartOfAccountIdsAttribute()
	{

		return json_decode($this->accounts_ids,  true);
	}

	public function getAccountsAttribute()
	{
		return ChartOfAccount::where('company_id', $this->company_id)
			->whereIn('id', $this->ChartOfAccountIds)->get();
	}


	public function is_showable()
	{
		return ($this->status == 1);
	}


	public function getStateAttribute()
	{

		if ($this->is_showable()) {
			return "<span class='label label-success'>Published</span>";
		} else {
			return  "<span class='label label-danger'>Not Published</span>";
		}
	}



	public static function for_company($company_id)
	{
		return self::where('company_id', $company_id);
	}




	public function getLabelAttribute()
	{
		$array = $this->ChartOfAccountIds;
		$chart_of_account  = ChartOfAccount::find($array[0]);
		return (count($array) > 1) ? $this->name : $chart_of_account->account_name;
	}



	public static function showables($company_id)
	{
		return self::where('status', 1)->where('company_id', $company_id);
	}



	public function getBalanceAttribute()
	{
		return ChartOfAccount::where('company_id', $this->company_id)
			->whereIn('id', $this->ChartOfAccountIds)->sum('current_balance');
	}
};
