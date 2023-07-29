<?php

namespace v2\Jobs\Jobs;

use MIS;
use Mailer;
use v2\Jobs\Traits\Job as TraitsJob;
use v2\Jobs\Contracts\Job as ContractsJob;

class SendEmailForCreatedSupportMessage implements ContractsJob
{
    use TraitsJob;
    public $message;

    public function __construct()
    {
    }


    public function setUpWith($message)
    {
        $this->message = $message;
        return $this;
    }

    public function sendToUser()
    {
        $project_name = \Config::project_name();
        $domain = \Config::domain();

        $ticket = $this->message->supportTicket;


        $client_email_message = "<p>Hello $ticket->customer_name,</p>
		                             <p>{$this->message->message} </p>
		                         <p>You can respond by clicking this button <a href='{$ticket->link}'><button> Respond</button></a></p>
		                         <br><br>
		                         <p>Please note that to update this support request, you need to click the link above. Please do not click your email reply button as you would be replying to an unattended email. </p>

                                 <p></p>
                                 <p></p>
                                 <p>
                                    <a href='$domain'>$project_name</a>
                                 </p>
		                         ";

        $client_email_message = MIS::compile_email($client_email_message);
        $mailer = new Mailer();

        return $mailer->sendMail(
            $ticket->customer_email,
            "Support - Ticket ID: $ticket->code",
            $client_email_message,
            $ticket->customer_name
        );
    }

    public function sendToAdmin()
    {

        $project_name = \Config::project_name();
        $domain = \Config::domain();

        $settings = \SiteSettings::site_settings();
        $noreply_email = $settings['noreply_email'];
        $support_email = $settings['support_email'];
        $ticket = $this->message->supportTicket;

        $support_email_address = "$support_email";

        $client_email_message = "Dear Admin, Please respond to this support ticket on the admin <br>
	                            From:<br>
	                            $ticket->customer_name,<br>
	                            $ticket->customer_email,<br>
	                            $ticket->customer_phone,<br>
	                            Ticket ID: $ticket->code<br>
	                            <br>
	                             ";

        $client_email_message .= $this->message->message;

        $client_email_message = MIS::compile_email($client_email_message);

        $mailer = new Mailer();

        return  $mailer->sendMail(
            "$support_email_address",
            "$project_name Support - Ticket ID: $ticket->code",
            $client_email_message,
            "Support"
        );
    }

    public  function execute()
    {
        if ($this->message->messageIsBy('admin')) {
            return $this->sendToUser();
        }
        return $this->sendToAdmin();
    }
}
