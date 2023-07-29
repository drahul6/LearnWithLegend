<?php

/**
 */
class ReferralController extends controller
{


    public function __construct()
    {
    }


    public function index($referral_username = null, $landing_page = null)
    {


        $referral_username = explode("/", $_GET['url'])[1] ?? null;
        // $referral_username = str_replace("_", " ", $referral_username);


        if ($referral_username == null) {
            Redirect::to('');
        }


        $referral = User::where('username', $referral_username)->first();

        if ($referral == null) {
            Redirect::to('');
        }

        if (isset($_COOKIE['referral'])) {
            // Redirect::to('');
        }

        setcookie(Config::cookie_name(), $referral_username, time() + (86400 * 30 * 365), "/"); // 86400 = 1 year


        if ($landing_page == 1) {
            Redirect::to('');
        }

        Redirect::to('register');
    }
}
