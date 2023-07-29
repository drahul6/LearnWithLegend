<?php


namespace Filters\Filters;

use Filters\QueryFilter;
use User, Orders;
use Filters\Traits\RangeFilterable;



/**
 * 
 */
class OrderFilter extends QueryFilter
{
	use RangeFilterable;




	public function type_of_product($ref = null)
	{

		if ($ref == null) {
			return;
		}
		$this->builder->where('buyer_order', 'like', "%$ref%");
	}



	public function user($user = null)
	{
		if ($user == null) {
			return;
		}

		$user_ids =  User::WhereRaw(
			"firstname like ? 
	                                      OR lastname like ? 
	                                      OR email like ? 
	                                      OR phone like ? 
	                                      OR username like ? 
	                                      ",
			array(
				'%' . $user . '%',
				'%' . $user . '%',
				'%' . $user . '%',
				'%' . $user . '%',
				'%' . $user . '%'
			)
		)->get()->pluck('id')->toArray();

		$this->builder->whereIn('user_id', $user_ids);
	}


	public function seller($user = null)
	{
		if ($user == null) {
			return;
		}

		$user_ids =  User::WhereRaw(
			"firstname like ? 
	                                      OR lastname like ? 
	                                      OR email like ? 
	                                      OR phone like ? 
	                                      OR username like ? 
	                                      ",
			array(
				'%' . $user . '%',
				'%' . $user . '%',
				'%' . $user . '%',
				'%' . $user . '%',
				'%' . $user . '%'
			)
		)->get()->pluck('id')->toArray();

		$this->builder->whereIn('sellers_ids', $user_ids);
	}






	public function status($status = null)
	{
		if ($status == null) {
			return;
		}

		$this->builder->where('status', $status);
	}





	public function ref($ref = null)
	{

		if ($ref == null) {
			return;
		}
		$this->builder->where('payment_details', 'like', "%$ref%");
	}








	public function payment_method($method = null)
	{

		if ($method == null) {
			return;
		}
		$this->builder->where('payment_details', 'like', "%$method%");
	}

	public function payment_status($payment_status = null)
	{
		if ($payment_status == null) {
			return;
		}

		$operations = ['paid' => '!=',  'unpaid' => '='];
		$operation = $operations[$payment_status];

		$this->builder->where('paid_at', $operation, null);
	}



	public function price($start = null, $end = null)
	{

		if (($start == null) &&  ($end == null)) {
			return;
		}

		$price = compact('start', 'end');

		if ($end == null) {
			$price = $start;
		}

		$this->date($price, 'amount_payable');
	}




	public function ordered($start_date = null, $end_date = null)
	{

		if (($start_date == null) &&  ($end_date == null)) {
			return;
		}

		$date = compact('start_date', 'end_date');

		if ($end_date == null) {
			$date = $start_date;
		}

		$this->date($date, 'created_at');
	}



	public function paid_at($start_date = null, $end_date = null)
	{

		if (($start_date == null) &&  ($end_date == null)) {
			return;
		}

		$date = compact('start_date', 'end_date');

		if ($end_date == null) {
			$date = $start_date;
		}

		$this->date($date, 'paid_at');
	}
}
