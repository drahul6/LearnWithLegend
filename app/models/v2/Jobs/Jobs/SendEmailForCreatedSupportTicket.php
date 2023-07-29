<?php

namespace v2\Jobs\Jobs;

use MIS;
use Mailer;
use v2\Jobs\Traits\Job as TraitsJob;
use v2\Jobs\Contracts\Job as ContractsJob;

class SendEmailForCreatedSupportTicket implements ContractsJob
{
    use TraitsJob;
    public $support_ticket;

    public function __construct()
    {
    }


    public function setUpWith($support_ticket)
    {
        $this->support_ticket = $support_ticket;
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
			       <p>Dear Admin, Please respond to this support ticket on the $project_name admin </p>

			       <p>Details:</p>
			       <p>
			       Name: " . $this->support_ticket->customer_name . "<br>
			       Phone Number: " . $this->support_ticket->customer_phone . "<br>
			       Email: " . $this->support_ticket->customer_email . "<br>
			       Comment: " . $this->support_ticket->subject_of_ticket . "<br>
			       </p>

			       ";


        $client_email_message = "
			       Hello {$this->support_ticket->customer_name},

			       <p>We have received your inquiry and a support ticket with the ID: <b>{$this->support_ticket->code}</b>
			        has been generated for you. We would respond shortly.</p>

			      <p>You can click the link below to update your inquiry.</p>

			       <p><a href='{$this->support_ticket->link}'>{$this->support_ticket->link}</a></p>

	               <br />
	               <br />
	               <br />
	               <a href='$domain'> $project_name </a>


	               ";


        $client_email_message = MIS::compile_email($client_email_message);
        $email_message_for_admin = MIS::compile_email($email_message_for_admin);



        //admin
        $admin_email =   $mailer->sendMail(
            $support_email,
            "$project_name Support - Ticket ID: {$this->support_ticket->code}",
            $email_message_for_admin,
            "Support"
        );

        //user
        $user_email = $mailer->sendMail(
            "{$this->support_ticket->customer_email}",
            "$project_name Support - Ticket ID: {$this->support_ticket->code}",
            $client_email_message,
            $this->support_ticket->customer_name
        );


        return $user_email && $admin_email;
    }
}
