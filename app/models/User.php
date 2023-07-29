<?php

/*ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
*/

use Carbon\Carbon;
use v2\Models\UserDocument;
use Filters\Filters\UserFilter;
use v2\Filters\Traits\Filterable;
use v2\Models\Wallet\ChartOfAccount;
use v2\Models\Wallet\Classes\AccountManager;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;


class User extends Eloquent
{
    use Filterable;

    protected $fillable = [
        'mlm_id',
        'referred_by',
        'introduced_by',
        'binary_id',
        'binary_position',
        'binary_point',
        'placement_position',
        'enrolment_position',
        'settings',
        'placement_cut_off',
        'type_of_registration',
        'remember_token',
        'firstname',
        'lastname',
        'username',
        'account_plan',
        'rank',
        'rank_history',
        'email',
        'details',
        'email_verification',
        'phone',
        'country',
        'phone_verification',
        'profile_pix',
        'resized_profile_pix',
        'password',
        'lastseen_at',
        'lastlogin_ip',
        'blocked_on',

    ];

    protected $table = 'users';
    protected $connection = 'default';
    protected $dates = [
        'created_at',
        'updated_at',
        'lastseen_at'

    ];
    protected $hidden = ['password'];

    //the placement tree width
    public static $max_level = 13;


    public static $genders = [
        1 => 'Male',
        2 => 'Female',
    ];



    public static  $not_changeable = [
        'firstname', 'lastname', 'email', 'username'
    ];



    private static $possible_personal_settings = [
        'membership_choice',
        "enable_2fa",
        "2fa_recovery"
    ];


    public static $tree = [
        'enrolment' => [
            'width' => 1000000,
            'depth' => 20,
            'column' => 'introduced_by',
            'title' => 'Enrolment',
            'position' => 'enrolment_position',
            'point' => null,
        ],

        'placement' => [
            'width' => 2,
            'depth' => 16,
            'column' => 'referred_by',
            'title' => 'Direct Referral',
            'position' => 'placement_position',
            'point' => null,
        ],

        'binary' => [
            'width' => 2,
            'depth' => 16,
            'column' => 'binary_id',
            'title' => 'Binary Tree',
            'position' => 'binary_position',
            'point' => 'binary_point',
        ],
    ];


    public static $rank_to_start_auto_membership = 1;

    public static $type_ids = [
        'staker',
        'tipster',
        's_tipster'
    ];


    public static $billings = [
        0 => "manual", 1 => "auto"
    ];


    public function isEligibleForCommission()
    {
        $has_verified_profile = $this->has_verified_profile();
        return $has_verified_profile;
    }

    public function getBillingModeAttribute()
    {
        $mode = $this->getAccountPlanSettings("auto_billing");
        return self::$billings[(int)$mode];
    }

    public function getBillingButtonTextAttribute()
    {
        $mode = $this->getAccountPlanSettings("auto_billing");
        return self::$billings[(int)!$mode];
    }

    public function getAccountPlanSettings($key = null)
    {
        $settings = $this->SettingsArray['account_plan'];


        $response  = get_defined_vars();
        return $key == null ? $response : $settings[$key];
    }

    public static function premiumMembers()
    {
        echo "getting premium members";
    }

    public function getDisplayableWebtalkLinkAttribute()
    {
        if ($this->id == 1) {
            return null;
        }

        return $this->details['webtalk_link'];
    }

    public function getDisplayableViiralLegendLinkAttribute()
    {
        if ($this->id == 1) {
            return null;
        }

        return $this->details['viiral_legend_link'];
    }


    public static function get_referral($referral_username = '')
    {
        return User::where('username', $referral_username)->first()->mlm_id ?? 1;
    }

    public static function createUser($user_details)
    {

        $introduced_by = self::get_referral($user_details['introduced_by']);
        $referred_by = self::where_to_place_new_user_within_team_serially($introduced_by);
        $binary_sponsor = self::stictly_where_to_place_new_user_within_team_introduced_by_balanced($introduced_by, 'binary');

        $user_details['referred_by'] = $referred_by;
        $user_details['introduced_by'] = $introduced_by;
        $user_details['binary_id'] = $binary_sponsor['member']['mlm_id'];
        $user_details['binary_point'] = $binary_sponsor['leg'];

        $user_details['email_verification'] = MIS::random_string();
        try {

            $new_user = self::create($user_details);
            $new_user->update(['mlm_id' => $new_user->id]);
            $new_user->setTreesPosition();


            $settings = $new_user->settingsArray;
            $account_plan = $new_user->settingsArray['account_plan'] ?? [];
            $account_plan['auto_billing'] = 1;
            $settings['account_plan'] = $account_plan;
            $new_user->save_settings($settings);
        } catch (\Throwable $th) {
            print_r($th->getMessage());
        }
        return $new_user;
    }

    public function updateDetails(array $key_value_array)
    {
        $details = $this->details;
        $details = array_merge($details, $key_value_array);

        $this->update(['details' => $details]);
    }

    public function getDetailsAttribute($value)
    {
        if ($value == null) {
            return [];
        }
        return json_decode($value, true);
    }


    public function setDetailsAttribute($value)
    {
        $this->attributes['details'] = json_encode($value);
    }

    //for accounts
    public function walletSummary($tag = 'default')
    {
        $wallet =  $this->getAccount($tag);

        $pending_withdrawals = $wallet->transactions(100000000, null, [
            'tag' => 'withdrawal',
            'status' => '2',
        ], [])['transactions']->sum('c_amount'); //in account currency


        $completed_withdrawals = $wallet->transactions(100000000, null, [
            'tag' => 'withdrawal',
            'status' => '3,4',
        ], [])['transactions']->sum('c_amount'); //in account currency


        $total_earnings = $wallet->transactions(100000000, null, [
            'tag' => 'pools_commission',
            'status' => '3,4',
        ], [])['transactions']->sum('c_amount'); //in account currency



        return compact('total_earnings', 'pending_withdrawals', 'completed_withdrawals');
    }



    public function getAccount($tag)
    {
        $account = ChartOfAccount::where('tag', $tag)->where('owner_id', $this->id)->first();

        if ($account != null) {
            return $account;
        }

        $manager = new AccountManager;
        $manager->setUser($this)->OpenAccountByTag($tag);


        $account = ChartOfAccount::where('tag', $tag)->where('owner_id', $this->id)->first();

        return $account;
    }





    public function hasActiveMembership()
    {
        $subscription_plan = $this->subscription->payment_plan;
        return !($subscription_plan == SubscriptionPlan::default_sub());
    }







    public function getTypeDetailsAttribute()
    {
        if ($this->type_id == null) {

            return [];
        }

        return json_decode($this->type_id,  true);
    }

    public function setType(array $type)
    {
        return $this->update(['type_id' => json_encode($type)]);
    }

    public function addType($type)
    {
        $type_ids = $this->TypeDetails;
        if (!$this->isA($type)) {
            $type_ids[] = $type;
            return $this->setType($type_ids);
        }

        return true;
    }

    public function removeType($type)
    {
        $type_ids = $this->TypeDetails;
        if ($this->isA($type)) {
            $key = array_search($type, $type_ids);
            unset($type_ids[$key]);
            return $this->setType($type_ids);
        }
        return true;
    }

    public function isA($type)
    {
        $type_ids = $this->TypeDetails;
        return in_array($type, $type_ids);
    }

    public function getTypesAttribute($type)
    {
        $type_ids = $this->TypeDetails;
        $labels =  @implode($type_ids, ",");
        return $labels;
    }


    public function scopeAllEditors($query)
    {
        return $query->where('type_id', 'like',  "%tipster%");
    }

    public function scopeSimulatedEditors($query)
    {
        return $query->where('type_id', 'like',  "%s_tipster%");
    }


    public static function is_disabled($property, $user)
    {
        $disabled = "readonly";
        $not_disabled = "";


        if (!in_array($property, User::$not_changeable)) {
            return $not_disabled;
        }


        if ($user->$property == null) {
            return $not_disabled;
        }


        return $disabled;
    }


    public function max_uplevel($tree_key)
    {

        $tree = self::$tree[$tree_key];
        $user_column = $tree['position'];

        $mlm_ids = explode("/", $this->$user_column);

        $max_level = count($mlm_ids) - 1;

        return compact('mlm_ids', 'max_level');
    }



    public function has_verified_phone()
    {
        return (intval($this->phone_verification) == 1);
    }


    public function scopeVerified($query)
    {
        $no_of_documents = count(v2\Models\UserDocument::$document_types) - 1;

        $eloquent = UserDocument::from("users_documents as user_doc")
            ->select('user_doc.user_id', DB::raw("COUNT(*) as approved_docs"))
            ->where('user_doc.status', 2)
            ->groupBy('user_doc.user_id')
            ->having('approved_docs', '>', $no_of_documents)
            ->leftJoin('users_documents', function ($join) {
                $join
                    ->on('user_doc.document_type', '=', 'users_documents.document_type')
                    ->on('user_doc.id', '<', 'users_documents.id');
            })
            ->where('users_documents.id', null);

        $userss = User::query()
            ->joinSub($eloquent, 'approved_documents', function ($join) {
                $join->on('users.id', '=', 'approved_documents.user_id');
            });

        return $userss;
    }

    public function has_verified_profile()
    {
        return $this->VerifyProfile();

        if (isset($this->details['profile_is_verified'])  && ($this->details['profile_is_verified'] == 1)) {
            return true;
        }

        return false;
    }


    public function VerifyProfile()
    {
        $no_of_documents = count(v2\Models\UserDocument::$document_types);
        $document_types = array_keys(UserDocument::$document_types);
        $query = UserDocument::select('user_id', DB::raw("COUNT(*) as approved_docs"))
            ->where('user_id', $this->id)
            ->where('status', 2)
            ->whereIn('document_type', $document_types)
            ->groupBy('user_id')
            ->having('approved_docs', '>=', $no_of_documents);

        $is_approved = $query->get()->count()  > 0;

        if ($is_approved) {
            $this->updateDetails([
                "profile_is_verified" => 1
            ]);
        }
        return $is_approved;
    }

    public function getVerifiedBagdeAttribute()
    {

        if ($this->has_verified_profile()) {

            $status = "<span class='badge badge-success'>Verified</span>";
        } else {

            $status = "<span class='badge badge-danger'>Not Verified</span>";
        }

        return $status;
    }


    public function getphoneVerificationStatusAttribute()
    {
        return;
        if ($this->has_verified_phone()) {

            $status = "<span class='badge badge-success'>Verified</span>";
        } else {

            $status = "<span class='badge badge-danger'>Not Verified</span>";
        }

        return $status;
    }

    public function has_verified_email()
    {
        return (strlen($this->email_verification) == 1);
    }

    public function getemailVerificationStatusAttribute()
    {

        if ($this->has_verified_email()) {

            $status = "<span class='badge badge-success'>Verified</span>";
        } else {

            $status = "<span class='badge badge-danger'>Not Verified</span>";
        }

        return $status;
    }



    public function documents()
    {
        return $this->hasMany('v2\Models\UserDocument', 'user_id')->latest();
    }

    public function approved_documents()
    {
        $id = $this->id;
        $approved_ids = collect(DB::select("SELECT m1.*
            FROM users_documents m1 LEFT JOIN users_documents m2
             ON (m1.document_type = m2.document_type AND m1.id < m2.id)
            WHERE m2.id IS NULL 
            AND m1.status = '2'
            AND m1.user_id = $id
            ;
            "))->pluck('id')->toArray();


        return $this->hasMany('v2\Models\UserDocument', 'user_id')->whereIn('id', $approved_ids)->Approved();
    }

    public function pending_documents()
    {
        $id = $this->id;
        $approved_ids = collect(DB::select("SELECT m1.*
            FROM users_documents m1 LEFT JOIN users_documents m2
             ON (m1.document_type = m2.document_type AND m1.id < m2.id)
            WHERE m2.id IS NULL 
            AND m1.status != '2'
            AND m1.user_id = $id
            ;
            "))->pluck('id')->toArray();


        return $this->hasMany('v2\Models\UserDocument', 'user_id')->whereIn('id', $approved_ids);
    }



    public function binary_status()
    {
        /* $binary = $this->referred_members_downlines(1,"binary");
        $frontline = $binary[1] ?? [];
       return count($frontline) == 2;*/

        return $this->is_qualified_distributor();
    }

    public function getBinaryStatusDisplayAttribute()
    {
        if ($this->binary_status()) {
            $display = "<em class='text-success'>Active</em>";
        } else {
            $display = "<em class='text-danger'>Inactive</em>";
        }

        return $display;
    }

    public function all_uplines($tree_key = 'placement')
    {

        $tree = self::$tree[$tree_key];
        $user_column = $tree['column'];


        //first include self
        $this_user_uplines[0] = $this->toArray();
        $upline = $this->$user_column;

        $level = 0;
        do {

            $level++;
            $found =    self::where('mlm_id', $upline)->where('mlm_id', '!=', null)->first();
            if ($found != null) {
                $this_user_uplines[$level] = $found->toArray();
            } else {
                $this_user_uplines[$level] = null;
            }

            $upline = $this_user_uplines[$level][$user_column];
        } while ($this_user_uplines[$level] != null);


        return ($this_user_uplines);
    }


    //0=left, 1=right
    public function all_downlines_at_position($position, $tree_key = 'placement')
    {

        $tree = self::$tree[$tree_key];
        $user_column = $tree['column'];
        $user_point = $tree['point'];
        $downline_at_position = ($this->referred_members_downlines(1, $tree_key));

        $downline_ordered = collect($downline_at_position[1] ?? [])->keyBy($user_point)->toArray();

        $downline_at_position = $downline_ordered[$position] ?? null;

        if ($downline_at_position == null) {
            return self::where('id', null);
        }

        $downline = self::find($downline_at_position['id']);

        return  $downline->all_downlines_by_path($tree_key, true);
    }



    public function all_downlines_by_path($tree_key = 'placement', $add_self = false, $level = -1)
    {

        $tree = self::$tree[$tree_key];
        $user_column = $tree['position'];
        $identifier = "/{$this->$user_column}";

        $add_self_options = [
            1 => self::WhereRaw("(mlm_id = '{$this->mlm_id}' OR $user_column like '%$identifier%')"),
            0 => self::where($user_column, 'like', "%$identifier%")
        ];


        $query = $add_self_options[(int)$add_self];

        if ($level <= -1 || $level == 'all') {
            return $query;
        }

        $level_pattern =  str_repeat("(\d+)*/", $level);
        $query->where($user_column, "regexp", "^({$level_pattern}{$this->mlm_id})");

        return $query;
    }



    public function setTreesPosition()
    {

        $position = [];
        foreach (self::$tree as $key => $value) {
            $user_column = $value['position'];
            $all_uplines = collect($this->all_uplines($key))->pluck('mlm_id')->toArray();

            $all_uplines =  array_filter($all_uplines, function ($item) {
                return $item != null;
            });

            $position_value =  (implode('/', $all_uplines));

            $position[$user_column] = $position_value;
        }

        $this->update($position);
    }


    public function auto_renew_subscription()
    {
        $mode = $this->getAccountPlanSettings("auto_billing");
        //check auto billing is on
        if (!$mode) {
            return;
        }

        $this->renew_subscription();
    }

    public function renew_subscription()
    {

        //get last subscription
        $subscription =  SubscriptionOrder::where('user_id', $this->id)->Paid()->latest('paid_at')->first();

        if (!($subscription instanceof SubscriptionOrder)) {
            return;
        }


        if (!$subscription->is_expired()) {
            return;
        }

        SubscriptionPlan::create_subscription_request($subscription->payment_plan->id, $this->id);
        Session::flash();
    }


    public function hasActiveSubscription()
    {
        return !($this->subscription->payment_plan->id == 1);
    }


    //has active person on the right and left, personlly sponsored
    public function is_qualified_distributor()
    {


        //direct_lines
        $direct_lines = $this->all_downlines_by_path('enrolment', false)->where('introduced_by', $this->mlm_id);


        if ($direct_lines->count() == 0) {
            return false;
        }


        //get those with active subscription
        $today = date("Y-m-d");

        ///since only active subscription are stored in this table, and no more expiration on subscription
        $active_subscriptions = SubscriptionOrder::Paid()/*->whereDate('expires_at','>' , $today)*/;

        $active_members_left = $direct_lines
            ->joinSub($active_subscriptions, 'active_subscriptions', function ($join) {
                $join->on('users.id', '=', 'active_subscriptions.user_id');
            });

        $active_left  =  $active_members_left->count();

        if ($active_left == 0) {
            return false;
        }




        $total =  $active_left;



        if ($total >= 2) {
            return true;
        }


        return false;
    }


    public function can_received_compensation()
    {
        $plan_details = $this->subscription->payment_plan->DetailsArray;

        if ($plan_details['benefits']['participate_in_compensation_plan'] == 1) {
            return true;
        }
        return false;
    }



    public function getDisplayGenderAttribute()
    {
        return self::$genders[$this->gender] ?? '';
    }




    public function decoded_country()
    {
        return $this->belongsTo('World\Country', 'country');
    }

    public function decoded_state()
    {
        return $this->belongsTo('World\State', 'state');
    }



    public function getTwofaDisplayAttribute()
    {

        if ($this->has_2fa_enabled()) {
            $display = "<span class='badge badge-success'>ON</span>";
        } else {
            $display = "<span class='badge badge-danger'>OFF</span>";
        }

        return $display;
    }

    public function save_settings(array $settings)
    {

        if (count($settings) == 0) {
            return;
        }

        $update = $this->update([
            'settings' => json_encode($settings)
        ]);

        return $update;
    }

    public function has_2fa_enabled()
    {
        return @$this->SettingsArray['enable_2fa'] == 1;
    }

    public function getSettingsArrayAttribute()
    {
        if ($this->settings == null) {
            return [];
        }

        return json_decode($this->settings, true);
    }


    public function company()
    {

        return $this->hasOne('Company', 'user_id');
    }


    public function unseen_notifications()
    {
        return Notifications::unseen_notifications($this->id);
    }



    public function all_notifications()
    {
        return Notifications::all_notifications($this->id, $per_page = null, $page = 1);
    }




    public function products_orders()
    {

        return $this->hasMany('Orders', 'user_id');
    }




    public function accessible_products()
    {

        return Products::accessible($this->subscription->id)->get();
    }




    //end of the calendar month degrade
    public static function degrade_all_members()
    {
        $update = self::latest()->update(['account_plan' => null]);
        if ($update) {
            return true;
        }
    }




    public function subscription_payment_date($month = null, $day_format = false)
    {
        $subscription =  $this->subscription_for($month);
        if ($subscription !=  null) {
            switch ($day_format) {
                case true:

                    return date("d", strtotime(($subscription->paid_at)));

                    break;

                default:
                    return $subscription->paid_at;
                    break;
            }
        }


        return false;
    }



    public function getSubAttribute()
    {

        if ($this->subscription != null) {
            return $this->subscription->plandetails['package_type'];
        }

        return 'Nil';
    }



    public function getMembershipStatusDisplayAttribute()
    {
        if ($this->subscription->payment_plan->id == 1) {
            $display = "<em class='text-danger'>Inactive</em>";
        } else {
            $display = "<em class='text-success'>Active</em>";
        }

        return $display;
    }



    public function getsubscriptionAttribute($date = null)
    {

        // die;
        $today = $date ?? date("Y-m-d");
        $subscription =  SubscriptionOrder::where('user_id', $this->id)->Paid()->NotExpired($today)->latest('paid_at')->first();

        $default = SubscriptionPlan::default_sub();
        $another = SubscriptionPlan::default_sub();
        $default->payment_plan = $another;

        if ($subscription == null) {
            return $default;
        }




        $expiry_time = strtotime($subscription->expires_at);

        // since no membership cannot expire

        if (($subscription->payment_state == 'manual') || ($subscription->payment_state == null)) {

            if ($expiry_time < strtotime($today)) {

                return $default;
            } else {
                return $subscription;
            }
        } elseif ($subscription->payment_state == 'automatic') {

            return $subscription;
        } elseif ($subscription->payment_state == 'cancelled') {

            if ($expiry_time < strtotime($today)) {

                return $default;
            } else {
                return $subscription;
            }
        } else {

            return $default;
        }

        return $default;
    }

    public function subscriptions()
    {
        return $this->hasMany('SubscriptionOrder',  'user_id');
    }


    public function scopeBlockedUsers($query)
    {

        return $query->where('blocked_on', '!=', null);
    }


    public function scopeActiveUsers($query)
    {
        return $query->where('blocked_on', '=', null);
    }





    public static function generate_phone_code_for($user_id)
    {

        $remaining_code_length =   6 -    strlen($user_id);
        $min = pow(10, ($remaining_code_length - 1));
        $max = pow(10, ($remaining_code_length)) - 1;

        $remaining_code = random_int($min, $max);

        return  $phone_code = $user_id . $remaining_code;
    }






    public function getqualifyStatusAttribute()
    {

        $status = (($this->is_qualified_for_commission(null)))
            ? "<span type='span' class='badge badge-success'>Active</span>" :
            "<span type='span' class='badge badge-danger'>Not Active</span>";

        return $status;
    }


    public function getactiveStatusAttribute()
    {

        $status = (($this->blocked_on == null))
            ? "<span type='span' class='badge badge-xs badge-success'>Active</span>" :
            "<span type='span' class='badge badge-xs badge-danger'>Blocked</span>";

        return $status;
    }



    public function getDropSelfLinkAttribute()
    {

        // $rank = $this->TheRank['name'];

        /*  <br> Membership-  {$this->subscription->payment_plan->name}
        <br> Rank-$rank */
        return  "<a target='_blank' href='{$this->AdminViewUrl}'>{$this->full_name} ($this->username)
        <br><i class='fa fa-envelope'></i> {$this->email} {$this->emailVerificationStatus}
        <br><i class='fa fa-phone'></i> {$this->phone} {$this->phoneVerificationStatus}
         </a>";
    }



    public function getAdminEditUrlAttribute()
    {
        $client_id = MIS::dec_enc('encrypt', $this->id);
        $href =  Config::domain() . "/admin/edit_client_detail/" . $client_id;
        return $href;
    }


    public function getAdminViewUrlAttribute()
    {
        $href =  Config::domain() . "/admin/user_profile/" . $this->id;
        return $href;
    }

    public function getAdminEditSubscriptionAttribute()
    {
        $href =  Config::domain() . "/admin/user/{$this->id}/subscription";
        return $href;
    }




    public function testimonies()
    {
        return $this->hasMany('Testimonials', 'user_id');
    }




    public function no_of_rejoin()
    {
        if ($this->rejoin_id != null) {

            return  count(explode(",", rtrim($this->rejoin_id, ',')));
        } else {
            return 0;
        }
    }



    public function ripe_for_rejoin()
    {
        $mustbe_on_highest_level = ($this->rank == self::$max_level);
        $payments_received = Payouts::where('payer_id', $this->id)->where('status', 'Approved')->count();


        $must_have_received_all_payments = ($payments_received == 30);

        return ($mustbe_on_highest_level && $must_have_received_all_payments);
    }

    public function Sponsor()
    {
        return  $this->belongsTo(self::class, 'introduced_by');
    }


    public function rejoin($tree_key = 'placement')
    {

        $tree = self::$tree[$tree_key];
        $user_column = $tree['column'];


        $email          = $this->email;
        $sponsor = User::where_to_place_new_user_within_team_introduced_by($this->id, $tree_key);
        $username      = User::generate_username_from_email($email);


        $replicate = $this->replicate();



        $this->rejoin_email = $this->email;
        $this->email = null;
        $this->username = null;

        print_r($this->toArray());



        $replicate->email = $this->rejoin_email;
        $replicate->$user_column = $sponsor;
        $replicate->introduced_by = $this->id;
        $replicate->rank = null;
        $replicate->rejoin_id = ($this->rejoin_id == null) ? $this->id : "{$this->rejoin_id},$this->id";

        $this->save();
        $replicate->save();



        // print_r($replicate->toArray());

        // $newTask->save();
        Session::putFlash('', "Congrats!! You completed the level" . self::$max_level . " and hence rejoined!");
    }



    public function generate_username_from_email($email)
    {
        $username = explode('@', $email)[0];
        $i = 1;
        do {
            $loop_username = ($i == 1) ? "$username" : "$username" . ($i - 1);
            $i++;
        } while (User::where('username', $loop_username)->get()->isNotEmpty());


        return $loop_username;
    }




    public function which_leg($sponsor_id)
    {

        $sponsor = User::find($sponsor_id);
        $mlm_width = 2;


        $direct_lines =  ($sponsor->referred_members_downlines(1)[1]);

        for ($leg_index = 0; $leg_index < $mlm_width; $leg_index++) {
            if ($direct_lines[$leg_index] == '') {
                return $leg_index;
            }
        }
    }



    public function replace_any_cutoff_mlm_placement_position($sponsor_id, $substitute_id)
    {
        $placement_sponsor = User::find($sponsor_id);

        $former_downline_mlm_id =  (array_values($placement_sponsor->placement_cut_off))[0]; //mlm_id


        if ($former_downline_mlm_id != '') {




            print_r(array_values($placement_sponsor->placement_cut_off));


            $former_downline = User::where('mlm_id', $former_downline_mlm_id)->first();
            $former_downline_replica = $former_downline->replicate();

            $former_downline->mlm_id = null;
            $former_downline->save();

            $substitute = User::find($substitute_id);
            $substitute->mlm_id =  $former_downline_mlm_id;

            $substitute->save();


            //update cutoff history
            $cutoff_history = $placement_sponsor->placement_cut_off;
            $cutoff_index = array_search($former_downline_mlm_id, $cutoff_history);
            unset($cutoff_history[$cutoff_index]);

            $placement_sponsor->update(['placement_cut_off' => json_encode($cutoff_history)]);
        }
    }




    public function getplacementcutoffAttribute($value = '')
    {
        return json_decode($value, true);
    }


    public function prepare_placement_cutoff($tree_key = 'placement')
    {


        $tree = self::$tree[$tree_key];
        $user_column = $tree['column'];


        try {


            $placement_sponsor = User::where('mlm_id', $this->$user_column)->where('mlm_id', '!=', null)->first();

            if ($placement_sponsor == null) {
                // Redirect::to('login/logout');
            }

            $leg_index  =    $placement_sponsor->leg_of_user($this->mlm_id);



            $cutoff_history         = ($placement_sponsor->placement_cut_off);
            $cutoff_history[$leg_index]     = $this->mlm_id;
            $cutoff_history['tree_key']    = $user_column;


            $placement_sponsor->update([
                'placement_cut_off' => json_encode($cutoff_history),
            ]);
        } catch (Exception $e) {
            echo "string";
        }
    }


    public function remove_from_mlm_tree($tree_key = 'placement')
    {

        $tree = self::$tree[$tree_key];
        $user_column = $tree['column'];

        $this->prepare_placement_cutoff($tree_key);
        $this->update([
            $user_column    => null,
        ]);
    }

    public function block_user()
    {

        $this->update([
            'blocked_on'    => date("Y-m-d H:i:s"),
        ]);
    }


    /**
     * [higher_level_leaders for generational bonuses]
     * @return [type] [description]
     */
    public static function higher_level_leaders()
    {
        $min_rank_to_earn_generational_bonus = json_decode(
            MlmSetting::where('rank_criteria', 'min_rank_to_earn_generational_bonus')->first()->settings,
            true
        );


        return    User::where('rank', '>=', $min_rank_to_earn_generational_bonus)->where('blocked_on', null);
    }



    /**
     * [finalise_upline determines the upline to eventuwlly receive funds
     * after checking if original upline e]meets certain criteria else returns the demo 
     * user as the uline
     * @param  [type] $receiver_id   [the orignal upline]
     * @param  [type] $upgrade_level [the level the ugrade fee is for]
     * @return [type]                [description]
     */
    public function finalise_upline($receiver_id, $upgrade_level, $tree_key = 'placment')
    {
        return $receiver_id;


        $tree = self::$tree[$tree_key];
        $user_column = $tree['column'];


        $original_upline = User::find($receiver_id);
        $default_upline =  User::where('account_plan', 'demo')->first();


        $not_locked_to_receive_funds = ($original_upline->locked_to_receive == null);
        $not_blocked = ($original_upline->blocked_on == null);
        $can_receive_level_fund = ($original_upline->rank >= $upgrade_level); //based on level
        $upline_exists_in_mlm_tree  =  ($original_upline->$user_column != null);

        $expected_no_of_receive = [1 => 2, 2 => 4, 3 => 8, 4 => 16];

        $has_not_received_fund_in_excess = (Payouts::where('receiver_id', $original_upline->id)
            ->where('upgrade_level', $upgrade_level)
            ->where('status', 'Approved')
            ->count() < $expected_no_of_receive[$upgrade_level]);


        if (
            $not_blocked &&
            $not_locked_to_receive_funds &&
            // $can_receive_level_fund  &&
            $has_not_received_fund_in_excess &&
            $upline_exists_in_mlm_tree
        ) {

            return $original_upline->id;
        }

        return $default_upline->id;
    }

    //this returns the total and last member on each legs
    public function strict_number_at_leg($leg_index, $tree_key)
    {

        $tree = self::$tree[$tree_key];
        $user_column = $tree['column'];
        $mlm_width      = $tree['width'];
        $point      = $tree['point'];
        $user = $this;

        $downlines = [];
        $level = 0;
        do {

            $level++;
            $found =    $user->referred_members_downlines($level, $tree_key)[1] ?? null;

            if ($found == null) {
                break;
            }
            //key retrieved by their binarypoint !important
            $found = collect($found)->keyBy($point)->toArray()[$leg_index];

            $downlines[$level] = $found;
            $user = self::find($found['id']);
        } while ($found != null);

        $downlines =  array_filter($downlines, function ($item) {
            return $item != null;
        });


        $result = [
            'total' => count($downlines),
            'last_member' => end($downlines),
        ];

        return $result;
    }


    public function strict_number_at_leg_balanced($leg_index, $tree_key)
    {

        $tree = self::$tree[$tree_key];
        $user_column = $tree['column'];
        $mlm_width      = $tree['width'];
        $point      = $tree['point'];
        $user = $this;

        $downlines = [];
        $level = 0;
        do {

            $level++;
            $found =    $user->referred_members_downlines($level, $tree_key)[1] ?? null;

            if ($found == null) {
                break;
            }
            //key retrieved by their binarypoint !important
            $found = collect($found)->keyBy($point)->toArray()[$leg_index];

            $downlines[$level] = $found;
            $user = self::find($found['id']);
        } while ($found != null);

        $self_downlines =  array_filter($downlines, function ($item) {
            return ($item['introduced_by'] == $this->mlm_id);
            return $item != null;
        });

        $downlines =  array_filter($downlines, function ($item) {
            return $item != null;
        });

        // print_r($downlines);

        $result = [
            'total' => count($downlines),
            'last_member' => end($downlines),
            'last_mlm_id' => end($downlines)['mlm_id'],
            'self_total' => count($self_downlines),
            'self_last_member' => end($self_downlines),
            'self_last_mlm_id' => end($self_downlines)['mlm_id'] ?? 0,
        ];

        return $result;
    }


    //determine where to put a new member (places new user at one on the left,one one the right.)
    //considers only directly sponsored team members
    public static function stictly_where_to_place_new_user_within_team_introduced_by_balanced($team_leader_id, $tree_key = 'placement', $perferred_leg = null)
    {


        $tree = self::$tree[$tree_key];
        $user_column = $tree['column'];
        $mlm_width      = $tree['width'];

        $team_leader    = User::find($team_leader_id);
        if ($team_leader->mlm_id == '') {
            $team_leader =  User::find(1);
        }

        $legs = [];
        for ($leg = 0; $leg < $mlm_width; $leg++) {

            $legs[$leg] =  $team_leader->strict_number_at_leg_balanced($leg, $tree_key);
        }

        print_r($legs);


        $collected_legs = collect($legs);
        $min = $collected_legs->min('self_last_mlm_id');

        foreach ($legs as $leg => $members) {
            if ($members['self_last_mlm_id'] == $min) {
                $member = [
                    'leg' =>  $leg,
                    'member' =>  $members['last_member'],
                ];
                break;
            }
        }

        if ($member['member'] == null) {

            $member = [
                'leg' => $member['leg'],
                'member' => [
                    'mlm_id' => $team_leader->mlm_id
                ],
            ];
        }


        return $member;
    }




    //determine where to put a new member (places new user at the leg with least team members)
    //this considers team members not directly sponsored
    public static function stictly_where_to_place_new_user_within_team_introduced_by($team_leader_id, $tree_key = 'placement', $perferred_leg = null)
    {

        $tree = self::$tree[$tree_key];
        $user_column = $tree['column'];
        $mlm_width      = $tree['width'];

        $team_leader    = User::find($team_leader_id);
        if ($team_leader->mlm_id == '') {
            $team_leader =  User::find(1);
        }

        $legs = [];
        for ($leg = 0; $leg < $mlm_width; $leg++) {

            $legs[$leg] =  $team_leader->strict_number_at_leg($leg, $tree_key);
        }

        $collected_legs = collect($legs);
        $min = $collected_legs->min('total');

        print_r($legs);

        foreach ($legs as $leg => $members) {
            if ($members['total'] == $min) {
                $member = [
                    'leg' =>  $leg,
                    'member' =>  $members['last_member'],
                ];
                break;
            }
        }

        if ($member['member'] == null) {

            $member = [
                'leg' => $member['leg'],
                'member' => [
                    'mlm_id' => $team_leader->mlm_id
                ],
            ];
        }


        return $member;
    }



    public static function where_to_place_new_user_within_team_serially($team_leader_id, $tree_key = 'placement')
    {
        $tree = self::$tree[$tree_key];
        $user_column = $tree['column'];
        $mlm_width         = $tree['width'];

        $team_leader     = User::find($team_leader_id);

        if ($team_leader->mlm_id == '') {
            $team_leader =  User::find(1);
        }


        $team_leader_downline_level = 1;


        do {
            $downline_at_level =  $team_leader->referred_members_downlines($team_leader_downline_level, $tree_key)[$team_leader_downline_level] ?? [];


            $team_leader_downline_level;

            if ((count($downline_at_level) < $mlm_width) && ($team_leader_downline_level == 1)) {
                return $team_leader->mlm_id;
            }


            $downline_at_level_obj   = collect($downline_at_level);
            $max =  ($downline_at_level_obj->max('no_of_direct_line'));
            $min =  ($downline_at_level_obj->min('no_of_direct_line'));


            $referrer_user = null;
            foreach ($downline_at_level as $key => $downline) {
                if ($downline['no_of_direct_line'] < $mlm_width) {  //select user with list downline
                    $referrer_user = ($downline);
                    break;
                }
            }


            if ($referrer_user != null && $referrer_user['no_of_direct_line'] < $mlm_width) {
                return $referrer_user['mlm_id'];
            }

            $team_leader_downline_level++;
        } while ($referrer_user == null);
    }




    /**
     * 
     * @param   $team_leader_id [this determines the
     * placement sponsor of a new user introduced/enrolled by the
     * supplied $team_leader. 
     * the spill over is automatic and even within the downline]
     * the first downline not having complete mlm width is selected
     * @return [int]                 [description]
     */
    public static function where_to_place_new_user_within_team_introduced_by($team_leader_id, $tree_key = 'placement')
    {

        $tree = self::$tree[$tree_key];
        $user_column = $tree['column'];
        $mlm_width         = $tree['width'];

        $team_leader     = User::find($team_leader_id);

        if ($team_leader->mlm_id == '') {
            $team_leader =  User::find(1);
        }


        $team_leader_downline_level = 1;
        do {
            $downline_at_level =  $team_leader->referred_members_downlines($team_leader_downline_level, $tree_key)[$team_leader_downline_level];

            if ((count($downline_at_level) < $mlm_width) && ($team_leader_downline_level == 1)) {
                return $team_leader->mlm_id;
            }


            $downline_at_level_obj   = collect($downline_at_level);
            $max =  ($downline_at_level_obj->max('no_of_direct_line'));
            $min =  ($downline_at_level_obj->min('no_of_direct_line'));


            foreach ($downline_at_level as $key => $downline) {
                if ($downline['no_of_direct_line'] == $min) {  //select user with list downline
                    $referrer_user = ($downline);
                    break;
                }
            }

            if ($referrer_user['no_of_direct_line'] < $mlm_width) {
                return $referrer_user['mlm_id'];
            }

            $team_leader_downline_level++;
        } while ($referrer_user != null);
    }




    public function referral_link()
    {

        $username = str_replace(" ", "_", $this->username);

        $link = Config::domain() . "/r/" . $username;
        return $link;
    }


    public function next_rank()
    {

        $next_rank  = intval($this->rank) + 1;
        if ($next_rank > self::$max_level) {
            $next_rank = self::$max_level;
        }
        return $next_rank;
    }



    public function current_rank()
    {
        if ($this->rank == 0) {
            return 'N/A';
        }
        return $this->rank;
    }



    public function factorial($n)
    {
        if ($n == 1) {
            return 1;
        } else {
            return $n * $this->factorial($n - 1);
        }
    }


    /*
        This returns the volume of sales in a leg for this user
        $postion is the leg
        $tree_key determines the tree to consider
        $add_self whether to add personal sales
        $volume determine the actual volume to calcuate
    */

    public function total_volumes($position = 0, $tree_key = 'binary', $date_range = [])
    {

        $users = $this->all_downlines_at_position($position, $tree_key);
        if ($users->count() < 1) {
            return 0;
        }

        if (count($date_range) == 2) {
            $total_volume = $users->join('wallet_for_hot_wallet', function ($join) use ($date_range) {
                extract($date_range);
                $join->on('users.id', '=', 'wallet_for_hot_wallet.user_id')
                    ->where('wallet_for_hot_wallet.earning_category', 'investment')
                    ->where('wallet_for_hot_wallet.type', 'credit')
                    ->whereDate('wallet_for_hot_wallet.paid_at', '>=',  $start_date)
                    ->whereDate('wallet_for_hot_wallet.paid_at', '<=', $end_date);
            })->sum('wallet_for_hot_wallet.cost');;
        } else {

            $total_volume = $users->join('wallet_for_hot_wallet', function ($join) {
                $join->on('users.id', '=', 'wallet_for_hot_wallet.user_id')
                    ->where('wallet_for_hot_wallet.earning_category', 'investment')
                    ->where('wallet_for_hot_wallet.type', 'credit');
            })->sum('wallet_for_hot_wallet.cost');
        }
        return (int)$total_volume;
    }





    public function total_member_qualifiers_by_path($position = 0, $tree_key = 'binary')
    {

        $TheRank = $this->TheRank;
        $tree = self::$tree[$tree_key];
        $user_column = $tree['column'];

        $users = $this->all_downlines_at_position($position, $tree_key);


        $qualifiers = $users->select('rank', DB::raw('count(*) as total'), 'mlm_id', $user_column)
            ->where('rank', '!=', null)
            ->where('rank', '>', -1)
            ->groupBy('rank')->get()->toArray();


        $qualifiers_text = "";
        foreach ($qualifiers as  $qualifier) {
            if ($qualifier['rank'] == -1) {
                continue;
            }

            $count = $qualifier['total'];
            $name = $TheRank['all_ranks'][$qualifier['rank']]['name'];
            $qualifiers_text .= "$count $name <br>";
        }

        $response = compact('qualifiers', 'qualifiers_text');

        return ($response);
    }


    public function find_rank_in($position = 0, $tree_key = 'placement', $rank, $number)
    {

        return $this->all_downlines_at_position($position, $tree_key)->where('rank', $rank)->count();
    }



    public function find_rank_in_team($tree_key = 'placement', $rank)
    {
        return $this->all_downlines_by_path($tree_key)->where('rank', $rank)->count();
    }



    public static function users_with_downlines($tree_key = 'placement')
    {
    }



    public static function users_with_no_downlines($tree_key = 'placement')
    {
    }




    public function life_rank()
    {
        $rank_history = json_decode($this->rank_history, true);
        return (max(array_values($rank_history)));
    }

    public function getRankHistoryArrayAttribute()
    {
        if ($this->rank_history == '') {
            return [];
        }
        return json_decode($this->rank_history, true);
    }



    public function getTheRankAttribute()
    {

        return [];
        $rank_setting = SiteSettings::find_criteria('leadership_ranks')->settingsArray;
        // print_r($rank_setting);

        $all_ranks = $rank_setting['all_ranks'];
        $rank_qualifications = $rank_setting['rank_qualifications'];

        $next_rank  = intval($this->rank) + 1;
        if ($next_rank > self::$max_level) {
            $next_rank = self::$max_level;
        }

        if (($this->rank == -1) || ($this->rank === null)) {
            $next_rank = 0;
            $rank = [
                'all_ranks' => $all_ranks,
                'index' => $this->rank,
                'name' => "Nil",
                'rank_qualifications' => $rank_qualifications[$this->rank] ?? [],
                'next' => [
                    'index' => $next_rank,
                    'name' => $all_ranks[$next_rank]['name'],
                    'rank_qualifications' => $rank_qualifications[$next_rank],

                ]
            ];

            return $rank;
        }



        $rank = [
            'all_ranks' => $all_ranks,
            'index' => $this->rank,
            'name' => $all_ranks[$this->rank]['name'],
            'rank_qualifications' => $rank_qualifications[$this->rank],
            'next' => [
                'index' => $next_rank,
                'name' => $all_ranks[$next_rank]['name'],
                'rank_qualifications' => $rank_qualifications[$next_rank],

            ]
        ];

        return $rank;
    }






    /*the placement structure begins*/

    /**
     * [leg_of_user this returns the leg in which the suplied user is on this users team/donwline]
     * @param  string $user_id [the id of the user we want to check in this instance user]
     * @return [int]          [the actual leg not leg index ]
     */
    public function leg_of_user($user_mlm_id = '', $tree_key = 'placement')
    {
    }


    /**
     * [downline_level_of retruns the downline level of a user in this instance user team]
     * @param  string $user_id [the id of the user we want to check in this instnace user]
     */
    public function downline_level_of($user, $tree_key = 'placement')
    {
        $tree = self::$tree[$tree_key];
        $user_column = $tree['position'];

        $level = count(explode("/", $this->$user_column));
        $downline_level = count(explode("/", $user->$user_column));

        return abs($level - $downline_level);
    }



    /**
     * [user_at_leg returns the first user at the leg supplied]
     * @param  [type] $leg 
     * @return [type]      [description]
     */
    public function user_at_leg($leg, $tree_key = 'placement')
    {

        //to be reworled

    }


    /**
     * [user_legs returns array with key as this user leg and value as number of downlines]
     * @return [array] [description]
     */
    public function user_legs($tree_key = 'placement')
    {
    }



    /**
     * [referred_members_uplines fetches all this uses uplines up to the level 
     * supplied :placement structure]
     * @param  int $level [description]
     * @return [type]        [description]
     */
    public function referred_members_uplines($level, $tree_key = 'placement')
    {

        $tree = self::$tree[$tree_key];
        $user_column = $tree['column'];


        //first include self
        $this_user_uplines[0] = $this;
        $upline = $this->$user_column;

        for ($iteration = 1; $iteration <= $level; $iteration++) {

            $upline_here =    self::where('mlm_id', $upline)->where('mlm_id', '!=', null)->first();

            if ($upline_here != null) {

                $this_user_uplines[$iteration] = $upline_here;
            } else {
                break;
            }


            $upline = $this_user_uplines[$iteration][$user_column];
        }
        return  $this_user_uplines;
    }







    /*
    *@param takes the depth of doenlnes to calaclate
    *returns array of this downlines
    */
    public function referred_members_downlines($level, $tree_key = "placement")
    {
        $tree = self::$tree[$tree_key];
        $user_column = $tree['column'];




        $recruiters = [$this->mlm_id];
        for ($iteration = 1; $iteration <= $level; $iteration++) {
            $this_user_downlines[$iteration] = self::whereIn($user_column, $recruiters)->where('mlm_id', '!=', null)->get(['mlm_id'])->toArray();
            $recruiters = $this_user_downlines[$iteration];
        }

        $this_user_downlines[0][0]['mlm_id'] = $this->mlm_id;


        foreach ($this_user_downlines as $downline => $members) {
            foreach ($members as $key => $member) {
                $member_full =  $this::where('mlm_id', $member['mlm_id'])->first();
                $this_user_downlines[$downline][$key][$user_column] = $member_full->$user_column;
                $this_user_downlines[$downline][$key]['id'] = $member_full->id;
                $this_user_downlines[$downline][$key]['rank'] = $member_full->rank;
                $this_user_downlines[$downline][$key]['binary_point'] = $member_full->binary_point;
                $this_user_downlines[$downline][$key]['username'] = $member_full->username;
                $this_user_downlines[$downline][$key]['introduced_by'] = $member_full->introduced_by;
                $this_user_downlines[$downline][$key]['no_of_direct_line'] = User::where($user_column, $member['mlm_id'])->count();
            }
        }


        $this_user_downlines =  array_filter($this_user_downlines, function ($item) {
            return $item != null;
        });

        return $this_user_downlines;
    }






    public function supportTickets()
    {
        return $this->hasMany('SupportTicket', 'user_id');
    }


    public function getfullnameAttribute()
    {

        return "{$this->firstname} {$this->lastname}";
    }



    public function getfullAddressAttribute()
    {
        /*, {$this->city} {$this->state}, */
        return "{$this->address}<br>{$this->decoded_country->name}";
    }










































    /**
     * is_blocked() tells whether a user is blocked or not
     * @return boolean true when blocked and false ff otherwise
     */
    public function is_blocked()
    {
        return    boolval($this->blocked_on);
    }




    public function getresizedprofilepixAttribute($value)
    {
        $value = $this->resized_profile_pix;
        if (!file_exists($value) &&  (!is_dir($value))) {
            return (Config::default_profile_pix());
        }
        return $value;
    }

    public function getprofilepicAttribute()
    {
        $value = $this->profile_pix;
        if (!file_exists($value) &&  (!is_dir($value))) {
            return (Config::default_profile_pix());
        }

        return $value;
    }



    /**
     * [getFirstNameAttribute eloquent accessor for firstname column]
     * @param  [type] $value [description]
     * @return [string]        [description]
     */
    public function getFirstNameAttribute($value)
    {
        return ucfirst($value);
    }

    /**
     * [getFirstNameAttribute eloquent accessor for firstname column]
     * @param  [type] $value [description]
     * @return [string]        [description]
     */
    public function getLastNameAttribute($value)
    {
        return ucfirst($value);
    }


    /**
     * eloquent mutators for password hashing
     * hashes user password on insert or update
     *@return 
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
    }


    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower(trim($value));
    }

    public function setFirstnameAttribute($value)
    {
        $this->attributes['firstname'] = strtolower(trim($value));
    }

    public function setLastnameAttribute($value)
    {
        $this->attributes['lastname'] = strtolower(trim($value));
    }

    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = strtolower(trim($value));
    }
}
