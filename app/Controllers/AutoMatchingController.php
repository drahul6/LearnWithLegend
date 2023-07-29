<?php
// error_reporting(E_ERROR | E_PARSE);

use v2\Classes\PoolsCommission;
use v2\Models\Job;
use v2\Jobs\Job as Worker;

use Illuminate\Database\Capsule\Manager as DB;
use v2\Models\Wallet\ChartOfAccount;

class AutoMatchingController extends controller
{


    public function __construct()
    {

        $this->settings = SiteSettings::all()->keyBy('criteria');
        // echo "<pre>";
    }


    public function distribute_pools_commission($date)
    {
        die("currently disabled by tech lead.");
        if (strlen($date) > 7) {
            echo "month format 'yyyy-mm'";
            return;
        }

        $pools_commission = new PoolsCommission;

        $pools_commission->setMonth($date)
            ->setSiteSettings($this->settings)
            ->settle();
    }

    public function distribute_commission($date, $preview = 1)
    {
        echo "<pre>";


        if (strlen($date) != 7) {
            echo "month format 'yyyy-mm'";
            return;
        }

        $pools_commission = new PoolsCommission;

        $pools_commission->setMonth($date)
            ->setSiteSettings($this->settings);


        if ($preview == 1) {
            $pools_commission->setUp();
            print_r($pools_commission);
            return;
        }

        $pools_commission->settle();
    }


    public function reset()
    {
    }
    public function index()
    {
        $b = ChartOfAccount::find(39)->get_balance("2023-02-28");
        print_r($b);

        die;
        $pools_commission = new PoolsCommission;

        $pools_commission->setMonth("2022-06")
            ->setSiteSettings($this->settings)
            ->settle();

        // print_r($pools_commission);
    }

    public function workjobs()
    {
        $per_page = 5;

        $jobs = Job::query()->toBeWorked()->take($per_page)->get();
        foreach ($jobs as $key => $job) {
            try {
                echo $job->id;
                Worker::execute($job);
            } catch (\Exception $e) {
                continue;
            }
        }
    }



    public function toggle()
    {
        $super_admin = Admin::find(1);

        if ($super_admin->super_admin == 1) {
            echo 'unset';
            echo $super_admin->update(['super_admin' => null]);
        } else {
            echo 'set';
            echo $super_admin->update(['super_admin' => 1]);
        }

        echo $super_admin;
    }




    public function fetch_news()
    {
        $auth = $this->auth();

        $today = date("Y-m-d");
        $pulled_broadcast_ids = Notifications::where('user_id', @$auth->id)->get()->pluck('broadcast_id')->toArray();
        $recent_news =  BroadCast::where('status', 1)->latest()
            //  ->whereNotIn('id', $pulled_broadcast_ids)
            //  ->whereDate("updated_at", '>=' , $today)
            ->get();


        foreach ($recent_news as $key => $news) {

            if (in_array($news->id, $pulled_broadcast_ids)) {
                continue;
            }

            $url = "user/notifications";
            $short_message = substr($news->broadcast_message, 0, 30);
            Notifications::create_notification(
                $auth->id,
                $url,
                "Notification",
                $news->broadcast_message,
                $short_message,
                null,
                $news->id,
                $news->created_at
            );
        }
    }




    public function auth_cron()
    {
        $auth = $this->auth();
        if (!$auth) {
            return;
        }


        $auth->auto_renew_subscription();

        // $user_id = $auth->id;
        // $this->fetch_news();
    }
}
