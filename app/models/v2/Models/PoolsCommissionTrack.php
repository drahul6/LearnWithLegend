<?php

namespace v2\Models;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;

class PoolsCommissionTrack extends Eloquent
{

    protected $fillable = [

        "user_id",
        "payment_month",
        "unpaid_amount",
        "details",
        "status",
    ];



    protected $table = 'pools_commission_track';


    public static $statuses = [
        '1' => 'settled',
    ];


    public function getDetailsArrayAttribute()
    {
        if ($this->details == null) {
            return [];
        }

        return json_decode($this->details, true);
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
