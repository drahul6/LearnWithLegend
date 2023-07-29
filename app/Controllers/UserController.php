<?php

use v2\Shop\Shop;
use Stripe\Product;
use v2\Classes\Queries;
use v2\Models\Document;
use v2\Models\Commission;
use v2\Models\Investment;
use v2\Models\Withdrawal;
use v2\Security\TwoFactor;
use v2\Models\Wallet\Journals;
use Filters\Filters\OrderFilter;
use v2\Models\InvestmentPackage;
use Filters\Filters\WalletFilter;
use Filters\Filters\WithdrawalFilter;
use v2\Filters\Filters\JournalsFilter;
use v2\Filters\Filters\ProductsFilters;
use Filters\Filters\SupportTicketFilter;
use v2\Models\Wallet\Classes\AccountManager;
use Illuminate\Database\Capsule\Manager as DB;

/**
 *
 */
class UserController extends controller
{

    public function __construct()
    {
        if (!$this->admin()) {
            $this->middleware('current_user')
                ->mustbe_loggedin()
                ->must_have_verified_email();
            // ->must_have_verified_company();
        }
    }




    public function buy_now($product_id = null)
    {
    }


    function order_support()
    {
        $id = $_GET['id'];
        $order = Orders::where('id', $id)->Paid()->first();
        if ($order == null) {
            Session::putFlash("danger", "Record not found.");
            Redirect::back();
        }

        $ticket = $order->createSupportTicket();
        Redirect::to($ticket->UserLink);
    }

    public function marketplace()
    {

        $sieve = $_REQUEST;
        $query = Products::latest()->OnSale();

        $total = $query->count();

        $sieve = array_merge($sieve);
        $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $per_page = 25;
        $skip = (($page - 1) * $per_page);

        $filter = new  ProductsFilters($sieve);

        $data = $query->Filter($filter)->count();

        $sql = $query->Filter($filter);

        $products = $query->Filter($filter)
            ->offset($skip)
            ->take($per_page)
            ->get();  //filtered

        $note = MIS::filter_note($products->count(), $data, $total,  $sieve, 1);

        $this->view('auth/marketplace', get_defined_vars());
        return;
    }

    public function cart()
    {
        $this->checkout();
        return;
        $shop = new Shop;
        $cart = json_decode($_SESSION['cart'], true)['$items'] ?? [];

        if (count($cart) == 0) {
            Session::putFlash("info", "Your cart is empty.");
            Redirect::to('user/marketplace');
        }

        $this->view('auth/cart', compact('shop'));
    }

    public function checkout()
    {
        $shop = new Shop;
        $cart = json_decode($_SESSION['cart'], true)['$items'] ?? [];

        if (count($cart) == 0) {
            Session::putFlash("info", "Your cart is empty.");
            Redirect::to('user/marketplace');
        }

        $this->view('auth/checkout', compact('shop'));
    }







    public function automate_account_plan_billing()
    {
        $auth = $this->auth();
        $settings = $auth->settingsArray;
        $account_plan = $auth->settingsArray['account_plan'] ?? [];
        $account_plan['auto_billing'] = (int)!((bool)$account_plan['auto_billing']);
        $settings['account_plan'] = $account_plan;
        $auth->save_settings($settings);
        Session::putFlash("success", "Billing mode toggled successfully.");

        Redirect::back();
    }

    public function choose_membership($membership_id)
    {
        $auth = $this->auth();
        $membership = SubscriptionPlan::find($membership_id);

        if ($membership == null) {
            return;
        }

        $subscription_id = $membership_id;


        $personal_settings = $auth->SettingsArray;
        $personal_settings['membership_choice'] = $membership_id;

        $auth->save_settings($personal_settings);

        if ($membership_id < 2) {

            Redirect::back();
        }

        $response = SubscriptionPlan::create_subscription_request($subscription_id, $auth->id,  true);

        Redirect::back();
    }

    function online_services()
    {
        $this->coming_soon();
    }
    function savings()
    {
        $this->coming_soon();
    }
    function loan_center()
    {
        $this->coming_soon();
    }


    function identity_verification()
    {
        $this->view('auth/identity_verification');
    }
    function privacy_policy()
    {
        $this->view('auth/privacy_policy');
    }
    function affiliate_agreement()
    {
        $this->view('auth/affiliate_agreement');
    }
    function terms_and_condition()
    {
        $this->view('auth/terms_and_condition');
    }


    function contact_us()
    {
        $this->view('auth/contact-us');
    }



    function promotion_materials()
    {
        $this->view('auth/promotion_materials');
    }

    function basic_members_training()
    {
        $this->view('auth/basic_members_training');
    }



    function swl_training()
    {
        $this->view('auth/swl_training');
    }


    function viiral_legends_training()
    {
        $this->view('auth/viiral_legends_training');
    }


    function unifying_legends_training()
    {

        $this->view('auth/unifying_legends_training');
    }
    function legends_links_training()
    {
        $this->view('auth/legends_links_training');
    }


    public function checkSubscription()
    {
        $auth = $this->auth();
        if (!$auth->hasActiveSubscription()) {
            Session::putFlash("danger", "You have to upgrade to premium to have access.");
            Redirect::to('user/account_plan');
        }
    }

    function premium_members_training()
    {
        $this->checkSubscription();
        $this->view('auth/premium_members_trainings');
    }

    /* 
    function ai_art()
    {
        $this->checkSubscription();
        $this->view('auth/ai_art');
    }
 */
    function personal_development()
    {
        $this->checkSubscription();
        $this->view('auth/personal_development');
    }

    function real_estate()
    {
        $this->checkSubscription();
        $this->view('auth/real_estate');
    }

    function music_marketing()
    {
        $this->checkSubscription();
        $this->view('auth/music_marketing');
    }

    function affiliate()
    {
        $this->checkSubscription();
        $this->view('auth/affiliate');
    }
    function video_marketing()
    {
        $this->checkSubscription();
        $this->view('auth/video_marketing');
    }


    /* 
        <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/personal-development">Personal Development</a></li>
        <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/real_estate">Real Estate</a></li>
        <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/affiliate">Affiliate</a></li>
        <li class="sidebar-item"><a class="sidebar-link" href="<?= domain; ?>/user/video_marketing">Video Marketing</a></li>
        Personal Development: https://www.webtalklegends.com/lwl-personaldev
        Real Estate: https://www.webtalklegends.com/lwl-realestate
        Affiliate Marketing: https://www.webtalklegends.com/lwl-affiliate
        Video Marketing: https://www.webtalklegends.com/lwl-video    
    */



    function upgrade_to_pro()
    {
        $this->coming_soon();
    }

    function verify_account()
    {
        $this->coming_soon();
    }

    /*     function testimonies()
    {
        $this->view('auth/testimonies');
    }
 */
    function donate()
    {
        $this->coming_soon();
    }


    /* 
    public function site_walkthrough()
    {
        $this->view('auth/site_walkthrough');
    } */

    public function coming_soon()
    {
        $this->view('auth/coming_soon');
    }
    public function membership_orders()
    {
        $this->view('auth/membership_orders');
    }


    public function direct_ranks()
    {
        $direct_ranks = $this->auth()->referred_members_downlines(1)[1];
        $direct_ranks = User::whereIn('id', collect($direct_ranks)->where('rank', '>', -1)->pluck('id')->toArray())->get();
        $this->view('auth/direct_ranks', compact('direct_ranks'));
    }

    public function send_email_code()
    {
        echo "<pre>";

        $this->create_email_code();
    }


    public function resources($category_key = null)
    {

        $category = Document::$categories[$category_key] ?? null;

        $documents = Document::where('category', $category)->get();
        $title = "$category";

        if ($documents->isEmpty()) {
            $documents = Document::get();
            $title = "All Documents";
        }

        $this->view('auth/resources', compact('title', 'documents'));
    }

    public function faqs()
    {
        $this->view('auth/faqs');
    }

    public function supportmessages($value = '')
    {
        $this->view('auth/support-messages');
    }




    public function submit_2fa()
    {
        $auth = $this->auth();

        if ($_POST['code'] == '') {
            Session::putFlash('danger', "Invalid Code");
            Redirect::back();
        }

        $this->verify_2fa_only();


        $existing_settings = $auth->SettingsArray;

        $twofa_recovery = MIS::random_string(10);
        if (!$auth->has_2fa_enabled()) {
            $existing_settings['enable_2fa'] = 1;
            $existing_settings['2fa_recovery'] = $twofa_recovery;

            Session::putFlash('success', "2FA enabled successfully");
        } else {
            $existing_settings['enable_2fa'] = 0;
            Session::putFlash('success', "2FA disabled successfully");
        }

        $auth->save_settings($existing_settings);

        Redirect::back();
    }

    public function two_factor_authentication()
    {

        $auth = $this->auth();

        if ($auth->has_2fa_enabled()) {

            $image = null;
        } else {

            $_2FA = new TwoFactor($auth);
            $image = $_2FA->getQrCode();
        }

        $this->view('auth/two-factor-authentication', compact('image'));
    }



    public function initiate_deposit()
    {


        $rules_settings = SiteSettings::find_criteria('rules_settings');
        $min_deposit = $rules_settings->settingsArray['min_deposit_usd'];

        $this->validator()->check(Input::all(), array(
            'amount' => [
                'required' => true,
                'positive' => true,
                'min_value' => $min_deposit,
            ],
            'payment_method' => [
                'required' => true,
            ],
        ));


        if (!$this->validator->passed()) {

            Session::putFlash('danger', Input::inputErrors());
            Redirect::back();
        }





        $auth = $this->auth();

        $default_account = $auth->getAccount('default');

        DB::beginTransaction();

        $payment_details = [
            'payment_method' => Input::get('payment_method'),
            'user_id' => $auth->id,
        ];



        try {

            $deposit = AccountManager::deposit([
                'receiving_account' => $default_account->id,
                'amount' => Input::get('amount'),
                'status' => 1,
                'collect_deposit_fee' => false,
                'narration' => "deposit",
                'journal_date' => null,
            ]);

            $deposit->updateDetailsByKey('payment_details', $payment_details);
            Session::putFlash("success", "Deposit initiated...");



            //to be removed
            // $deposit->mark_paid();
            DB::commit();
            // Redirect::back();
        } catch (Exception $e) {
            DB::rollback();
            Session::putFlash("danger", "We could not initialize the payment process. Please try again");
            Redirect::back();
        }


        $callback_param = http_build_query([
            'checkout_type' => "inline",
            'item_purchased' => "deposit",
            'order_unique_id' => $deposit->id,
            'payment_method' => Input::get('payment_method'),
        ]);


        $callback_url = "shop/checkout?$callback_param";


        /*		$deposit->mark_paid();
                $shop = new Shop();
                $shop->empty_cart_in_session();
        */

        Redirect::to("$callback_url");
    }


    public function notifications($notification_id = 'all')
    {
        $auth = $this->auth();
        $per_page = 50;
        $page = $_GET['page'] ?? 1;

        switch ($notification_id) {
            case 'all':
                $notifications = Notifications::all_notifications($auth->id, $per_page, $page);
                $total = Notifications::all_notifications($auth->id)->count();
                break;

            default:

                $total = null;
                $notifications = Notifications::where('user_id', $auth->id)->where('id', $notification_id)->first();
                Notifications::mark_as_seen([$notifications->id]);

                if ($notifications == null) {
                    Session::putFlash("danger", "Invalid Request");
                    Redirect::back();
                }

                if ($notifications->DefaultUrl != $notifications->UsefulUrl) {
                    Redirect::to($notifications->UsefulUrl);
                }
                break;
        }

        $this->view('auth/notifications', compact('notifications', 'per_page', 'total'));
    }




    public function company()
    {
        $company = $this->auth()->company;
        $this->view('auth/company', compact('company'));
    }


    public function order($order_id = null)
    {
        $auth  = $this->auth();
        $order = Orders::where('id', $order_id)->where('user_id', $auth->id)->Paid()->first();

        if ($order == null) {
            Session::putFlash("danger", "Record not found.");
            Redirect::back();
        }

        $this->view('auth/order_detail', compact('order'));
    }

    public function sale($sale_id = null)
    {
        $auth  = $this->auth();
        $sale = Orders::where('id', $sale_id)->where('sellers_ids', $auth->id)->Paid()->first();

        if ($sale == null) {
            Session::putFlash("danger", "Record not found.");
            Redirect::back();
        }

        $this->view('auth/sale_detail', compact('sale'));
    }



    public function create_product()
    {

        $auth = $this->auth();
        $product = Products::create(['user_id' => $auth->id]);
        Redirect::to("user/edit_product/{$product->id}");
    }



    public function edit_product($id = null)
    {
        $auth = $this->auth();
        $product = Products::where('id', $id)->where('user_id', $auth->id)->first();

        if ($product == null) {
            Redirect::back();
        }


        $this->view('auth/edit_product', get_defined_vars());
    }



    public function download_preview($item_id = null)
    {
        $item = Products::find($item_id);

        if (($item_id == null)) {
            Redirect::back();
        }

        $item->download();
        Redirect::back();
    }
    public function download_request($order_id = null)
    {
        $auth = $this->auth();
        $order = Orders::where('id', $order_id)
            ->whereRaw("((FIND_IN_SET('$auth->id', sellers_ids)) OR  ('user_id' =$auth->id))")
            ->where('paid_at', '!=', null)
            ->first();

        if (($order_id == null)) {
            Redirect::back();
        }

        $items = $order->order_detail();
        $item_id = $items[0]['id'];

        $item = Products::find($item_id);

        if (($item == null)) {
            Redirect::back();
        }

        $item->download();
        Redirect::back();
    }


    function buy($id = null)
    {
        $product = Products::find($id);
        $auth = $this->auth();
        $product['market_details'] = $product->market_details();
        $product_array = $product->toArray();
        $product_array['qty'] = 1;

        $cart = [

            '$items' => [
                0 => $product_array
            ],


            '$total' => 0,
            '$coupon' => [],
            '$shipping_details' => [],
            '$billing_details' => [],
            '$config' => [
                'per_page' => '25',
                'model' => 'Products',
                'currency' => '$',
                'shop_link' => 'http://localhost/legends/user/marketplace'
            ],
        ];

        // echo "<pre>";
        // print_r(json_decode($_SESSION['cart']));

        unset($_SESSION['cart']);
        $_SESSION['cart'] = json_encode($cart);

        Redirect::to('user/checkout');
    }

    public function preview_product($item_id = null)
    {
        $item = Products::find($item_id);


        if (($item_id == null)) {
            Redirect::back();
        }

        $item->download();
        Redirect::back();
    }




    public function delete_product()
    {

        $auth = $this->auth();
        $product = Products::where('id', Input::get('item_id'))
            ->where('user_id', $auth->id)
            ->first();


        if ($product == null) {
            Session::putFlash("danger", "Record not found.");
            Redirect::back();
        }

        if ($product->delete()) {

            Session::putFlash("success", "{$product->name} deleted successfully.");
        }

        Redirect::back();
    }

    public function update_product()
    {

        $auth = $this->auth();
        $product = Products::where('id', Input::get('item_id'))
            ->where('user_id', $auth->id)
            ->first();


        if ($product == null) {
            Session::putFlash("danger", "Record not found.");
            Redirect::back();
        }


        $validator = new Validator;
        $validator->check(Input::all(), array(

            "composite_unique" => [
                "columns_value" => [
                    "user_id" => $auth->id,
                    "name" => Input::get('name')
                ],
                "model" => Products::class,
                "name" => "Product",
                "primary_key" => 'id',
                "find_key" => Input::get('item_id'),
            ]
        ));


        $product_name = Input::get('name');
        if (!$validator->passed()) {
            Session::putFlash("danger", "This {$product_name} already exist.");
            Redirect::back();
        }

        $update = $product->update_product(
            $_POST,
            $_FILES['front_image'],
            $_FILES['downloadable_files']
        );

        // $product->update(['status' => 1]);

        Redirect::back();
    }

    public function products()
    {

        $auth = $this->auth();

        $query = Products::where('user_id', $auth->id)->latest();

        $sieve = $_REQUEST;

        $total = $query->count();

        $sieve = array_merge($sieve);
        $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $per_page = 20;
        $skip = (($page - 1) * $per_page);

        $filter = new ProductsFilters($sieve);

        $data = $query->Filter($filter)->count();

        $products = $query->Filter($filter)
            ->offset($skip)
            ->take($per_page)
            ->get();  //filtered

        $note = MIS::filter_note($products->count(), $data, $total,  $sieve, 1);



        $this->view('auth/products', get_defined_vars());
    }


    public function products_orders()
    {
        $this->view('auth/products_orders');
    }


    public function view_cart()
    {

        $cart = json_decode($_SESSION['cart'], true)['$items'];

        if (count($cart) == 0) {
            Session::putFlash("info", "Your cart is empty.");
            Redirect::to('user/marketplace');
        }
        $this->view('auth/view_cart');
    }




    public function create_upgrade_request($subscription_id = null)
    {

        $subscription_id = $_REQUEST['subscription_id'];

        $response = SubscriptionPlan::create_subscription_request($subscription_id, $this->auth()->id);


        header("content-type:application/json");
        echo $response;

        Redirect::back();
    }


    public function invoices()
    {
        $packs = InvestmentPackage::for($this->auth()->id)->latest()->get();

        $this->view('auth/invoices', compact('packs'));
    }



    public function investments()
    {
        $auth = $this->auth();

        $investments = Investment::where('user_id', $auth->id)->latest()->get();

        $this->view('auth/investments', compact('investments'));
    }


    public function select_pack()
    {

        $investment = InvestmentPackage::find($_POST['investment_id']);

        if ($investment == null) {
            Session::putFlash('danger', "Invalid Request");
            Redirect::back();
        }

        $wallet = new Wallet;

        $this->view('auth/select_pack', compact('investment', 'wallet'));
    }

    public function submit_investment()
    {

        echo "<pre>";
        $pack = InvestmentPackage::find($_POST['pack_id']);



        if ($pack == null) {
            Session::putFlash('danger', "Invalid Request");
            echo "not in range";
            // Redirect::back();
        }


        $this->validator()->check(Input::all(), array(

            'wallet' => [
                // 'required' => true,
            ],
            'pack_id' => [
                'required' => true,
            ],
        ));



        if (!$this->validator->passed()) {
            Session::putFlash('danger', Input::inputErrors());
            Redirect::back();
        }

        $auth = $this->auth();

        $amount = $pack->DetailsArray['min_capital'];

        DB::beginTransaction();


        try {
            //debit this user

            //debit user
            $comment = "Purchased $pack->name for $amount";
            $debit = Wallet::createTransaction(
                'debit',
                $auth->id,
                null,
                $amount,
                'completed',
                'deposit',
                $comment,
                null,
                null,
                null
            );

            if ($debit == false) {

                throw new Exception("Could not debit", 1);
            }





            //create investment

            $investment = Investment::create([
                'user_id' => $auth->id,
                'pack_id' => $pack->id,
                'capital' => $amount,
                'worth_after_maturity' => $pack->getWorthAfterMaturity()['roi_and_capital'],
                'currency_id' => null,
                'matures_at' => $pack->getMaturityTimeFrom(),
                'status' => 1,
                'extra_detail' => $pack->toJson(),
            ]);


            if ($investment == false) {
                throw new Exception("Could not create investment", 1);
            }


            $debit->update([
                'payment_method' => 'pack',
                'order_id' =>  $investment->id,
            ]);


            $investment->give_referral_commission();

            DB::commit();
            Session::putFlash('success', "{$investment->pack->name} purchased successfully");
        } catch (Exception $e) {
            DB::rollback();
            // print_r($e->getMessage());
            Session::putFlash('danger', 'Action Failed');
        }


        Redirect::to("user/purchase-investment");
    }


    public function purchase_investment()
    {
        $shop = new Shop;
        $wallet = new Wallet;
        $this->view('auth/purchase_investment', compact('shop', 'wallet'));
    }

    public function account_plan()
    {

        $this->view('auth/account_plan');
    }


    public function reports()
    {
        $this->view('auth/report');
    }


    public function submit_user_transfers()
    {
        $rules_settings = SiteSettings::find_criteria('rules_settings');
        $transfer_fee = $rules_settings->settingsArray['user_transfer_fee_percent'];
        $min_transfer = $rules_settings->settingsArray['min_transfer_usd'];


        $this->validator()->check(Input::all(), array(
            'amount' => [
                'required' => true,
                'positive' => true,
                'min_value' => $min_transfer,
            ],
            'wallet' => [
                // 'required' => true,
            ],

            'username' => [
                'required' => true,
                'exist' => 'User|username',
            ],

        ));


        if (!$this->validator->passed()) {

            Session::putFlash('danger', Input::inputErrors());
            Redirect::back();
        }

        $this->verify_2fa();


        $auth = $this->auth();
        $amount = Input::get('amount');
        $username = Input::get('username');
        $to = User::where('username', $username)->first();
        $sending_account = $auth->getAccount('default');
        $receiving_account = $to->getAccount('default');



        $transfer = AccountManager::transfer([
            'sending_account' => $sending_account->id,
            'receiving_account' => $receiving_account->id,
            'amount' => $amount,
            'status' => 3,
            'collect_transfer_fee' => "receiver",
            'tag' => "transfer",
            'narration' => "transfer",
            'journal_date' => null,
        ]);



        if ($transfer == true) {

            Session::putFlash('success', "transfer initiated successfully to $username");
        } else {

            Session::putFlash('danger', "Transfer Failed");
        }

        Redirect::back();
    }


    public function donate_to_pool()
    {
        $rules_settings = SiteSettings::find_criteria('rules_settings');
        $min_donation = $rules_settings->settingsArray['min_donation'];


        $this->validator()->check(Input::all(), array(
            'amount' => [
                'required' => true,
                'positive' => true,
                'min_value' => $min_donation,
            ],
        ));

        $this->verify_2fa();


        if (!$this->validator->passed()) {

            Session::putFlash('danger', Input::inputErrors());
            Redirect::back();
        }


        $auth = $this->auth();
        $amount = Input::get('amount');
        $donating_account = $auth->getAccount('default');

        $donation = AccountManager::donateToPool([
            'donating_account' => $donating_account,
            'amount' => $amount,
            'tag' => "donation",
            'narration' => "donation",
            'journal_date' => null,
            'user_id' => $auth->id,
        ]);



        if ($donation == true) {

            Session::putFlash('success', "donation is successful");
        } else {
            Session::putFlash('danger', "donation Failed");
        }

        Redirect::back();
    }




    public function wallet()
    {
        $auth = $this->auth();
        $shop = new Shop;


        $wallet = $auth->getAccount('default');



        $per_page = 50;
        $page = (isset($_GET['page'])) ? $_GET['page'] : 1;

        $sieve = $_REQUEST;
        $journal_sieve  = $sieve['journal'] ?? [];
        $line_items_sieve  = $sieve['line_items'] ?? [];

        $journal_sieve['status'] = "2,3,4";

        $transactions = $wallet->transactions($per_page, $page, $journal_sieve, $line_items_sieve, "DESC");



        $rules_settings =  SiteSettings::find_criteria('rules_settings');
        $min_deposit = $rules_settings->settingsArray['min_deposit_usd'];
        $transfer_fee = $rules_settings->settingsArray['user_transfer_fee_percent'];
        $min_transfer = $rules_settings->settingsArray['min_transfer_usd'];
        $min_withdrawal_usd = $rules_settings->settingsArray['min_withdrawal_usd'];
        $withdrawal_fee_percent = $rules_settings->settingsArray['withdrawal_fee_percent'];
        $min_donation = $rules_settings->settingsArray['min_donation'];

        $balance = $wallet->get_balance();

        $this->view('auth/wallet', get_defined_vars());
    }


    public function withdrawal_methods()
    {
        $this->view('auth/withdrawal_methods');
    }




    public function withdrawals()
    {

        $query = Withdrawal::where('user_id', $this->auth()->id)->latest();


        $sieve = $_REQUEST;
        // ->where('status', 1);  //in review
        $sieve = array_merge($sieve);
        $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $per_page = 50;
        $skip = (($page - 1) * $per_page);

        $filter = new  WithdrawalFilter($sieve);

        $data = $query->Filter($filter)->count();

        $withdrawals = $query->Filter($filter)
            ->offset($skip)
            ->take($per_page)
            ->get();  //filtered



        $this->view('auth/withdrawal-history', compact('withdrawals', 'sieve', 'data', 'per_page'));

        // $this->view('auth/withdrawal-history', compact('withdrawals'));
    }



    public function fetch_testimonial($testimony_id)
    {
        $testimony = Testimonials::find($testimony_id);
        header("content-type:application/json");

        echo $testimony;
    }

    public function update_testimonial()
    {

        echo "<pre>";
        $testimony_id = Input::get('testimony_id');

        $auth = $this->auth();

        $testimony = Testimonials::where('user_id', $auth->id)->where('id', $testimony_id)->NotApproved()->first();

        $attester = $auth->lastname . ' ' . $auth->firstname;

        Testimonials::updateOrCreate(
            [
                'id' => $_POST['testimony_id']
            ],
            [
                'attester' => $attester,
                'user_id' => $auth->id,
                'content' => Input::get('testimony'),
                'type' => Input::get('type'),
                'video_link' => Input::get('video_link'),
                'intro' => Input::get('intro'),
                'approval_status' => 0
            ]
        );


        Session::putFlash('success', 'Testimonial updated successfully. Awaiting approval');

        Redirect::back();
    }


    public function create_testimonial()
    {
        if (Input::exists() || true) {

            $auth = $this->auth();

            $testimony = Testimonials::create([
                'attester' => $auth->lastname . ' ' . $auth->firstname,
                'user_id' => $auth->id,
                'content' => Input::get('testimony')
            ]);
        }
        Redirect::to("user/edit_testimony/{$testimony->id}");
    }


    public function edit_testimony($testimony_id = null)
    {
        $testimony = Testimonials::where('user_id', $this->auth()->id)->where('id', $testimony_id)->NotApproved()->first();

        if (($testimony == null)) {
            Session::putFlash('danger', 'Invalid Request');
            Redirect::back();
        }


        $this->view('auth/edit_testimony', ['testimony' => $testimony]);
    }


    public function view_testimony()
    {
        $this->view('auth/view-testimony');
    }


    public function testimony()
    {

        $auth = $this->auth();
        $testimonials = Testimonials::where('user_id', $auth->id)->latest()->get();
        $this->view('auth/testimony', compact('testimonials'));
    }


    public function documents()
    {
        $show = false;
        $this->view('auth/documents', compact('show'));
    }


    public function news()
    {
        $this->view('auth/news');
    }

    public function language()
    {
        $this->view('auth/language');
    }


    public function profile()
    {
        $this->view('auth/profile');
    }

    public function upload_payment_proof()
    {
        $order_id = $_POST['order_id'];
        $order = SubscriptionOrder::find($order_id);
        $order->upload_payment_proof($_FILES['payment_proof']);
        Session::putFlash('success', "#$order_id Proof Uploaded Successfully!");
        Redirect::back();
    }


    public function support()
    {
        $auth = $this->auth();

        $sieve = $_REQUEST;
        $sieve = array_merge($sieve);

        $query = SupportTicket::whereRaw("find_in_set({$auth->id}, user_id)")->latest();
        // ->where('status', 1);  //in review
        $sieve = array_merge($sieve);
        $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $per_page = 50;
        $skip = (($page - 1) * $per_page);

        $filter = new  SupportTicketFilter($sieve);

        $data = $query->Filter($filter)->count();

        $tickets = $query->Filter($filter)
            ->offset($skip)
            ->take($per_page)
            ->get();  //filtered


        $this->view('auth/support', compact('tickets', 'sieve', 'data', 'per_page'));
    }


    public function view_ticket($ticket_id)
    {

        $support_ticket = SupportTicket::find($ticket_id);

        $this->view('auth/support-messages', [
            'support_ticket' => $support_ticket
        ]);
    }


    public function index()
    {
        $this->dashboard();
    }


    public function accounts()
    {
        $this->view('auth/accounts');
    }


    public function change_password()
    {
        $this->accounts();
    }



    function mark_order_as_disputed($order_id = null)
    {
        $auth = $this->auth();

        //buyer only
        $order = Orders::where('user_id', $auth->id)->where('id', $order_id)->Paid()->first();

        if ($order == null) {
            Session::putFlash("danger", "Order not found.");
            Redirect::back();
        }


        try {
            $order->mark_order_as_disputed();
        } catch (\Throwable $th) {
            Session::putFlash("danger", "You cannot dispute this order.");
        }


        Redirect::back();
    }


    function mark_order_as_delivered($order_id = null)
    {
        $auth = $this->auth();

        //seller only
        $order = Orders::where('sellers_ids', $auth->id)->where('id', $order_id)->Paid()->first();

        if ($order == null) {
            Session::putFlash("danger", "Order not found.");
            Redirect::back();
        }

        $order->mark_order_as_delivered();

        Redirect::back();
    }

    function mark_order_as_received($order_id = null)
    {
        $auth = $this->auth();

        //buyer only
        $order = Orders::where('user_id', $auth->id)->where('id', $order_id)->Paid()->first();

        if ($order == null) {
            Session::putFlash("danger", "Order not found.");
            Redirect::back();
        }


        $order->mark_order_as_received();
        Redirect::back();
    }


    public function orders()
    {
        $auth = $this->auth();

        $query = Orders::where('user_id', $auth->id)->Paid()->latest();

        $sieve = $_REQUEST;
        // $sieve = array_merge($sieve, $extra_sieve);
        $total = $query->count();
        // ->where('status', 1);  //in review
        $sieve = array_merge($sieve);
        $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $per_page = 20;
        $skip = (($page - 1) * $per_page);

        $filter = new OrderFilter($sieve);

        $data = $query->Filter($filter)->count();

        $orders = $query->Filter($filter)
            ->offset($skip)
            ->take($per_page)
            ->get();  //filtered

        $note = MIS::filter_note($orders->count(), $data, $total,  $sieve, 1);


        foreach ($orders as $key => $order) {
            //this makes live edit of ad reflect in order purchases
            $order->order_detail = $order->delivery_details();
        }

        $shop = new Shop;

        $this->view('auth/orders', get_defined_vars());
    }

    public function sales()
    {
        $auth = $this->auth();

        $query = Orders::whereRaw("find_in_set($auth->id, sellers_ids)")->Paid()->latest();

        $sieve = $_REQUEST;
        // $sieve = array_merge($sieve, $extra_sieve);
        $total = $query->count();
        // ->where('status', 1);  //in review
        $sieve = array_merge($sieve);
        $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $per_page = 20;
        $skip = (($page - 1) * $per_page);

        $filter = new OrderFilter($sieve);

        $data = $query->Filter($filter)->count();

        $sales = $query->Filter($filter)
            ->offset($skip)
            ->take($per_page)
            ->get();  //filtered

        $note = MIS::filter_note($sales->count(), $data, $total,  $sieve, 1);

        $shop = new Shop;

        $this->view('auth/sales', get_defined_vars());
    }


    private  function getDonationList($month = null)
    {
        $sieve = [
            "status" => "3,4",
            "tag" => "donation",
            "journal_date" => [
                "start_date" => date("$month-01"),
                "end_date" => date("$month-t"),
            ],
        ];

        $filter = new JournalsFilter($sieve);
        $data = Journals::query()->Filter($filter)->take(50)->latest()->get();

        return $data;
    }

    public function dashboard()
    {
        $user = $this->auth();
        $default_wallet = $user->getAccount('default');
        $wallet_summary = $user->walletSummary();

        $pools_summary = AccountManager::getPoolsSummary();

        $this_month =  date("Y-m");
        $last_month =  date("Y-m", strtotime("-1 month"));

        $this_month_donation_list = $this->getDonationList($this_month);
        $last_month_donation_list = $this->getDonationList($last_month);

        $total_premium_members = (new Queries)->getAllPremiumMembers();

        $this->view('auth/dashboard', get_defined_vars());
    }

    public function broadcast()
    {

        $auth = $this->auth();

        $sieve = $_REQUEST;
        $query = BroadCast::Published()->latest();
        // ->where('status', 1);  //in review
        $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $per_page = 50;
        $skip = (($page - 1) * $per_page);

        $data = $query->count();

        $news = $query
            ->offset($skip)
            ->take($per_page)
            ->get();  //filtered


        $this->view('auth/broadcast', compact('news', 'sieve', 'data', 'per_page'));
    }
}
