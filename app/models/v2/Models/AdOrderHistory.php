<?php

namespace v2\Models;



use Orders;
use Illuminate\Database\Eloquent\Model as Eloquent;

class AdOrderHistory extends Eloquent
{

	protected $fillable = [
		'game_date',
		'ads_ids'
	];

	protected $table = 'ads_order_history';


	public function has_record($ad_id)
	{
		$history_array = $this->Ads;

		return in_array($ad_id, $history_array);
	}


	public function getAdsAttribute()
	{
		return json_decode($this->ads_ids, true);
	}

	public static function history($game_date)
	{

		$history = self::where('game_date', $game_date)->first();

		$bought_orders_array = [];

		$bought_orders =  Orders::where('game_date', $game_date)->Paid()->get();
		foreach ($bought_orders as $key => $orders) {
			$ad_ordered = collect($orders->order_detail())->pluck('id')->toArray();
			$bought_orders_array = 	array_merge($ad_ordered, $bought_orders_array);
		}

		$history = self::updateOrCreate(
			[
				'game_date' => $game_date
			],
			[

				'ads_ids' => json_encode($bought_orders_array)
			]
		);

		return $history;
	}
}
