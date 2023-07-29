<?php


namespace v2\Models\Wallet\Classes;

use MIS;

use User;
use Input;
use Config;
use Session;
use Exception;
use Validator;
use SiteSettings;
use v2\Classes\ExchangeRate;
use v2\Models\Wallet\Journals;
use v2\Models\Wallet\ChartOfAccount;
use v2\Models\Wallet\CompanyAccountType;
use v2\Models\Wallet\JournalInvolvedAccounts;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * undocumented class
 */

class AccountManager
{

    public $user;
    public $company;
    public $company_id;





    public function __construct()
    {

        $this->company = ChartOfAccount::getCompany();
        $this->company_id = $this->company->id;
    }

    /**
     * chart of account ids for listed transactions
     *
     * @var array
     * @return string
     */
    public static function journal_second_legs($key)
    {
        $journal_second_legs = [

            /*  "deposit" => 2, // company account for deposit transactions
            "transfer_fee" => 18, // 
            "withdrawal_fee" => 22, //
            "withdrawal" => 2, // company account to pay withdrawal requests
            "membership_fee" => 21, //
            "company_donations_to_pool" => 38, //expense
            "monthly_pools_prize_account" => 39, //liability
            "ecommerce_escrow" => 76, //liability
        */



            "deposit" => $_ENV['deposit'],
            "transfer_fee" => $_ENV['transfer_fee'],
            "withdrawal_fee" => $_ENV['withdrawal_fee'],
            "withdrawal" => $_ENV['withdrawal'],
            "membership_fee" => $_ENV['membership_fee'],
            "company_donations_to_pool" => $_ENV['company_donations_to_pool'],
            "monthly_pools_prize_account" => $_ENV['monthly_pools_prize_account'],
            "ecommerce_escrow" => $_ENV['ecommerce_escrow'],
        ];

        return $journal_second_legs[$key];
    }

    public static function getPoolsSummary()
    {
        $total_persons = User::ActiveUsers()->count();

        //get total premium members



        $pools_prize_account = ChartOfAccount::find(self::journal_second_legs('monthly_pools_prize_account'));

        $this_month = date("Y-m-t");
        $pools_prize_account_balance = $pools_prize_account->get_balance($this_month);

        $this_month_pools = $pools_prize_account_balance['account_currency']['available_balance'];
        $value_per_person = round($this_month_pools / $total_persons, 2);

        // $response = get_defined_vars();
        $response = compact('this_month_pools', 'value_per_person', 'total_persons');

        return $response;
    }


    public function OpenAccountByTag($tag)
    {

        $accounts = [
            "default" => [
                'account_name' => "wallet",
                'currency' => 'USD',
                'opening_balance' => 0,
                'account_type' => 6,   //normal savings account
                'description' => 'normal account regardless of type',
                'tag' => 'default',
            ],

        ];

        $account = $accounts[$tag];

        return $this->OpenAccounts($account);
    }


    /**
     * This opens a new wallet for a user
     *
     */
    public function OpenAccounts(array $template = [])
    {
        $data = [
            [
                'account_type' => 2, //comany based account type id
                'account_name' => "Savings Account",
                'username' => 'by_manager',
                'opening_balance' =>  0.0,
                'currency' => 'USD',
                'tag' => 'savings_account',
                'description' => '',
            ]
        ];


        $data = array_map(function ($item) use ($template) {
            return  array_merge($item, $template);
        }, $data);

        $by_manager =  [
            'username' => $this->user->username
        ];

        $resolved_data = array_map(function ($item) use ($by_manager) {
            foreach ($item as $key => $value) {
                if ($value === 'by_manager') {
                    $item[$key] =  $by_manager[$key];
                }
            }
            return $item;
        }, $data);


        foreach ($resolved_data as $key => $post) {

            $validator = new Validator;
            $owner = User::where('username', $post['username'])->first();
            $rules = ChartOfAccount::$validator_rules;
            $account_type = CompanyAccountType::find($post['account_type']);

            if ($owner) {
                $composite = [
                    'model' => ChartOfAccount::class,
                    'name' => 'Account',
                    'columns_value' => [
                        'account_name' => $post['account_name'],
                        'owner_id' => $owner->id,
                        'company_customised_account_id' => $account_type->id,
                    ],
                ];

                $rules['composite_unique'] = $composite;
            } else {
                $rules['account_name']['unique'] = ChartOfAccount::class;
            }

            $validator->check($post, $rules);

            if (!$validator->passed()) {
                Session::putFlash('danger', Input::inputErrors());
                return false;
            }

            DB::beginTransaction();

            try {
                $account_type = CompanyAccountType::find($post['account_type']);

                $basic_account_type_id = $account_type->basic_account->id;
                $account_code = ChartOfAccount::generate_account_code($this->company_id, $account_type);
                $account_number = ChartOfAccount::generate_account_number($this->company_id, $account_type);


                $new_chart = ChartOfAccount::create([
                    'company_customised_account_id'        => $account_type->id,
                    'user_id'                    => $this->id ?? null,
                    'owner_id'                    => $owner->id ?? null,
                    'company_id'                => $this->company_id,
                    'account_name'                => $post['account_name'],
                    'account_code'                => $account_code,
                    'account_number'            => $account_number,
                    'tag'                        => $post['tag'],
                    'description'                => $post['description'],
                    'currency'                    => $post['currency'] ?? ChartOfAccount::$base_currency,
                ]);

                $new_chart->setOpeningBalance($post['opening_balance']);

                DB::commit();
                // Session::putFlash('success', 'Chart of Account Created Successfully.');


                return $new_chart;
            } catch (\Exception $e) {
                print_r($e->getMessage());
                DB::rollback();
                // something went wrong
            }
        }

        return false;

        return;
        foreach ($resolved_data as $key => $post) {
            $url = Config::domain() . "/accounts/create_chart_of_accounts";
            $response = MIS::make_post($url, $post);
            return  $response;
        }
    }



    public static function prepareLineItems($type, $post_schedule, $account)
    {

        $line_items = array_map(function ($item) use ($account, $type) {
            $item = [
                'chart_of_account_number' => $account->account_number,
                'credit' => $item,
                'debit' => $item,
            ];


            switch ($type) {
                case 'credit':
                    $item['debit'] = 0;
                    break;
                case 'debit':
                    $item['credit'] = 0;
                    # code...
                    break;

                default:
                    # code...
                    break;
            }


            return $item;
        }, $post_schedule);

        return $line_items;
    }

    public static function fetchDescription($post_type)
    {
        $i_ref = random_int(10, 99);
        $e_ref = random_int(100, 999);

        $bank  = \Config::project_name();


        $credit = [
            "Transfer -$i_ref*****$e_ref",
            "Internet Transfer -$i_ref*****$e_ref",
            "Deposit -$i_ref*****$e_ref",
            "Inflow -$i_ref*****$e_ref",
            "Original Credit -$i_ref*****$e_ref",
            "PAYMENT -$i_ref*****$e_ref",
        ];

        $debit = [
            "Debit WEB ID $bank -$i_ref*****$e_ref",
            "Direct Debit -$i_ref*****$e_ref",
            "$bank ATM -$i_ref*****$e_ref",
            "Bill Payment -$i_ref*****$e_ref",
            "POS -$i_ref*****$e_ref",
            "Online Web POS -$i_ref*****$e_ref",
            "Cheque -$i_ref*****$e_ref",
            "Access Cheque -$i_ref*****$e_ref",
            "Interac Purchase -$i_ref*****$e_ref",
            "Purchase Order -$i_ref*****$e_ref",
            "Debit Terminal -$i_ref*****$e_ref",
            "Transfer to *** for Transaction WEB ID -$i_ref*****$e_ref",
        ];



        $descriptions = compact('credit', 'debit')[$post_type];

        shuffle($descriptions);

        return $descriptions[0];
    }


    public static function preparePostableJournal(array $journals, $account)
    {

        $postable_journals = array_map(function ($date, $line_item) use ($account) {
            $type = $line_item['credit'] == 0 ? 'debit' : 'credit';
            $description = self::fetchDescription($type);
            $journal = [
                'id' => '',
                'company_id' => $account->company_id,
                'notes' => 'notes',
                'status' => 1,
                'journal_date' => $date,
                'created_at' => $date,
                'updated_at' => $date,
                'tag' => 'generated',
                'identifier' => null,
                'createddate' => $date,
                'involved_accounts' => [
                    [
                        'journal_id' => '',
                        'chart_of_account_id' => null,
                        'chart_of_account_number' => $line_item['chart_of_account_number'],
                        'description' => $description,
                        'credit' => $line_item['credit'],
                        'debit' => $line_item['debit'],
                        'created_at' => $date,
                    ],

                    [
                        'journal_id' => '',
                        'chart_of_account_id' => null,
                        'chart_of_account_number' => '2001175692', //company cash
                        'description' => 'note',
                        'credit' => $line_item['debit'],
                        'debit' => $line_item['credit'],
                        'created_at' => $date,
                    ],

                ],
                'published_status' => 3,  //final publish


            ];
            return $journal;
        }, $journals['journal_dates'], $journals['line_items']);

        return $postable_journals;
    }




    /*
        from_wallet
        to_wallet    
        exchange_fee    
    */
    public static function exchange()
    {
    }




    /**
     * Post Transfer Journal from one account to the other
     * $transfer = [
            sending_account => integer
            receiving_account => integer
            amount => float
            currency=> string | optional
            narration => string 

            collect_transfer_fee => sender|receiver|false
            journal_date => date | optional
            status => pending|complete|draft|final
            tag => string | optional
            identifier => string | optional
        ]
    
     * @param array $transfer
     * @return void
     */
    public static function transfer(array $transfer)
    {
        // print_r($transfer);
        $journal_date = $transfer['journal_date'] ?? date("Y-m-d");


        $base_currency = ChartOfAccount::$base_currency;
        $currency = $transfer['currency'] ?? $base_currency;
        $amount = $transfer['amount'];


        //transfer fee
        $collect_transfer_fee = $transfer['collect_transfer_fee'];
        if ($collect_transfer_fee) {
            $rules = SiteSettings::find_criteria('rules_settings')->settingsArray;
            $transfer_fee = $rules['user_transfer_fee_percent'];

            //convert fee to journal currency
            $exchange = new ExchangeRate;
            $fee_conversion = $exchange->setFrom($base_currency)
                ->setTo($currency)
                ->setAmount($transfer_fee)
                ->getConversion();

            $transfer_fee = ChartOfAccount::round($fee_conversion['destination_value']);
        } else {
            $transfer_fee = 0;
        }




        $sending_account = ChartOfAccount::findNumber($transfer['sending_account']) ?? ChartOfAccount::find($transfer['sending_account']);
        if ($sending_account->is_credit_balance()) {

            $sending_credit = 0;
            $sending_debit = $amount + $transfer_fee;

            $receiving_credit = $amount;
            $receiving_debit = 0;
        } else {
            $sending_credit = $amount;
            $sending_debit = 0;

            $receiving_credit = 0;
            $receiving_debit = $amount;
        }


        $receiving_account = ChartOfAccount::findNumber($transfer['receiving_account']) ?? ChartOfAccount::find($transfer['receiving_account']);


        $narration = $transfer['narration'] ?? null;
        $sending_amount = max($sending_credit, $sending_debit);

        //confirm balance sufficiency
        if (!$sending_account->hasSufficientBalanceFor($sending_amount, $currency)) {
            Session::putFlash("danger", "Insufficient funds");
            // throw new Exception("Insufficient Balance", 1);
            return false;
        }


        $transfer_fee_decription = $collect_transfer_fee ? ", transfer Fee$transfer_fee" : "";
        @$send_description = "$narration Transfer {$currency}$amount to {$receiving_account->id}-{$receiving_account->owner->username}";

        $sending_line = [
            "journal_id" => "",
            "chart_of_account_id" => $sending_account->id ?? null,
            "chart_of_account_number" => $sending_account->account_number ?? null,
            "description" => "$send_description",
            "credit" => $sending_credit,
            "debit" => $sending_debit,
            "created_at" => $journal_date
        ];


        $received_amount = max($receiving_credit, $receiving_debit);
        @$description = "$narration Received {$currency}$received_amount From {$sending_account->id}-{$sending_account->owner->username}";



        $receiving_line = [
            "journal_id" => "",
            "chart_of_account_id" => $receiving_account->id ?? null,
            "chart_of_account_number" => $receiving_account->account_number ?? null,
            "description" => "$description",
            "credit" => $receiving_credit,
            "debit" => $receiving_debit,
        ];


        $charges = [];
        $company_account = self::journal_second_legs('transfer_fee');

        if ($collect_transfer_fee != false) {

            $charges[] = [
                "journal_id" => "",
                "chart_of_account_id" => $company_account,
                "chart_of_account_number" => null,
                "description" => "Transfer Fee {$base_currency}$transfer_fee",
                "credit" => $transfer_fee,
                "debit" => 0,
            ];
            /* 
            switch ($collect_transfer_fee) {
                case 'sender':

                    $charges[] = [
                        "journal_id" => "",
                        "chart_of_account_id" => $sending_account->id,
                        "chart_of_account_number" => $sending_account->account_number,
                        "description" => "Transfer Fee {$base_currency}$transfer_fee",
                        "credit" => 0,
                        "debit" => $transfer_fee,
                    ];

                    break;

                case 'receiver':
                    $charges[] = [
                        "journal_id" => "",
                        "chart_of_account_id" => $receiving_account->id,
                        "chart_of_account_number" => $receiving_account->account_number,
                        "description" => "Transfer Fee {$base_currency}$transfer_fee",
                        "credit" => 0,
                        "debit" => $transfer_fee,
                    ];

                    break;
                default:
                    break;
            } */
        }


        $involved_accounts = array_merge([
            $sending_line,
            $receiving_line,
        ], $charges);


        $involved_accounts = array_filter($involved_accounts, function ($line) {
            return !empty($line);
        });

        $journal = [
            "id" => "",
            "company_id" => 1,
            "notes" => $transfer['narration'] ?? null,
            "currency" => $transfer['currency'] ?? null,
            "c_amount" => $transfer['amount'],
            "status" => 3,
            "journal_date" => $journal_date,
            "tag" => $transfer['tag'] ?? null,
            "identifier" => $transfer['identifier'] ?? null,
            "user_id" => $transfer['user_id'] ?? null,
            "involved_accounts" => $involved_accounts,
        ];


        //   return;
        //sending and receiving account must belong to same account


        if ($sending_account->account_type->id != $receiving_account->account_type->id) {
            return false;
        }



        $response = self::postJournal($journal);
        return $response;
    }





    /* 
        withdrawal_account
        amount
        currency
        narration
        status
        date|optional
        collect_withdrawal_fee ||true or false
*/


    /* 
    membership_account
    amount
    currency
    narration
    status
    date|optional
    */
    public static function membership(array $journal)
    {

        $journal_date = $journal['journal_date'] ?? date("Y-m-d");


        $base_currency = ChartOfAccount::$base_currency;
        $currency = $journal['currency'] ?? $base_currency;
        $amount = $journal['amount'];

        $revenue_amount = 20 * 0.01 * $amount;
        $pools_amount = $amount -  $revenue_amount;


        //pools donation line
        $company_monthly_pools_prize_account = ChartOfAccount::find(self::journal_second_legs('monthly_pools_prize_account'));
        $company_monthly_pools_prize_account_credit = $pools_amount;

        @$journal_description = "for pools {$currency}$pools_amount";
        $company_pools_donation_line = [
            "journal_id" => "",
            "chart_of_account_id" => $company_monthly_pools_prize_account->id ?? null,
            "chart_of_account_number" => $company_monthly_pools_prize_account->account_number ?? null,
            "description" => "$journal_description",
            "credit" => $company_monthly_pools_prize_account_credit,
            "debit" => 0,
        ];



        //revenue account
        $company_account = ChartOfAccount::find(self::journal_second_legs('membership_fee'));
        $company_credit = $revenue_amount;

        $narration = $journal['narration'];
        @$journal_description = "$narration membership {$currency}$revenue_amount";
        $company_revenue_line = [
            "journal_id" => "",
            "chart_of_account_id" => $company_account->id ?? null,
            "chart_of_account_number" => $company_account->account_number ?? null,
            "description" => "$journal_description",
            "credit" => $company_credit,
            "debit" => 0,
        ];



        //users account
        $paying_account = ChartOfAccount::findNumber($journal['membership_account']) ?? ChartOfAccount::find($journal['membership_account']);
        $journal_debit = $amount;
        $journal_amount = max(0, $journal_debit);
        @$description = "$narration membership {$currency}$journal_amount From #{$paying_account->id}";

        $journal_line = [
            "journal_id" => "",
            "chart_of_account_id" => $paying_account->id ?? null,
            "chart_of_account_number" => $paying_account->account_number ?? null,
            "description" => "$description",
            "credit" => 0,
            "debit" => $journal_debit,
        ];


        //ensure sufficiency
        if (!$paying_account->hasSufficientBalanceFor($journal_amount, $currency)) {
            Session::putFlash("danger", "insufficient Funds");
            throw new Exception("Insufficient Balance", 1);
            return false;
        }



        $involved_accounts = [
            $company_revenue_line,
            $company_pools_donation_line,
            $journal_line,
        ];

        $involved_accounts = array_filter($involved_accounts, function ($line) {
            return !empty($line);
        });

        $journal = [
            "id" => "",
            "company_id" => 1,
            "notes" => $journal['narration'] ?? 'membership',
            "currency" => $journal['currency'] ?? $base_currency,
            "c_amount" => $journal['amount'],
            "status" => $journal['status'] ?? 3,
            "journal_date" => $journal_date,
            "tag" => 'membership' ?? null,
            "identifier" => $journal['identifier'] ?? null,
            "user_id" => $journal['user_id'] ?? null,
            "involved_accounts" => $involved_accounts,
        ];

        $response = self::postJournal($journal);
        return $response;
    }


    /* 
'donating_account' => $donating_account->id,
'amount' => $amount,
'tag' => "donation",
'narration' => "donation",
'journal_date' => null,
*/

    public static function donateToPool(array $journal)
    {

        $journal_date = $journal['journal_date'] ?? date("Y-m-d");


        $base_currency = ChartOfAccount::$base_currency;
        $currency = $journal['currency'] ?? $base_currency;
        $amount = $journal['amount'];




        //pools donation line
        $company_monthly_pools_prize_account = ChartOfAccount::find(self::journal_second_legs('monthly_pools_prize_account'));
        $company_monthly_pools_prize_account_credit = $amount;

        @$journal_description = "received for pools {$currency}$amount";

        $pools_donation_account_line = [
            "journal_id" => "",
            "chart_of_account_id" => $company_monthly_pools_prize_account->id ?? null,
            "chart_of_account_number" => $company_monthly_pools_prize_account->account_number ?? null,
            "description" => "$journal_description",
            "credit" => $company_monthly_pools_prize_account_credit,
            "debit" => 0,
        ];



        //users account
        $donating_account = $journal['donating_account'];
        $journal_debit = $amount;
        $journal_amount = max(0, $journal_debit);
        @$description = "donation {$currency}$journal_amount to monthly pool";

        $donation_line = [
            "journal_id" => "",
            "chart_of_account_id" => $donating_account->id ?? null,
            "chart_of_account_number" => $donating_account->account_number ?? null,
            "description" => "$description",
            "credit" => 0,
            "debit" => $journal_debit,
        ];


        //ensure sufficiency
        if (!$donating_account->hasSufficientBalanceFor($journal_amount, $currency)) {
            Session::putFlash("danger", "insufficient Funds");
            throw new Exception("Insufficient Balance", 1);
            return false;
        }



        $involved_accounts = [
            $pools_donation_account_line,
            $donation_line,
        ];

        $involved_accounts = array_filter($involved_accounts, function ($line) {
            return !empty($line);
        });

        $journal = [
            "id" => "",
            "company_id" => 1,
            "notes" => $journal['narration'] ?? 'donation',
            "currency" => $journal['currency'],
            "c_amount" => $journal['amount'],
            "status" => $journal['status'] ?? 3,
            "journal_date" => $journal_date,
            "tag" => 'donation' ?? null,
            "identifier" => $journal['identifier'] ?? null,
            "user_id" => $journal['user_id'] ?? null,
            "involved_accounts" => $involved_accounts,
        ];


        $response = self::postJournal($journal);
        return $response;
    }



    public static function donateToPoolByAdmin(array $journal)
    {

        $journal_date = $journal['journal_date'] ?? date("Y-m-d");


        $base_currency = ChartOfAccount::$base_currency;
        $currency = $journal['currency'] ?? $base_currency;
        $amount = $journal['amount'];


        //pools donation line
        $company_monthly_pools_prize_account = ChartOfAccount::find(self::journal_second_legs('monthly_pools_prize_account'));
        $company_monthly_pools_prize_account_credit = $amount;

        @$journal_description = "admin for pools {$currency}$amount";

        if ($journal['type'] == 'credit') {
            $pools_credit =  $amount;
            $pools_debit =  0;

            $expense_credit = 0;
            $expense_debit = $amount;
        } else {
            $pools_credit =  0;
            $pools_debit =  $amount;

            $expense_credit = $amount;
            $expense_debit = 0;
        }

        $pools_donation_account_line = [
            "journal_id" => "",
            "chart_of_account_id" => $company_monthly_pools_prize_account->id ?? null,
            "chart_of_account_number" => $company_monthly_pools_prize_account->account_number ?? null,
            "description" => "$journal_description",
            "credit" => $pools_credit,
            "debit" => $pools_debit,
        ];



        //company expense account
        $company_expense_donations_to_pool = ChartOfAccount::find(self::journal_second_legs('company_donations_to_pool'));
        $journal_debit = $amount;
        $journal_amount = max(0, $journal_debit);
        @$description = "company donation {$currency}$journal_amount to monthly pool";

        $donation_expense_line = [
            "journal_id" => "",
            "chart_of_account_id" => $company_expense_donations_to_pool->id ?? null,
            "chart_of_account_number" => $company_expense_donations_to_pool->account_number ?? null,
            "description" => "$description",
            "credit" => $expense_credit,
            "debit" => $expense_debit,
        ];


        //ensure sufficiency
        /*
           if (!$company_expense_donations_to_pool->hasSufficientBalanceFor($journal_amount, $currency)) {
            Session::putFlash("danger", "insufficient Funds");
            throw new Exception("Insufficient Balance", 1);
            return false;
        }
        */


        $involved_accounts = [
            $pools_donation_account_line,
            $donation_expense_line,
        ];

        $involved_accounts = array_filter($involved_accounts, function ($line) {
            return !empty($line);
        });

        $journal = [
            "id" => "",
            "company_id" => 1,
            "notes" => $journal['narration'] ?? 'donation',
            "currency" => $journal['currency'],
            "c_amount" => $journal['amount'],
            "status" => $journal['status'] ?? 3,
            "journal_date" => $journal_date,
            "tag" => 'donation' ?? null,
            "identifier" => $journal['identifier'] ?? null,
            "user_id" => $journal['user_id'] ?? null,
            "involved_accounts" => $involved_accounts,
        ];


        $response = self::postJournal($journal);
        return $response;
    }


    public static function payPoolsCommission(array $journal)
    {

        $journal_date = $journal['journal_date'] ?? date("Y-m-d");


        $base_currency = ChartOfAccount::$base_currency;
        $currency = $journal['currency'] ?? $base_currency;
        $amount = $journal['amount'];



        /* 
        
            'receiver' => $receiver,
            'amount' => $offset_amount,
            'identifier' => $identifier,
            'description' => $comment,
        */

        //pools donation line
        $company_monthly_pools_prize_account = ChartOfAccount::find(self::journal_second_legs('monthly_pools_prize_account'));
        @$journal_description = "{$journal['narration']} {$journal["identifier"]}";

        $pools_credit =  0;
        $pools_debit =  $amount;

        $pools_donation_account_line = [
            "journal_id" => "",
            "chart_of_account_id" => $company_monthly_pools_prize_account->id ?? null,
            "chart_of_account_number" => $company_monthly_pools_prize_account->account_number ?? null,
            "description" => "$journal_description",
            "credit" => $pools_credit,
            "debit" => $pools_debit,
        ];



        //user receiver account
        $receiver_credit = $amount;
        $receiver_debit = 0;

        $receiver_account = $journal['receiver']->getAccount('default');
        $journal_debit = $amount;
        $journal_amount = max(0, $journal_debit);
        @$description = "{$journal['narration']} {$journal["identifier"]}";

        $receiver_line = [
            "journal_id" => "",
            "chart_of_account_id" => $receiver_account->id ?? null,
            "chart_of_account_number" => $receiver_account->account_number ?? null,
            "description" => "$description",
            "credit" => $receiver_credit,
            "debit" => $receiver_debit,
        ];


        //ensure sufficiency
        /*
           if (!$company_expense_donations_to_pool->hasSufficientBalanceFor($journal_amount, $currency)) {
            Session::putFlash("danger", "insufficient Funds");
            throw new Exception("Insufficient Balance", 1);
            return false;
        }
        */


        $involved_accounts = [
            $pools_donation_account_line,
            $receiver_line,
        ];

        $involved_accounts = array_filter($involved_accounts, function ($line) {
            return !empty($line);
        });

        $journal = [
            "id" => "",
            "company_id" => 1,
            "notes" => $journal['narration'] ?? 'pools_commission',
            "currency" => $journal['currency'] ?? $currency,
            "c_amount" => $journal['amount'],
            "status" => $journal['status'] ?? 3,
            "journal_date" => $journal_date,
            "tag" => 'pools_commission' ?? null,
            "identifier" => $journal['identifier'] ?? null,
            "user_id" => $journal['user_id'] ?? null,
            "involved_accounts" => $involved_accounts,
        ];


        $response = self::postJournal($journal);
        return $response;
    }



    public static function withdrawal(array $withdrawal)
    {

        $journal_date = $withdrawal['journal_date'] ?? date("Y-m-d");


        $base_currency = ChartOfAccount::$base_currency;
        $currency = $withdrawal['currency'] ?? $base_currency;
        $amount = $withdrawal['amount'];


        //withdrawal fee
        $collect_withdrawal_fee = $withdrawal['collect_withdrawal_fee'] ?? false;
        if ($collect_withdrawal_fee) {
            $rules = SiteSettings::find_criteria('rules_settings')->settingsArray;
            // $withdrawal_fee = $rules['withdrawal_fee'] ?? 0;
            $withdrawal_fee = $rules['withdrawal_fee_percent'] * 0.01 * $amount;

            //convert fee to journal currency
            $exchange = new ExchangeRate;
            $fee_conversion = $exchange->setFrom($base_currency)
                ->setTo($currency)
                ->setAmount($withdrawal_fee)
                ->getConversion();

            $withdrawal_fee = ChartOfAccount::round($fee_conversion['destination_value']);
        } else {
            $withdrawal_fee = 0;
        }


        $company_account_id = self::journal_second_legs('withdrawal');

        $company_account = ChartOfAccount::find($company_account_id);
        $company_credit = $amount - $withdrawal_fee;
        $company_debit = 0;

        $withdrawal_credit = 0;
        $withdrawal_debit = $amount;


        $withdrawal_account = ChartOfAccount::findNumber($withdrawal['withdrawal_account']) ?? ChartOfAccount::find($withdrawal['withdrawal_account']);
        $narration = $withdrawal['narration'];


        $withdrawal_fee_decription = $collect_withdrawal_fee ? ", withdrawal Fee$withdrawal_fee" : "";
        @$withdrawal_description = "$narration withdrawal {$currency}$amount from #{$company_account->id} to #{$withdrawal_account->id}";

        $company_line = [
            "journal_id" => "",
            "chart_of_account_id" => $company_account->id ?? null,
            "chart_of_account_number" => $company_account->account_number ?? null,
            "description" => "$withdrawal_description",
            "credit" => $company_credit,
            "debit" => $company_debit,
        ];


        $withdrawal_amount = max($withdrawal_credit, $withdrawal_debit);
        @$description = "$narration withdrawal {$currency}$withdrawal_amount From #{$withdrawal_account->id}";

        $withdrawal_line = [
            "journal_id" => "",
            "chart_of_account_id" => $withdrawal_account->id ?? null,
            "chart_of_account_number" => $withdrawal_account->account_number ?? null,
            "description" => "$description",
            "credit" => $withdrawal_credit,
            "debit" => $withdrawal_debit,
        ];


        //ensure sufficiency
        if (!$withdrawal_account->hasSufficientBalanceFor($withdrawal_amount, $currency)) {
            Session::putFlash("danger", "insufficient Funds");
            throw new Exception("Insufficient Balance", 1);
            return false;
        }


        $withdrawal_fee_company_account_id = self::journal_second_legs('withdrawal_fee');

        $charges = [];
        if ($collect_withdrawal_fee != false) {

            $charges[] = [
                "journal_id" => "",
                "chart_of_account_id" => $withdrawal_fee_company_account_id,
                "chart_of_account_number" => null,
                "description" => "withdrawal fee {$base_currency}$withdrawal_fee",
                "credit" => $withdrawal_fee,
                "debit" => 0,
            ];


            /* 
            $charges[] = [
                "journal_id" => "",
                "chart_of_account_id" => $withdrawal_account->id,
                "chart_of_account_number" => $withdrawal_account->account_number,
                "description" => "withdrawal Fee {$base_currency}$withdrawal_fee",
                "credit" => 0,
                "debit" => $withdrawal_fee,
            ]; */
        }


        $involved_accounts = array_merge(
            [
                $company_line,
                $withdrawal_line,
            ],
            $charges
        );


        $involved_accounts = array_filter($involved_accounts, function ($line) {
            return !empty($line);
        });

        $journal = [
            "id" => "",
            "company_id" => 1,
            "notes" => $withdrawal['narration'] ?? 'withdrawal',
            "currency" => $withdrawal['currency'],
            "c_amount" => $withdrawal['amount'],
            "status" => $withdrawal['status'] ?? 3,
            "journal_date" => $journal_date,
            "tag" => 'withdrawal' ?? null,
            "identifier" => $withdrawal['identifier'] ?? null,
            "involved_accounts" => $involved_accounts,
            "user_id" => $withdrawal['user_id'] ?? null,
        ];

        $response = self::postJournal($journal);
        // $response->updateDetailsByKey('withdrawal_method', $withdrawal['method']);


        return $response;
    }


    /* 
    [
        receiving_account
        amount
        currency
        narration
        status
        date|optional
        collect_deposit_fee ||true or false
    ]
     */
    public static function deposit(array $deposit)
    {

        $journal_date = $deposit['journal_date'] ?? date("Y-m-d");


        $base_currency = ChartOfAccount::$base_currency;
        $currency = $deposit['currency'] ?? $base_currency;
        $amount = $deposit['amount'];


        //deposit fee
        $collect_deposit_fee = $deposit['collect_deposit_fee'] ?? false;
        if ($collect_deposit_fee) {
            $rules = SiteSettings::find_criteria('rules_settings')->settingsArray;
            $deposit_fee = $rules['deposit_fee'];

            //convert fee to journal currency
            $exchange = new ExchangeRate;
            $fee_conversion = $exchange->setFrom($base_currency)
                ->setTo($currency)
                ->setAmount($deposit_fee)
                ->getConversion();

            $deposit_fee = ChartOfAccount::round($fee_conversion['destination_value']);
        } else {
            $deposit_fee = 0;
        }


        $company_account = self::journal_second_legs('deposit');

        $sending_account = ChartOfAccount::find($company_account);
        if ($sending_account->is_debit_balance()) {

            $sending_credit = 0;
            $sending_debit = $amount;

            $receiving_credit = $amount;
            $receiving_debit = 0;
        } else {
            $sending_credit = $amount;
            $sending_debit = 0;

            $receiving_credit = 0;
            $receiving_debit = $amount;
        }


        $receiving_account = ChartOfAccount::findNumber($deposit['receiving_account']) ?? ChartOfAccount::find($deposit['receiving_account']);
        $narration = $deposit['narration'];


        $deposit_fee_decription = $collect_deposit_fee ? ", deposit Fee$deposit_fee" : "";
        @$send_description = "$narration deposit {$currency}$amount to #{$receiving_account->id}-{$receiving_account->owner->username}";

        $sending_line = [
            "journal_id" => "",
            "chart_of_account_id" => $sending_account->id ?? null,
            "chart_of_account_number" => $sending_account->account_number ?? null,
            "description" => "$send_description",
            "credit" => $sending_credit,
            "debit" => $sending_debit,
            "created_at" => $journal_date
        ];


        $received_amount = max($receiving_credit, $receiving_debit);
        @$description = "$narration deposit {$currency}$received_amount From {$sending_account->owner->username}";



        $receiving_line = [
            "journal_id" => "",
            "chart_of_account_id" => $receiving_account->id ?? null,
            "chart_of_account_number" => $receiving_account->account_number ?? null,
            "description" => "$description",
            "credit" => $receiving_credit,
            "debit" => $receiving_debit,
        ];


        $charges = [];
        if ($collect_deposit_fee != false) {

            $charges[] = [
                "journal_id" => "",
                "chart_of_account_id" => null,
                "chart_of_account_number" => "",
                "description" => "deposit fee {$base_currency}$deposit_fee",
                "credit" => $deposit_fee,
                "debit" => 0,
            ];

            switch ($collect_deposit_fee) {
                case 'company':

                    $charges[] = [
                        "journal_id" => "",
                        "chart_of_account_id" => $sending_account->id,
                        "chart_of_account_number" => $sending_account->account_number,
                        "description" => "deposit Fee {$base_currency}$deposit_fee",
                        "credit" => 0,
                        "debit" => $deposit_fee,
                    ];

                    break;

                case 'receiver':
                    $charges[] = [
                        "journal_id" => "",
                        "chart_of_account_id" => $receiving_account->id,
                        "chart_of_account_number" => $receiving_account->account_number,
                        "description" => "deposit Fee {$base_currency}$deposit_fee",
                        "credit" => 0,
                        "debit" => $deposit_fee,
                    ];

                    break;
                default:
                    break;
            }
        }



        $involved_accounts = array_merge(
            [
                $sending_line,
                $receiving_line,
            ],
            $charges
        );


        $involved_accounts = array_filter($involved_accounts, function ($line) {
            return !empty($line);
        });


        $journal = [
            "id" => "",
            "company_id" => 1,
            "notes" => $deposit['narration'] ?? 'deposit',
            "currency" => $currency,
            "c_amount" => $deposit['amount'],
            "amount" => $amount,
            "status" => $deposit['status'] ?? 3,
            "journal_date" => $journal_date,
            "tag" => 'deposit' ?? null,
            "identifier" => $deposit['identifier'] ?? null,
            "user_id" => $deposit['user_id'] ?? null,
            "involved_accounts" => $involved_accounts,
        ];



        $response = self::postJournal($journal);
        return $response;
    }


    public static function postJournal($postable_journals)
    {


        $journal = $postable_journals;
        $involved_accounts = $journal['involved_accounts'];
        unset($journal['involved_accounts']);

        $validator = new Validator;
        //check that involved accounts is not empty
        if (count($involved_accounts)  == 0) {
            $validator->addError('Line items', "You must add line items.");
        }


        DB::connection('wallet')->beginTransaction();

        try {


            $journal =  Journals::create($journal);

            //update involved accounts
            $journal->remove_line_items();

            //validate involved account
            $account_numbers = collect($involved_accounts)->pluck('chart_of_account_number')->toArray();
            $chart_of_accounts = ChartOfAccount::whereIn('account_number', $account_numbers)->get()->keyBy('account_number')->toArray();
            $involved_accounts = array_map(function ($item) use ($chart_of_accounts) {
                if ($item['chart_of_account_id'] != null) {
                    return $item;
                }


                $item['chart_of_account_id'] = $chart_of_accounts[$item['chart_of_account_number']]['id'];
                return $item;
            }, $involved_accounts);




            //validate equal credit and debit
            $involved_accounts_count = 1;
            foreach ($involved_accounts as $key => $line) {

                $chart_of_account = ChartOfAccount::find($line['chart_of_account_id']);
                if ($line['description'] == '') {
                    $validator->addError('Chart of Accounts', "<b> {$involved_accounts_count})</b> Must have description.");
                }


                if ($chart_of_account == null) {
                    $validator->addError('Chart of Accounts', "<b> {$involved_accounts_count})</b> You must select chart of account.");
                }



                if ($line['credit'] == 0) {
                    $debits[] = $line['debit'];
                } else {
                    $credits[] = $line['credit'];
                }

                $involved_accounts_count++;
            }


            if (array_sum($credits) != array_sum($debits)) {
                $validator->addError('Double Entry', "Total Credits must be equal to Total Debits.");
            }


            if (!$validator->passed()) {
                throw new Exception("Error Processing Request",  $validator->errors());
                return false;
            }



            foreach ($involved_accounts as  $involved_account) {
                JournalInvolvedAccounts::create_involved_account($involved_account, $journal);
            }

            $journal->refresh();

            $actions = [
                1 => "draft",
                2 => "pend",
                3 => "publish",
            ];

            $action = $actions[$journal['status']];

            $response =  $journal->$action();
            DB::connection('wallet')->commit();

            return $response;
        } catch (\Throwable $th) {
            //throw $th;
            print_r($th->getMessage());
            DB::connection('wallet')->rollback();
            $response = false;
        }



        return $response;
    }




    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }
}
