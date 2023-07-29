<?php

namespace v2\Jobs\Jobs;

use MIS;
use Mailer;
use v2\Jobs\Traits\Job as TraitsJob;
use v2\Jobs\Contracts\Job as ContractsJob;

class SendNewRegistrationNoticeEmail implements ContractsJob
{
    use TraitsJob;
    public $user;

    public function __construct()
    {
    }


    public function setUpWith($user)
    {
        $this->user = $user;
        return $this;
    }


    public  function execute()
    {
        $mailer = new Mailer();

        $project_name = \Config::project_name();
        $domain = \Config::domain();

        $settings = \SiteSettings::site_settings();
        $noreply_email = $settings['noreply_email'];
        $support_email = $settings['support_email'];

        $email_message_for_admin = "
                           <p>Dear Admin, A new user with details just got signed up. Please check $project_name admin </p>

                           <p>Details:</p>
                           <p>
                           <h4> Sponsor</h4>
                           Name: " . $this->user->sponsor->fullname . "<br>
                           Phone Number: " . $this->user->sponsor->phone . "<br>
                           Email: " . $this->user->sponsor->email . "<br>

                           <h4> New User</h4>
                           Name: " . $this->user->fullname . "<br>
                           Phone Number: " . $this->user->phone . "<br>
                           Email: " . $this->user->email . "<br>
                           </p>

                           ";

        $email_message_for_admin = MIS::compile_email($email_message_for_admin);

        //admin
        $admin_email =   $mailer->sendMail(
            $support_email,
            "New Sign Up",
            $email_message_for_admin,
            "Notification"
        );

        return $admin_email;
    }
}
