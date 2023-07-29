<?php

use v2\Shop\Shop;
use app\models\ProspectiveUser;



@session_start();

/**
 */
class RegisterController extends controller
{

    public function __construct()
    {
    }


    public function confirm_email($email, $email_verification_token)
    {

        $user = User::where('email', $email)
            ->where('email_verification', $email_verification_token)
            ->first();

        if ($user != null) {
            $update = $user->update(['email_verification' => 1]);
            //give referral point

            if ($update) {

                Session::putFlash('success', 'Email verified successfully');
            } else {

                Session::putFlash('danger', 'Email verification unsuccesfully');
            }
        }

        Redirect::to('user');
    }


    public function confirm_phone()
    {


        if (Input::exists('fe') || true) {

            $this->validator()->check(Input::all(), array(

                'phone_code' => [

                    'required' => true,
                    'exist' => 'User|phone_verification',
                ],

            ));
        }

        if ($this->validator()->passed()) {


            if ($this->auth()->phone_verification == Input::get('phone_code')) {

                $update = $this->auth()->update(['phone_verification' => 1]);

                if ($update) {

                    Session::putFlash('success', 'Phone verified successfully');
                } else {

                    Session::putFlash('danger', 'Phone verification unsuccessful.');
                }


                Redirect::to('login');
            } else {

                Session::putFlash('danger', 'Phone verification unsuccessful.');
            }
        }
        Redirect::back();
    }


    public function verify_phone()
    {
        $message = "Dear " . $this->auth()->firstname . ", your code is " . $this->auth()->phone_verification . " from " . Config::project_name();
        $phone = $this->auth()->phone;

        return (new SMS)->send_sms($phone, $message);
    }


    public function send_credential($user_id, $password)
    {
        ob_start();


        $user = User::find($user_id);
        $name = $user->firstname;
        $email = $user->email;
        $username = $user->username;

        $subject = 'SUCCESSFUL REGISTRATION';
        $body = $this->buildView('emails/credential-message', [
            'password' => $password,
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'email_verification_token' => $user->email_verification,
        ]);


        $to = $email;


        $mailer = new Mailer;
        $status = $mailer->sendMail($to, $subject, $body, $reply = '', $recipient_name = '');

        $response = (!$status) ? 'false' : 'true';

        // ob_end_clean();

        echo $response;
    }


    public function verify_email()
    {


        ob_start();

        $user = $this->auth();
        $name = $user->firstname;
        $email = $user->email;

        $subject = 'EMAIL VERIFICATION';
        $body = $this->buildView('emails/email-verification', [
            'name' => $name,
            'email' => $email,
            'email_verification_token' => $user->email_verification,
        ]);


        $to = $email;

        $email_verification_token = $user->email_verification;
        $email = $user->email;


        /*
        $body = 'Thank you for signing up at '.Conproject_name.', \n please click this link to continue '.domain.'/register/confirm_email/'.$email.'/'.$email_verification_token.'';*/

        // $status =  mail($email, $subject, $link);

        $mailer = new Mailer;
        $status = $mailer->sendMail($to, $subject, $body, $reply = '', $recipient_name = '');


        ob_end_clean();

        if ($status) {
            Session::putFlash('success', "Verification Mail Sent!<br>
            Please check your inbox or spam folder for the email and click the confirm button or link to verify your account.");
        } else {
            Session::putFlash('danger', "Verification Mail Could not Send.");
        }

        Redirect::back();
    }


    public function generate_phone_code_for($user_id)
    {

        $remaining_code_length = 6 - strlen($user_id);
        $min = pow(10, ($remaining_code_length - 1));
        $max = pow(10, ($remaining_code_length)) - 1;

        $remaining_code = random_int($min, $max);

        return $phone_code = $user_id . $remaining_code;
    }


    public function index()
    {
        $settings = SiteSettings::getSettings('rules_settings');

        $shop = new Shop;

        $this->view('auth/register', get_defined_vars());
    }


    /**
     * handles the first stage of user registration
     * @return [type]
     */
    public function register()
    {
        echo "<pre>";


        print_r($_POST);

        if (!Input::exists()) {
            // Redirect::back();
        }


        MIS::verify_google_captcha();
        $this->validator()->check(Input::all(), array(

            'firstname' => [
                'required' => true,
                'max' => '32',
                'max' => '32',
                'min' => '2',
            ],
            'lastname' => [
                'required' => true,
                'max' => '32',
                'min' => '2',
            ],

            'phone' => [
                // 'required' => true,
                'max' => '32',
                'min' => '2',
            ],


            'username' => [
                'required' => true,
                'min' => 3,
                'one_word' => true,
                'no_special_character' => true,
                'unique' => User::class,
            ],


            'email' => [
                'required' => true,
                'email' => true,
                'min' => 3,
                'unique' => User::class,
            ],


            'password' => [

                'required' => true,
                'min' => 6,
                'max' => 32,
            ],

            'introduced_by' => [
                'exist' => 'User|username',
            ],

        ));


        $enroler = User::where('username', Input::get('introduced_by'))->first();


        if (!$this->validator->passed()) {
            Session::putFlash('danger', Input::inputErrors());
            Redirect::back();
        }

        $user_details = Input::all();

        //if paid registration is enabled
        $settings = SiteSettings::getSettings('rules_settings');
        if ($settings['only_paid_members_registration'] == true && !Input::get('allow')) {

            $prospective_user =  ProspectiveUser::create([
                "data" => $user_details,

            ]);

            $callback_param = http_build_query([
                'checkout_type' => "standard",
                'item_purchased' => "prospective_user",
                'order_unique_id' => $prospective_user->id,
                'payment_method' => $prospective_user->data['membership']['payment_method'],
            ]);


            $callback_url = "shop/checkout?$callback_param";
            Redirect::to("$callback_url");

            return;
        }


        $new_user = User::createUser($user_details);

        if (!$new_user) {
            Redirect::back();
        }

        Session::putFlash('success', "Registration Successful!.");
        $this->directly_authenticate($new_user->id);


        if (Input::get('allow')) {
            return $new_user;
        }

        Redirect::to("user");
    }


    public function get_referral($referral_username = '')
    {
        return User::where('username', Input::get('introduced_by'))->first()->mlm_id ?? 1;
    }



    public function generate_username_from_email($email)
    {

        $username = explode('@', $email)[0];

        $i = 1;
        do {
            $loop_username = ($i == 1) ? "$username" : "$username" . ($i - 1);
            $i++;
        } while (User::where('username', $loop_username)->get()->isNotEmpty());


        return $loop_username;
    }


    public function sendWelcomeEmail($user_id, $password)
    {

        $new_user = User::find($user_id);

        $mailer = new Mailer();
        $subject = $this->name . " SUCCESSFUL REGISTRATION!!";

        $body = $this->buildView('emails/registration-welcome-mail', [
            'firstname' => $new_user->firstname,
            'user_id' => $new_user->username,
            'password' => $password,
        ]);

        $to = $new_user->email;
        $recipient_name = $new_user->firstname;

        if ($mailer->sendMail($to, $subject, $body, $reply = '', $recipient_name)) {
            return;
        }
    }
}
