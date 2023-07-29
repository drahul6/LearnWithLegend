<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class RememberAuth extends Eloquent
{
    protected $fillable = [
        'user_id',
        'selector',
        'token',
        'expires_at',
        'dump',
    ];

    protected $table = 'remember_auth';



    public function isNotExpired()
    {
        return time() < strtotime($this->expires_at);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function recallUser($browser_token)
    {
        $salt = $_ENV['SALT'];

        $level_1 =  base64_decode($browser_token);
        $de_salt = str_replace($salt, "", $level_1);
        $explode = explode(":", $de_salt);

        $selector = $explode[0] ?? null;
        $key = $explode[1] ?? null;

        return  self::where("selector", $key)->where('token', $browser_token)->first();
    }

    public static function remember($user)
    {
        $rememberance = self::where('user_id', $user->id)->first();

        if ($rememberance && $rememberance->isNotExpired()) {

            $token = $rememberance->token;
            $expires_at = $rememberance->expires_at;
            return $rememberance;
        }

        //generate new token
        $salt = $_ENV['SALT'];
        $key = uniqid($user->id);
        $token =  base64_encode("$salt.$user->id:$key");
        /* 
        */
        $expires_at = date("Y-m-d", strtotime("+7 months"));

        $rememberance = self::updateOrCreate([
            'user_id' => $user->id,
            'selector' => $key,
        ], [
            'token' => $token,
            'expires_at' => $expires_at,
        ]);

        return $rememberance;
    }
}
