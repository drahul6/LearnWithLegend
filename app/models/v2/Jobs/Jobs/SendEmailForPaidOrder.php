<?php

namespace v2\Jobs\Jobs;

use MIS;
use Mailer;
use v2\Jobs\Traits\Job as TraitsJob;
use v2\Jobs\Contracts\Job as ContractsJob;

class SendEmailForPaidOrder implements ContractsJob
{
    use TraitsJob;
    public $order;

    public function __construct()
    {
    }


    public function setUpWith($order)
    {
        $this->order = $order;
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

        $order = $this->order;
        $body = MIS::buildView('emails/order_delivery', compact('order'));

        $user_email = $mailer->sendMail(
            "{$this->order->Buyer->email}",
            "Delivery - Order Id: #{$this->order->id}",
            $body,
            $this->order->Buyer->fullname
        );


        return $user_email;
    }
}
