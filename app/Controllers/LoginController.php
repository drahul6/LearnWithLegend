<?php


@session_start();


class LoginController extends controller
{

    public function __construct()
    {
        // print_r($_SESSION);
    }


    public function adminLogindfghjkioiuy3hj8()
    {

        /*if($this->auth() ){
            Redirect::to('admin-dashboard');
        }*/
        $this->view('admin/login', []);
    }


    // authenticateing admnistrators
    public function authenticateAdmin()
    {
        if (Input::exists('admin_login') || true) {


            $trial = Admin::where('email', Input::get('user'))->first();

            if ($trial == null) {

                $trial = Admin::where('username', Input::get('user'))->first();
            }


            $user_id = $trial->id;

            $admin = $this->authenticate_with($user_id, Input::get('password'), $_POST['remember_me'], 'admin');

            if (!$admin) {
                Session::putFlash('danger', 'Invalid Credentials');
                $this->validator()->addError('credentials', "<i class='fa fa-exclamation-triangle'></i> Invalid Credentials.");
                Redirect::back();
            }

            Session::putFlash('success', "Welcome Admin $admin->firstname");
            Redirect::to('admin-dashboard');
        }
    }


    public function index()
    {


        if ($this->auth()) {
            Redirect::to("user/dashboard");
        }

        $this->view('auth/login', []);
    }


    public function submit_2fa()
    {
        if (!isset($_SESSION['awaiting_2fa'])) {
            Session::putFlash('danger', "Invalid Request");
            Redirect::to('login');
        }


        print_r($_POST);

        $user = User::find($_SESSION['awaiting_2fa']);

        // $this->verify_2fa_only($user);
        $_2FA = new TwoFactor($user);

        if (!$_2FA->hasLogin($_POST['code'])) {
            Session::putFlash('danger', "Invalid. Please Enter Valid 2FA Code");
            Redirect::back();
        }

        $this->directly_authenticate($user->id);
        Redirect::to('user');
    }


    public function enter_2fa_code()
    {
        if (!isset($_SESSION['awaiting_2fa'])) {
            Session::putFlash('danger', "Invalid Request");
            Redirect::to('login');
        }

        $this->view('auth/enter_2fa_code');
    }


    /**
     * this function handles user authentication
     * @return instance of eloquent object of the authenticated User model
     */
    public function authenticate()
    {

        if (Input::exists("user_login") || true) {
            // print_r(Input::all());

            MIS::verify_google_captcha();

            parse_str($_SERVER['HTTP_REFERER'], $referral_url);
            $intended_route =  array_values($referral_url)[0];

            $trial = User::where('username', Input::get('user'))->first();

            if ($trial == null) {

                $trial = User::where('email', Input::get('user'))->first();
            }



            $user_id = $trial->id;
            $result = $this->authenticate_with($user_id, Input::get('password'), $_POST['remember_me']);


            if ($result) {

                if ($intended_route != null) {
                    Redirect::to($intended_route);
                }

                // $welcome_page = User::$redirect_path[$this->auth()->type_id];

                Redirect::to('login');
            } else {

                $this->validator()->addError('user_login', "<i class='fa fa-exclamation-triangle'></i> Invalid Credentials.");
            }
        }

        print_r($this->validator()->errors());


        Redirect::to("login");
    }



    public function logout($user = null)
    {

        if ($user == 'admin') {

            $this->logout_user($user);

            Redirect::to('login/adminLogin');
        } else {

            $this->logout_user('user');
        }

        Redirect::to('login');
    }
}
