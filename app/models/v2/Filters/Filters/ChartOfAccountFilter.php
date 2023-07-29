<?php

namespace v2\Filters\Filters;

use Filters\QueryFilter;
use User;
use Filters\Traits\RangeFilterable;

/**
 * 
 */
class ChartOfAccountFilter extends QueryFilter
{
	use RangeFilterable;


	public function ref($ref = null)
	{

		if ($ref == null) {
			return;
		}

		$ref = explode(',', $ref);

		$this->builder->whereIn('id', $ref);
	}


	public function account_name($account_name = null)
	{
		if ($account_name == null) {
			return;
		}

		$this->builder->where('account_name', "like",  "%$account_name%");
	}


	public function account_code($account_code = null)
	{
		if ($account_code == null) {
			return;
		}

		$this->builder->where('account_code', "like",  "%$account_code%");
	}



	public function account_number($account_number = null)
	{
		if ($account_number == null) {
			return;
		}

		$this->builder->where('account_number', $account_number);
	}




	public function lastname($lastname = null)
	{
		if ($lastname == null) {
			return;
		}

		$user_ids = User::where('lastname', "like",  "%$lastname%")->get()->pluck('id')->toArray();

		$this->builder->whereIn('owner_id', $user_ids);
	}


	public function email($email = null)
	{
		if ($email == null) {
			return;
		}

		$user_ids = User::where('email', $email)->get()->pluck('id')->toArray();

		$this->builder->whereIn('owner_id', $user_ids);
	}

	public function name($name = null)
	{
		if ($name == null) {
			return;
		}


		$user_ids = User::WhereRaw(
			"firstname like ? 
                                      OR lastname like ? 
                                      OR username like ? 
                                      OR email like ? 
                                      OR phone like ? 
                                      ",
			array(
				'%' . $name . '%',
				'%' . $name . '%',
				'%' . $name . '%',
				'%' . $name . '%',
				'%' . $name . '%'
			)
		)->get()->pluck('id')->toArray();



		$imploded_ids = implode(",", $user_ids);

		$this->builder->WhereRaw(
			"account_name like ? 
                                      OR account_code like ? 
                                      OR account_number like ? 
                                      OR tag like ? 
                                      ",
			array(
				'%' . $name . '%',
				'%' . $name . '%',
				'%' . $name . '%',
				'%' . $name . '%'
			)

		);
		// $this->builder->whereRaw("owner_id in ($imploded_ids)");

		$this->builder->orWhereIn('owner_id', $user_ids);
	}



	public function phone($phone = null)
	{
		if ($phone == null) {
			return;
		}

		$user_ids = User::where('phone', $phone)->get()->pluck('id')->toArray();

		$this->builder->whereIn('owner_id', $user_ids);
	}





	public function opened($start_date = null, $end_date = null)
	{

		if (($start_date == null) &&  ($end_date == null)) {
			return;
		}


		if ($end_date == null) {
			$end_date = date("Y-m-d");
		}

		$date = compact('start_date', 'end_date');

		$this->date($date, 'created_at');
	}
}
