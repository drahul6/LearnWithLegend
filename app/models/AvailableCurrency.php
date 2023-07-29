<?php

namespace App\models;


use Illuminate\Database\Eloquent\Model as Eloquent;

class AvailableCurrency extends Eloquent
{

	protected $fillable = ['name', 'code', 'html_code', 'available'];

	protected $table = 'currencies';



	public static function fetch_currency($code)
	{
		return self::where('code', $code)->first();
	}

	public function scopeAvailable($query)
	{
		return $query->where('available', 1);
	}
}
