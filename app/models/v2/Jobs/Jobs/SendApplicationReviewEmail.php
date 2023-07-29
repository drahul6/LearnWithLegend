<?php

namespace v2\Jobs\Jobs;

use MIS;
use Config;
use Mailer;
use SiteSettings;
use v2\Jobs\Traits\Job as TraitsJob;
use v2\Jobs\Contracts\Job as ContractsJob;

class SendApplicationReviewEmail implements ContractsJob
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

        $domain = Config::domain() . "/" . Config::admin_url();
        $project_name = Config::project_name();

        $user = $this->user;
        $status = (int) $user->isActivated();

        switch ($status) {
            case 1: //approved

                $content = "Dear {$user->firstname},
                                    <p>Your account has been activated. 

                                    <p>&nbsp;</p>

                                    <p>Thank you for choosing to do business with us.</p>
                                    
                                    <p>&nbsp;</p>

                                    ";

                $admin_content = "
                                    <p><strong>NOTICE</strong></p>

                                    <p>{$user->fullname} account has been approved</p>

                                <p>Please <a href='$domain'>login </a>to confirm.</p>
                        ";

                break;


            case 0: //declined
                $comment = $user->adminComments()->where('status', 3)->last()->comment;

                $content = "Dear {$user->firstname},
                                <p>Your application for account activation has been declined.</p>


                                <p>&nbsp;</p>
                                Comment: $comment


                                <p>Thank you for choosing to do business with us.</p>
                                
                                <p>&nbsp;</p>

                                ";


                $admin_content = "
                                <p><strong>NOTICE</strong></p>

                                <p>{$user->fullname} account has been declined</p>

                            <p>Please <a href='$domain'>login </a>to confirm.</p>
                    ";


                break;


            default:

                return;

                break;
        }


        $settings = SiteSettings::site_settings();
        $noreply_email = $settings['noreply_email'];
        $support_email = $settings['support_email'];
        $notification_email = $settings['support_email'];


        $subject = "Account Activation Notification ";
        $mailer = new Mailer;

        $content = MIS::compile_email($content);
        $admin_content = MIS::compile_email($admin_content);


        //client
        $user_email =  $mailer->sendMail(
            "{$user->email}",
            "$subject",
            $content,
            "{$user->firstname}",
            "{$support_email}",
            "$project_name"
        );

        //ADMIN
        $admin_email = $mailer->sendMail(
            $notification_email,
            "$subject",
            $admin_content,
            "$project_name",
            "$support_email",
            "$project_name"
        );

        return $user_email && $admin_email;
    }
}
