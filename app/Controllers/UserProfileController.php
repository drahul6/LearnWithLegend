<?php

/**
 * this class is the default controller of our application,
 *
 */

use v2\Jobs\Jobs\SendEmailForNewPassword;
use Illuminate\Database\Capsule\Manager as DB;

class UserProfileController extends controller
{


    public function __construct()
    {
    }


    public function update_payment_info()
    {
        echo "<pre>";

        print_r(Input::all());
        print_r($_FILES);
        if (Input::exists('update_payment_info')) {


            $this->validator()->check(Input::all(), array(

                'account_name' => [
                    'required' => true,
                    'min' => 3,
                    'max' => 32,
                ],
                'bank_name' => [
                    'required' => true,
                    'min' => 3,
                    'max' => 32,
                ],
                'account_number' => [
                    'required' => true,
                    'numeric' => true,
                    'min' => 3,
                    'max' => 32,
                ],
            ));


            if ($this->validator->passed()) {

                $this->auth()->payment_information->update([
                    'bank_account_name' => Input::get('account_name'),
                    'bank_account_number' => Input::get('account_number'),
                    'bank_name' => Input::get('bank_name'),
                ]);


                if ($_FILES['upload']['error'] !== 4) {
                    $original_file = $this->upload_userid_proof($_FILES);
                    $this->auth()->payment_information->update(['id_proof' => $original_file]);
                }

                Session::putFlash('info', 'Payment Information updated successfully!');
            } else {
            }
        }


        Redirect::to('user/payment-information');
    }


    public function upload_userid_proof($files)
    {

        print_r($files);


        $directory = 'uploads/images/users/id_proofs';
        $handle = new Upload($files['upload']);

        //if it is image, generate thumbnail
        if (explode('/', $handle->file_src_mime)[0] == 'image') {
            $handle->Process($directory);
            $original_file = $directory . '/' . $handle->file_dst_name;

            (new Upload($this->auth()->payment_information->id_proof))->clean();

            return $original_file;
        }
    }


    public function change_password()
    {
        // $this->verify_2fa();

        if (/*Input::exists('change_password')*/
            true
        ) {

            $this->validator()->check(Input::all(), array(

                'current_password' => [
                    'required' => true,
                    'min' => 3,
                    'max' => 32,
                ],

                'new_password' => [

                    'required' => true,
                    'min' => 3,
                    'max' => 32,
                ],

                'confirm_password' => [
                    'required' => true,
                    'matches' => 'new_password',
                ],


            ));


            print_r($this->validator()->errors());

            $auth = $this->auth();

            if (!password_verify(Input::get('current_password'), $auth->password)) {
                $this->validator()->addError('current_password', "current password do not match");
            }

            if (!$this->validator()->passed()) {
                Session::putFlash('danger', Input::inputErrors());
                Redirect::back();
            }

            $auth->update(['password' => Input::get('new_password')]);
            $user = $auth;
            $type = 'Changed';
            // SendEmailForNewPassword::dispatch(compact('user', 'type'));

            Session::putFlash('success', "Password changed successfully!");
        }
        Redirect::back();
    }


    public function admin_change_password()
    {

        $user = User::find($_POST['id']);

        if (/*Input::exists('change_password')*/
            true
        ) {

            $this->validator()->check(Input::all(), array(

                'new_password' => [

                    'required' => true,
                    'min' => 3,
                    'max' => 32,
                ],

                'confirm_password' => [
                    'required' => true,
                    'matches' => 'new_password',
                ],


            ));


            if (!$this->validator()->passed()) {

                Session::putFlash('danger', Input::inputErrors());
                Redirect::back();
            }


            DB::beginTransaction();
            try {

                $user->update(['password' => Input::get('new_password')]);
                DB::commit();
                Session::putFlash('success', "Password changed successfully!");

                $new_password = $_POST['new_password'];

                //send email to this new user
                $receiver_subject = "Password Reset";
                $message = <<<EOL
        <p>Hi $user->firstname,</p>

        <p>Your Password has been reset by admin.</p>

        <p>New Password:$new_password</p>

        <p>Please login with the new password. Ensure to change your password after login.</p>
        <p>Contact support if you have to.</p>

        <p>Thanks.</p>

EOL;


                $receiver_content =  $this->buildView('emails/contact-message', compact('message'), true);

                $mailer = new Mailer;

                //sender email
                $mailer->sendMail(
                    "{$user->email}",
                    "$receiver_subject",
                    $receiver_content,
                    "{$user->firstname}"
                );
            } catch (Exception $e) {
                DB::rollback();
                Session::putFlash('danger', "Something went wrong");
            }
        }

        Redirect::back();
    }

    public function update_profile_by_admin()
    {

        echo "<pre>";
        if (/*Input::exists('update_user_profile')*/
            true
        ) {

            // print_r($_FILES);

            $user = User::find(MIS::dec_enc('decrypt', $_POST['user_id']));

            $this->validator()->check(Input::all(), array(


                'firstname' => [
                    // 'required'=> true,
                    'max' => '32',
                    'min' => '2',
                ],



                'username' => [
                    // 'required'=> true,
                    'min' => 1,
                    'one_word' => true,
                    'no_special_character' => true,
                    'replaceable' => 'User|' . $user->id,
                ],


                'email' => [
                    // 'required'=> true,
                    'email' => true,
                    'replaceable' => 'User|' . $user->id,
                ],

                'username' => [
                    // 'required'=> true,
                    'username' => true,
                    'replaceable' => 'User|' . $user->id,
                ],

                'lastname' => [
                    // 'required'=> true,
                    'max' => '32',
                    'min' => '2',
                ],

                'phone' => [
                    // 'required'=> true,
                    'max' => '32',
                    'min' => '2',
                ],

                'gender' => [
                    // 'required'=> true,
                ],


                'birthdate' => [
                    // 'required' => true,
                    'date' => 'Y-m-d',
                    'min_age' => '18',
                ],


                'country' => [
                    // 'required' => true,
                ],
                'address' => [
                    // 'required' => true,
                ],

            ));

            $auth = $user;

            if ($this->validator->passed()) {
                if ($auth->email != $_POST['email']) {

                    $auth->update(['email_verification' => md5(uniqid())]);
                }


                if ($auth->phone != $_POST['phone']) {

                    $auth->update(['phone_verification' => User::generate_phone_code_for($auth->id)]);
                }

                $posted = Input::all();
                $user->update($posted);


                Session::putFlash('success', 'Profile updated successfully!');
            } else {

                // print_r($this->validator->errors());
                Session::putFlash('danger', Input::inputErrors());
            }
        }


        Redirect::back();
    }

    public function update_profile()
    {
        echo "<pre>";
        if ($this->auth()->has_verified_profile()) {
            Session::putFlash('success', 'Profile is already approved. Please contact support');
            return;
        }


        if (/*Input::exists('update_user_profile')*/
            true
        ) {

            // print_r($_FILES);


            $this->validator()->check(Input::all(), array(


                'firstname' => [
                    'required' => true,
                    'max' => '32',
                    'min' => '2',
                ],



                'username' => [
                    // 'required'=> true,
                    'min' => 1,
                    'one_word' => true,
                    'no_special_character' => true,
                    'replaceable' => 'User|' . $this->auth()->id,
                ],


                'email' => [
                    'required' => true,
                    'email' => true,
                    'replaceable' => 'User|' . $this->auth()->id,
                ],

                'lastname' => [
                    'required' => true,
                    'max' => '32',
                    'min' => '2',
                ],

                'phone' => [
                    'required' => true,
                    'max' => '32',
                    'min' => '2',
                ],

                'webtalk_link' => [
                    'url' => true,
                ],

            ));

            $auth = $this->auth();

            if ($this->validator->passed()) {
                if ($auth->email != $_POST['email']) {
                    $auth->update(['email_verification' => md5(uniqid())]);
                }
                if ($auth->phone != $_POST['phone']) {
                    $auth->update(['phone_verification' => User::generate_phone_code_for($auth->id)]);
                }


                $unsets = array_filter(User::$not_changeable, function ($item) use ($auth) {
                    if ($auth[$item] == null) {
                        return false;
                    }
                    return true;
                });


                $posted = Input::all();
                foreach ($unsets as $key => $item) {
                    unset($posted[$item]);
                }

                $auth->update($posted);
                $auth->updateDetails([
                    // "webtalk_link" => Input::get('webtalk_link')
                    "viiral_legend_link" => Input::get('viiral_legend_link')
                ]);


                Session::putFlash('success', 'Profile updated successfully!');
            } else {

                Session::putFlash('danger', Input::inputErrors());
            }
        }


        Redirect::back();
    }

    public function update_profile_picture()
    {

        if ($_FILES['profile_pix']['error'] != 4) {
            $profile_pictures = $this->update_user_profile($_FILES);
            Session::putFlash('success', 'Profile Picture Updated Successfully.');
        }

        Redirect::back();
    }


    public function update_user_profile($file)
    {
        $directory = 'uploads/images/users/profile_pictures';
        $handle = new Upload($file['profile_pix']);

        //if it is image, generate thumbnail
        if (explode('/', $handle->file_src_mime)[0] == 'image') {

            // $handle->file_new_name_body = "{$this->auth()->username}";

            $handle->Process($directory);
            $original_file = $directory . '/' . $handle->file_dst_name;

            // we now process the image a second time, with some other settings
            $handle->image_resize = true;
            $handle->image_ratio_y = true;
            $handle->image_x = 50;

            // $handle->file_new_name_body = "{$this->auth()->username}";
            $handle->Process($directory);

            $resize_file = $directory . '/' . $handle->file_dst_name;
        }


        $profile_pictures = ['original_file' => $original_file, 'resize_file' => $resize_file];


        if ($this->auth()->profile_pix != Config::default_profile_pix()) {
            (new Upload($this->auth()->profile_pix))->clean();
        }

        if ($this->auth()->resized_profile_pix != Config::default_profile_pix()) {
            (new Upload($this->auth()->resized_profile_pix))->clean();
        }

        $this->auth()->update([
            'profile_pix' => $profile_pictures['original_file'],
            'resized_profile_pix' => $profile_pictures['resize_file']
        ]);


        return $profile_pictures;
    }
}
