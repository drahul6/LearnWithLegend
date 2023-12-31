<?php

use app\models\ProspectiveUser;
use v2\Models\Wallet;
use v2\Models\HeldCoin;
use v2\Models\HotWallet;
use v2\Models\Commission;
use v2\Models\Withdrawal;
use v2\Models\PayoutWallet;
use v2\Models\InvestmentPackage;
use v2\Shop\Payments\LivePay\LivePay;
use v2\Jobs\Jobs\SendEmailForNewLoginAlert;
use Illuminate\Database\Capsule\Manager as DB;
use v2\Models\Wallet\Journals;
use v2\Shop\Payments\CoinPayment\Coinpayment;

// use v2\Shop\Payments\Paypal\Paypal as cPaypal;
// use v2\Shop\Payments\Paypal\Subscription;


/**
 * this class is the default controller of our application,
 *
 */
class home extends controller
{


    public function __construct()
    {
    }



    public function order_settlement_journal_sample()
    {

        foreach ($items as $key => $item) {

            print_r($item['data']);
            $cost = $item['market_details']['price'] * $item['qty'];
            $pools_commission = max($item['data']['pools_commission'], $min_pools_commission);

            $amount_from_buyer = $cost;
            $amount_to_pools = $pools_commission * 0.01 * $cost;
            $amount_to_seller = $cost - $amount_to_pools;

            $buyer_account =  $user->getAccount('default');
            $seller = User::find($item['user_id']);

            $seller_account =  $seller->getAccount('default');


            //debit buyer
            $journal_lines[] = [
                "journal_id" => "",
                "chart_of_account_id" => $buyer_account->id,
                "chart_of_account_number" => $buyer_account->account_number,
                "description" => "payment for order #{$this->order->id}",
                "credit" => 0,
                "debit" => round($cost, 2),
            ];

            //credit seller
            $journal_lines[] = [
                "journal_id" => "",
                "chart_of_account_id" => $seller->id,
                "chart_of_account_number" => $seller->account_number,
                "description" => "received for {$item['market_details']['name']}#{$item['id']} on order #{$this->order->id}",
                "credit" => round($amount_to_seller, 2),
                "debit" => 0,
            ];

            $pools_account = ChartOfAccount::find(AccountManager::$journal_second_legs['monthly_pools_prize_account']);

            //credit pools_commission
            $journal_lines[] = [
                "journal_id" => "",
                "chart_of_account_id" => $pools_account->id,
                "chart_of_account_number" => $pools_account->account_number,
                "description" => "comm for {$item['market_details']['name']}#{$item['id']} on order #{$this->order->id}",
                "credit" => round($amount_to_pools, 2),
                "debit" => 0,
            ];
        }
    }

    public function test()
    {
        echo "<pre>checking";





        return;

        [
            "company_id" => 1,
            "notes" => "deposit",
            "currency" => "USD",
            "c_amount" => 10000,
            "amount" => 10000,
            "status" => 1,
            "journal_date" => 2023 - 05 - 07,
            "tag" => "deposit",
            "identifier" => "",
            "user_id" => "",
            "involved_accounts" => [
                [
                    "journal_id" => "",
                    "chart_of_account_id" => 2,
                    "chart_of_account_number" => 2001239312,
                    "description" => "deposit deposit USD10000 to #75-wealthdev",
                    "credit" => 0,
                    "debit" => 10000,
                    "created_at" => "2023-05-07",
                ],

                [
                    "journal_id" => "",
                    "chart_of_account_id" => 75,
                    "chart_of_account_number" => 5001510145,
                    "description" => "deposit deposit USD10000 From",
                    "credit" => 10000,
                    "debit" => 0,
                ]
            ]
        ];
    }






    public function contact_us()
    {


        // verify_google_captcha();

        echo "<pre>";

        print_r($_REQUEST);
        extract($_REQUEST);

        Input::exists();

        $project_name = Config::project_name();
        $domain = Config::domain();

        $settings = SiteSettings::site_settings();
        $noreply_email = $settings['noreply_email'];
        $support_email = $settings['support_email'];

        $email_message = "
			       <p>Dear Admin, Please respond to this support ticket on the $project_name admin </p>


			       <p>Details:</p>
			       <p>
			       Name: " . $full_name . "<br>
			       Phone Number: " . $phone . "<br>
			       Email: " . $email . "<br>
			       Comment: " . $comment . "<br>
			       </p>

			       ";


        $client = User::where('email', $_POST['email'])->first();
        $support_ticket = SupportTicket::create([
            'subject_of_ticket' => $_POST['comment'],
            'user_id' => $client->id,
            'customer_name' => $_POST['full_name'] ?? null,
            'customer_phone' => $_POST['phone'] ?? null,
            'customer_email' => $_POST['email'] ?? null,
        ]);

        $code = $support_ticket->id . MIS::random_string(7);
        $support_ticket->update(['code' => $code]);
        //log in the DB

        $client_email_message = "
			       Hello {$support_ticket->customer_name},

			       <p>We have received your inquiry and a support ticket with the ID: <b>{$support_ticket->code}</b>
			        has been generated for you. We would respond shortly.</p>

			      <p>You can click the link below to update your inquiry.</p>

			       <p><a href='{$support_ticket->link}'>{$support_ticket->link}</a></p>

	               <br />
	               <br />
	               <br />
	               <a href='$domain'> $project_name </a>";


        $support_email_address = $noreply_email;

        $client_email_message = MIS::compile_email($client_email_message);
        $email_message = MIS::compile_email($email_message);


        $mailer = new Mailer();

        $mailer->sendMail(
            $email_message,
            "$project_name Support - Ticket ID: $support_ticket->code",
            $client_email_message,
            "Support"
        );


        $response = $mailer->sendMail(
            "$support_ticket->customer_email",
            "$project_name Support - Ticket ID: $support_ticket->code",
            $client_email_message,
            $support_ticket->customer_name
        );

        Session::putFlash('success', "Message sent successfully.");

        Redirect::back();

        die();
    }


    /**
     * [flash_notification for application notifications]
     * @return [type] [description]
     */
    public function flash_notification()
    {
        header("Content-type: application/json");

        if (isset($_SESSION['flash'])) {
            echo json_encode($_SESSION['flash']);
        } else {
            echo "[]";
        }


        unset($_SESSION['flash']);
    }


    public function close_ticket()
    {
        $ticket = SupportTicket::where('code', $_REQUEST['ticket_code'])->first();
        $ticket->mark_as_closed();
        Redirect::back();
    }


    public function support_message()
    {

        $project_name = Config::project_name();
        $domain = Config::domain();

        $settings = SiteSettings::site_settings();
        $noreply_email = $settings['noreply_email'];
        $support_email = $settings['support_email'];


        $files = MIS::refine_multiple_files($_FILES['documents']);

        $ticket = SupportTicket::where('code', $_POST['ticket_code'])->first();
        $ticket->update(['status' => '0']);

        $message = SupportMessage::create([
            'ticket_id' => $ticket->id,
            'message' => $_POST['message'],
        ]);


        $message->upload_documents($files);

        $support_email_address = "$support_email";
        $_headers = "From: {$ticket->customer_email}";

        $client_email_message = "Dear Admin, Please respond to this support ticket on the admin <br>
	                            From:<br>
	                            $ticket->customer_name,<br>
	                            $ticket->customer_email,<br>
	                            $ticket->customer_phone,<br>
	                            Ticket ID: $ticket->code<br>
	                            <br>
	                             ";
        $client_email_message .= $message->message;

        $client_email_message = $ticket->compile_email($client_email_message);

        $mailer = new Mailer();

        $mailer->sendMail(
            "$support_email_address",
            "$project_name Support - Ticket ID: $ticket->code",
            $client_email_message,
            "Support"
        );

        Redirect::back();
    }


    public function index($page = null)
    {
        Redirect::to('login');

        switch ($page) {
            case 'supportmessages':

                $this->view('guest/support-messages');

                break;


            case null:

                // $this->view('guest/index');
                Redirect::to('login');
                break;

            default:

                $this->view('guest/404');
                break;
        }
    }


    public function about_us()
    {
        $this->view('guest/about_us');
    }


    public function how_it_works()
    {
        $this->view('guest/how-it-works');
    }

    public function contact()
    {
        $this->view('guest/contact');
    }

    public function faqs()
    {
        $this->view('guest/faq');
    }
}
