<?php

namespace v2\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class InvestmentPackage extends Eloquent 
{
	
	protected $fillable = [
				'name',	'details','pack_id', 'features','availablity','category'
				];
	
	protected $table = 'investment_ranges';

	private $amount ;

	public static $categories = [
		1=> [
			'name'=>"REGULAR",
		],
		2=>	[
			'name'=>"CO-FOUNDER PACKS",
		],
	];


	public function getMaturityTimeFrom(string $date_string = null)
	{
		echo $maturity_in_days = $this->DetailsArray['maturity_in_days'];

		$maturity_time = date("Y-m-d H:i:s",  strtotime("$date_string + $maturity_in_days days"));

		return $maturity_time;
	}

	/**
	 *
	 * @return array
	 */
	public function getWorthAfterMaturity()
	{
		$setting = ($this->DetailsArray);
		$roi_percent = $setting['roi_percent'];
		$capital = $setting['min_capital'];

		$roi =  ($roi_percent * 0.01 * $capital);
		$roi_and_capital = $capital + $roi;
		
		$return = compact('roi','roi_and_capital');
		return $return;

	}

	public function in_range($amount)
	{
		$setting = ($this->DetailsArray);
		$min_capital = $setting['min_capital'];
		$max_capital = $setting['max_capital'];

		if (($min_capital <= $amount) && ($amount <= $max_capital)) {

			return true;
		}else{

			return false;

		}
	}


	public function is_available()
	{
		return (bool) ($this->availablity =='on');
	}



	public function scopeAvailable($query)
	{
		return $query->where('availablity', 'on');
	}



	public function getDetailsArrayAttribute()
	{
	    if ($this->details == null) {
	        return [];
	    }

	    return json_decode($this->details, true);
	}







}