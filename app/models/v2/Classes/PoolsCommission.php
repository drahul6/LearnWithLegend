<?php

namespace v2\Classes;

use User;
use v2\Models\PoolsCommissionTrack;
use v2\Models\Wallet\ChartOfAccount;
use v2\Models\Wallet\Classes\AccountManager;
use Illuminate\Database\Capsule\Manager as DB;
use Session;

class PoolsCommission
{

    public $month;
    public $month_range;

    public $value_per_person;

    public $site_settings;


    public $get_users_query;




    public function setMonthTesting($month = null)
    {
        $month = $month ?? date("Y-m-01", strtotime("-1 month"));


        $this->month = $month;
        $this->month_range = [
            "start" => date("Y-m-01", strtotime("$month")),
            "end" => $month,
        ];


        return $this;
    }


    public function setMonth($month = null)
    {

        $month = $month ?? date("Y-m", strtotime("-1 month"));



        $this->month = $month;
        $this->month_range = [
            "start" => date("$month-01"),
            "end" =>  date("Y-m-t", strtotime("$month-01"))
        ];

        //ensure month is a previous month
        if (time() < strtotime("{$this->month_range['end']} 03:00:00")) {

            throw new \Exception("This month:$month is not a previous month", 1);
            return;
        }

        return $this;
    }


    public function setUp()
    {
        $this->get_users_query = User::where('created_at', "<=", "{$this->month_range['end']} 23:59:59")->ActiveUsers();
        $total_users = $this->get_users_query->count();


        //check if already existing
        $pools_commission_track =  PoolsCommissionTrack::where('payment_month', $this->month_range['start'])->first();
        if ($pools_commission_track) {
            $total_pools_amount = $pools_commission_track->DetailsArray[0]['total_pools_amount'];
        } else {
            $pools_acount = ChartOfAccount::find(AccountManager::journal_second_legs('monthly_pools_prize_account'));
            $total_pools_amount = $pools_acount->get_balance($this->month_range['end'])['account_currency']['available_balance'];
        }



        $this->total_pools_amount = $total_pools_amount;
        // $total_pools_amount = $pools_acount->get_balance("2022-06-15")['account_currency']['available_balance'];
        $value_per_person = $total_pools_amount / $total_users;

        $this->value_per_person = round($value_per_person, 2);

        return $this;
    }


    private function getRowsToTreat()
    {

        $pools_commission_track =  PoolsCommissionTrack::where('payment_month', $this->month_range['start']);

        $query = User::where('users.created_at', "<=", "{$this->month_range['end']} 23:59:59")
            ->where('users.blocked_on', '=', null)
            ->addSelect('users.*')
            ->leftJoinSub($pools_commission_track, 'pools_commission_track', function ($join) {
                $join->on('users.id', '=', 'pools_commission_track.user_id');
            })->where('pools_commission_track.id', null)
            // ->take(20)
        ;

        $remaining = $query->count();

        echo "$remaining users remaining ";

        $users = $query->take(20);


        return $users;


        $sql = "select users.* from users left join pools_commission_track on users.id= pools_commission_track.user_id 
        where pools_commission_track.id is null limit 20";
    }



    public function treat()
    {



        $users =   $this->getRowsToTreat();

        if (count($users->get()) == 0) {
            Session::putFlash("info", "pools commission completely distributed for $this->month");
            echo "pools commission completely distributed for $this->month";
            return;
        }

        foreach ($users->get() as $key => $user) {

            $uplines = collect($user->referred_members_uplines(16, 'placement'));

            $pool_commission_structure = ($this->site_settings['pool_commission']->settingsArray);

            $detail = [];

            DB::beginTransaction();

            try {

                foreach ($pool_commission_structure as $level => $bonus) {

                    if (!isset($uplines[$level])) {
                        continue;
                    }


                    $receiver = $uplines[$level];


                    //create auto renewal if turned on, so user get better roi
                    $receiver->auto_renew_subscription();

                    if (!$receiver->isEligibleForCommission()) {
                        continue;
                    }

                    $membership =  $receiver->subscription->payment_plan;
                    $membership_driving_factors = $membership->DetailsArray['driving_factors'];
                    $capped_monthly_earning = $membership_driving_factors['capped_monthly_earning'];

                    $percent = floatval($bonus['percent']);
                    $amount = $percent * 0.01 * $this->value_per_person;


                    $user_identifier = "^pools_commission#{$this->month_range["start"]}.*D{$receiver->id}$";
                    $identifier = "pools_commission#{$this->month_range["start"]}#{$user->id}L{$level}#D{$receiver->id}";

                    //get total pools commission for the month already received by the receiver
                    $account = $receiver->getAccount('default');
                    $transactions = ($account->transactions(
                        100000000,
                        1,
                        [
                            "tag" => "pools_commission",
                            "identifier" => "$user_identifier",
                            "status" => "3,4",
                        ],
                        [
                            "chart_of_account.owner_id" => $account->owner_id
                        ],
                    ));
                    $total_amount_received_already = $transactions['query']->sum('ac_involved_accounts.credit');



                    //check max capped earnings
                    $offset_amount =  ($amount + $total_amount_received_already) > $capped_monthly_earning
                        ?  $capped_monthly_earning - ($total_amount_received_already)
                        : $amount;




                    $display_month = date("M.Y", strtotime($this->month_range['start']));
                    $comment = "{$display_month} level $level pools commission, {$percent}% of {$this->value_per_person} vpp";

                    $upline_user_id = $user->id;
                    $receiving_user_id = $receiver->id;
                    $total_pools_amount = $this->total_pools_amount;
                    $detail[$level] = compact("total_pools_amount", "amount", "offset_amount", "comment", "upline_user_id", "receiving_user_id", "identifier");



                    if ($offset_amount == 0) {
                        continue;
                    }


                    $line =  AccountManager::payPoolsCommission([
                        'receiver' => $receiver,
                        'amount' => $offset_amount,
                        'identifier' => $identifier,
                        'narration' => $comment,
                    ]);
                }


                $unpaid  = $this->value_per_person - collect($detail)->sum('offset_amount');

                $track = PoolsCommissionTrack::create([
                    "user_id" => "$user->id",
                    "payment_month" => $this->month_range['start'],
                    "unpaid_amount" => $unpaid,
                    "details" => json_encode($detail),
                    "status" => "1",
                ]);



                DB::commit();
            } catch (\Throwable $th) {
                print_r($th->getMessage());
                DB::rollback();
                //throw $th;
            }

            // break;
        }
    }

    public function settle()
    {
        $this->setUp();

        $this->treat();
    }

    public function setSiteSettings($site_settings)
    {
        $this->site_settings = $site_settings;

        return $this;
    }
}
