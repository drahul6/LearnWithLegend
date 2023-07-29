<?php

namespace v2\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class UserWithdrawalMethod extends Eloquent
{

	protected $fillable = [

		'user_id',	'method',	'details'
	];

	protected $table = 'users_withdrawals_methods';



	public  static $method_options = [
		/* 'bitcoin'=> [
			'name' => 'Bitcoin',
			'class' => 'Bitcoin',
			'view' => 'withdrawal_methods/bitcoin',
			'display' => [
							'bitcoin_address'=> 'Bitcoin Address'
						],
		],

		'ethereum'=> [
			'name' => 'Ethereum',
			'class' => 'Ethereum',
			'view' => 'withdrawal_methods/ethereum',
			'display' => [
				'ethereum_address'=> 'Ethereum Address'
						],
		], */
		'paypal' => [
			'name' => 'Paypal',
			'class' => 'Paypal',
			'view' => 'withdrawal_methods/paypal',
			'display' => [

				'email_address' => 'Email Address'
			],
		],

		/* 
		'skrill'=> [
			'name' => 'Skrill',
			'class' => 'Skrill',
			'view' => 'withdrawal_methods/skrill',
			'display' => [

							'email_address'=> 'Email Address'
					],
		],

		'payeer'=> [
			'name' => 'Payeer',
			'class' => 'Payeer',
			'view' => 'withdrawal_methods/payeer',
			'display' => [

							'payeer_id'=> 'Payeer ID'
			],
		], */
	];





	public static function scopeForUser($query, $user_id)
	{
		return  $query->where('user_id', $user_id);
	}



	public static function for($user_id, $method)
	{
		$return  = self::where('user_id', $user_id)->where('method', $method)->first();

		return $return;
	}



	public function getDetailsArrayAttribute()
	{
		if ($this->details == null) {
			return [];
		}

		return json_decode($this->details, true);
	}





	public function user()
	{
		return $this->belongsTo('User', 'user_id');
	}
}
