<?php

use v2\Classes\Queries;
use League\ISO3166\ISO3166;
use v2\Models\UserDocument;
use v2\Models\Wallet\Journals;
use app\models\ProspectiveUser;
use v2\Models\Wallet\ChartOfAccount;
use v2\Models\Wallet\Classes\AccountManager;
use v2\Models\Wallet\JournalInvolvedAccounts;
use Illuminate\Database\Capsule\Manager as DB;

class HomeController extends controller
{
    public function index()
    {
        $total_amount_shared = (new Queries)->totalAmountShared();
        $this->view('guest/index', get_defined_vars());
    }

    public function contact()
    {
        $this->view('guest/contact');
    }

    public function test()
    {


        echo "<pre>";

        $iso = (new ISO3166);
        print_r($iso->all());

        return;

        $order = Orders::find(23);
        $items  = $order->order_detail();

        $r = collect($items)->pluck('user_id')->unique()->implode(",");
        print_r($r);

        return;
        CMS::updateOrCreate(
            [
                'criteria' => "dashboard_ad_banner",
                'settings' => "dashboard_ad_banner",
                'description' => "Dashboard Ads Banner",
                'availability' => 1,
                'default_setting' => "",
            ],
            []
        );


        return;

        echo "<pre>";
        echo $user = User::find(5);

        print_r($user->verifyProfile());
        return;
        $doc = UserDocument::find(4);

        print_r($doc->data);

        return;
        $user = User::find(6);

        try {
            $user->auto_renew_subscription();
            //code...
        } catch (\Throwable $th) {
            print_r($th->getMessage());
        }


        // die;
        return;
        $subscription = SubscriptionOrder::Paid()->NotExpired()->groupBy('user_id');

        echo $sub;

        echo (int)$account = ChartOfAccount::find(19)->hasSufficientBalanceFor(88);
        return;
        AccountManager::withdrawal([
            'withdrawal_account' => 16,
            'amount' => 10,
            'currency' => 'GBP',
            'status' => 3,
            'collect_withdrawal_fee' => true,
            'narration' => "withdrawal",
            'journal_date' => null,
        ]);

        return;
        AccountManager::transfer([
            'sending_account' => 16,
            'receiving_account' => 17,
            'amount' => 10,
            'currency' => 'GBP',
            'status' => 3,
            // 'collect_transfer_fee' => false,
            // 'narration' => "deposit",
            'journal_date' => null,
        ]);

        return;
        AccountManager::deposit([
            'receiving_account' => 17,
            'amount' => 30,
            'currency' => 'GBP',
            'status' => 3,
            'collect_deposit_fee' => false,
            'narration' => "deposit",
            'journal_date' => null,
        ]);

        return;


        $journal = [
            'user_id' => null,
            'company_id' => 1,
            'amount' => null,
            'notes' => "test",
            'tag' => null,
            'identifier' => null,
            'currency' => "NGN",
            'status' => 1,
            'journal_date' => "2022-03-28",
        ];

        // return;
        $involved_accounts = [
            [
                'chart_of_account_id' => 2,
                'description' => 'test',
                'credit' => 0,
                'debit' => 2000,
            ],
            [
                'chart_of_account_id' => 17,
                'description' => 'test',
                'credit' => 2000,
                'debit' => 0,
            ],
        ];


        $journal =  Journals::create($journal);



        //update involved accounts
        $journal->remove_line_items();
        foreach ($involved_accounts as  $involved_account) {
            JournalInvolvedAccounts::create_involved_account($involved_account, $journal);
        }
        $journal->refresh();
        $journal->publish();

        return;
        $user->OpenAccounts([
            'account_name' => "wallet",
            'currency' => 'USD',
            'opening_balance' => 0,
            'account_type' => 6,   //normal savings account
            'description' => 'normal account regardless of type',
            'tag' => 'default',
        ]);
    }

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

        $auth = $this->auth();

        $settings = SiteSettings::site_settings();
        $noreply_email = $settings['noreply_email'];
        $support_email = $settings['support_email'];


        $files = MIS::refine_multiple_files($_FILES['documents']);

        $ticket = SupportTicket::where('code', $_POST['ticket_code'])->first();
        $ticket->update(['status' => '0']);

        $message = SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $auth->id ?? null,
            'message' => $_POST['message'],
        ]);


        $message->upload_documents($files);

        $support_email_address = "$support_email";
        $_headers = "From: {$ticket->customer_email}";

        $client_email_message = "Dear Admin,<br> Please respond to this support ticket on the admin <br>
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


        //send email to all in the thread.



        $recipients = array_merge(["$support_email_address" => "Support"], $ticket->Recipients);
        // unset($recipients[$auth->email]);

        $mailer->sendMail(
            $recipients,
            "$project_name Support - Ticket ID: $ticket->code",
            $client_email_message,
        );

        Redirect::back();
    }
}
