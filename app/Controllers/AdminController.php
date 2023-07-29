<?php

use v2\Shop\Shop;
use v2\Models\Wallet;
use v2\Models\Document;
use v2\Models\Withdrawal;
use v2\Models\UserDocument;
use v2\Models\Wallet\Journals;
use Filters\Filters\UserFilter;
use Filters\Filters\OrderFilter;
use v2\Models\InvestmentPackage;
use Filters\Filters\WalletFilter;
use v2\Models\Wallet\ChartOfAccount;
use Filters\Filters\WithdrawalFilter;
use Filters\Filters\UserDocumentFilter;
use v2\Filters\Filters\ProductsFilters;
use Filters\Filters\SupportTicketFilter;
use Filters\Filters\SubscriptionOrderFilter;
use v2\Models\Wallet\Classes\AccountManager;
use Illuminate\Database\Capsule\Manager as DB;



/**
 * this class is the default controller of our application,
 *
 */
class AdminController extends controller
{


    public function __construct()
    {
        $this->middleware('administrator')->mustbe_loggedin();
    }




    public function investment_exempt()
    {
        $auth = User::find($_POST['id']);
        $personal_settings = $auth->SettingsArray;
        $personal_settings['exempt_investments_from_tenure_weeks_check'] = $_POST['state'];

        DB::beginTransaction();

        try {

            $auth->save_settings($personal_settings);
            DB::commit();
            Session::putFlash("success", "Changes saved successfully");
        } catch (Exception $e) {
            DB::rollback();
            Session::putFlash("danger", "Something went wrong");
        }
    }


    public function toggle_block_payout_wallet()
    {
        $auth = User::find($_POST['user_id']);
        $personal_settings = $auth->SettingsArray;

        $state = $personal_settings['has_blocked_payout_wallet'] ?? 0;
        if ($state == 1) {
            $next = 0;
            $text = "Unblocked Payout wallet";
        } else {
            $next = 1;
            $text = "Blocked Payout Wallet";
        }

        $personal_settings['has_blocked_payout_wallet'] = $next;

        DB::beginTransaction();

        try {

            $auth->save_settings($personal_settings);
            DB::commit();
            Session::putFlash("success", "$text successfully");
        } catch (Exception $e) {
            DB::rollback();
            Session::putFlash("danger", "Something went wrong");
        }

        // has_blocked_payout_wallet
    }



    public function edit_client_detail($client_id)
    {
        $client_id = MIS::dec_enc('decrypt', $client_id);
        $user = User::find($client_id);

        if ($user == null) {
            Session::putFlash("danger", "Client not found");
            Redirect::back();
        }


        $this->view('admin/edit_client_profile', compact('user'));
    }



    public function user_verification()
    {


        $sieve = $_REQUEST;
        $query = UserDocument::latest();
        // ->where('status', 1);  //in review


        $sieve = array_merge($sieve);
        $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $per_page = 50;
        $skip = (($page - 1) * $per_page);

        $filter = new  UserDocumentFilter($sieve);

        $data = $query->Filter($filter)->count();

        $documents = $query->Filter($filter)
            ->offset($skip)
            ->take($per_page)
            ->get();  //filtered


        $this->view('admin/user_verification', compact('documents', 'sieve', 'data', 'per_page'));
    }


    public function search($query = null)
    {

        $compact = $this->users_matters(['name' => $query]);
        $users = $compact['users'];
        $line = "";
        foreach ($users as $key => $user) {
            $username = $user->username;
            $fullname = $user->fullname;
            $line .= "<option value='$username'> $fullname ($username)</option>";
        }

        header("content-type:application/json");
        echo json_encode(compact('line'));
    }


    public function submit_manual_credit()
    {


        $this->validator()->check(Input::all(), array(
            'amount' => [
                'required' => true,
                'positive' => true,
            ],

            'category' => [
                'required' => true,
            ],

            'type' => [
                'required' => true,
            ],
            'comment' => [
                'required' => true,
            ],

            'paid_at' => [
                'date' => "Y-m-d",
            ],

            'username' => [
                'required' => true,
                'exist' => 'User|username',
            ],

        ));


        if (!$this->validator()->passed()) {

            Session::putFlash("danger", Input::inputErrors());
            Redirect::back();
        }


        $receiver = User::where('username', $_POST['username'])->first();

        if ($receiver == null) {

            Session::putFlash("danger", "User not found. Please check the username entered");
            Redirect::back();
        }


        $amount = $_POST['amount'];



        $wallet_to_use = Wallet::$wallet_classes[$_POST['wallet']];
        $wallet_class = $wallet_to_use['class'];


        DB::beginTransaction();




        try {


            $direct_bonus_commission = $wallet_class::createTransaction(
                $_POST['type'],
                $receiver['id'],
                null,
                $amount,
                'completed',
                $_POST['category'],
                $_POST['comment'],
                null,
                null,
                null,
                null,
                $_POST['paid_at'],
                null,
                false
            );


            $direct_bonus_commission->update(['admin_id' => $this->admin->id]);

            DB::commit();
            Session::putFlash("success", "$amount {$_POST['type']} successful");
        } catch (Exception $e) {
            DB::rollback();
            Session::putFlash("danger", "Something went wrong.");
        }


        Redirect::back();
    }

    public function donate_to_pool()
    {



        echo "<pre>";

        print_r($_POST);


        $this->validator()->check(Input::all(), array(
            'amount' => [
                'required' => true,
                'positive' => true,
            ],
            'type' => [
                'required' => true,
            ],
            'comment' => [],
        ));


        if (!$this->validator()->passed()) {
            Session::putFlash("danger", Input::inputErrors());
            Redirect::back();
        }



        $amount = Input::get('amount');
        $comment = Input::get('comment');
        $type = Input::get('type');


        DB::beginTransaction();

        try {

            $donation = AccountManager::donateToPoolByAdmin([
                'amount' => $amount,
                'tag' => "donation",
                'narration' => $comment,
                'type' => $type,
            ]);

            if (!$donation) {

                throw new Exception("could not complete process", 1);
            }

            DB::commit();
            Session::putFlash("success", "changes successful");
        } catch (Exception $e) {
            DB::rollback();
            Session::putFlash("danger", "Something went wrong.");
        }


        Redirect::back();
    }

    public function faqs()
    {
        $this->view("admin/faqs");
    }


    public function manual_credit()
    {
        $this->view("admin/manual_credit");
    }

    public function monthly_pool()
    {
        $this->view("admin/monthly_pool");
    }


    public function support_messages()
    {

        $this->view('admin/support-messages');
    }




    private function wallet_matters($extra_sieve, $class, $category = null)
    {

        $sieve = $_REQUEST;
        $sieve = array_merge($sieve, $extra_sieve);



        $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $per_page = 50;
        $skip = (($page - 1) * $per_page);

        $filter = new  WalletFilter($sieve);



        $query = $class::latest()->Filter($filter);


        $total_credit  = $query->Credit()->Completed()->sum('amount');

        $query = $class::latest()->Filter($filter);
        $total_debit  = $query->Debit()->Completed()->sum('amount');

        $total_net = $total_credit - $total_debit;


        $query = $class::latest()->Category($category);
        $total_set = $query->count();
        // ->where('status', 1);  //in review


        $data = $query->Filter($filter)->count();

        // echo $query->toSql();/

        $records = $query->Filter($filter)
            ->offset($skip)
            ->take($per_page)
            ->get();  //filtered



        $note = MIS::filter_note($records->count(), $data, $total_set,  $sieve, 1);

        return compact('records', 'sieve', 'data', 'per_page', 'note', 'total_debit', 'total_credit', 'total_net');
    }


    public function withdrawals()
    {

        $sieve = $_REQUEST;
        // $sieve = array_merge($sieve, $extra_sieve);

        $query = Withdrawal::latest();
        $total = $query->count();
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

        $note = MIS::filter_note($withdrawals->count(), $data, $total,  $sieve, 1);

        $this->view('admin/withdrawal-history', compact('withdrawals', 'sieve', 'data', 'per_page', 'note'));
    }


    public function payout_wallets()
    {
        $compact = $this->wallet_matters([], 'v2\Models\PayoutWallet');

        extract($compact);
        $page_title = 'Payout Wallet';
        $wallet = 'payout';
        $this->view('admin/deposits', compact('records', 'sieve', 'data', 'per_page', 'page_title', 'wallet', 'note', 'total_credit', 'total_debit', 'total_net'));
    }


    public function commissions()
    {
        $compact = $this->wallet_matters([], 'v2\Models\Commission');

        extract($compact);
        $page_title = 'Commissions';
        $wallet = 'commission';

        $this->view('admin/deposits', compact('records', 'sieve', 'data', 'per_page', 'page_title', 'wallet', 'note', 'total_credit', 'total_debit', 'total_net'));
    }


    public function ranks()
    {
        $compact = $this->wallet_matters([
            'earning_category' => 'rank'
        ], 'v2\Models\Commission');

        extract($compact);
        $page_title = 'Ranks Earning';

        $wallet = 'hotwallet';
        $this->view('admin/deposits', compact('records', 'sieve', 'data', 'per_page', 'page_title', 'wallet', 'note', 'total_credit', 'total_debit', 'total_net'));
    }


    public function deposits()
    {
        $compact = $this->wallet_matters([
            'earning_category' => 'deposit'
        ], 'v2\Models\Wallet');

        extract($compact);
        $page_title = 'Deposits';

        $wallet = 'deposit';
        $this->view('admin/deposits', compact('records', 'sieve', 'data', 'per_page', 'page_title', 'wallet', 'note', 'total_credit', 'total_debit', 'total_net'));
    }


    public function referrals($username = null, $level_of_referral = 1, $tree_key = 'placement')
    {
        $this->view('admin/mlm_list', get_defined_vars());
    }



    public function tree($user_id = '', $tree_key = 'placement', $requested_depth = null)
    {


        if (!in_array($tree_key, array_keys(User::$tree))) {
            // Session::putFlash("danger","Invalid Request");
            Redirect::to('genealogy/placement');
            die();
        }

        $user = User::where('username', $user_id)->first() ?? User::find(1)->first();

        $tree = User::$tree[$tree_key];
        $user_column = $tree['column'];

        $this->view('admin/tree', get_defined_vars());
    }



    private function users_matters($extra_sieve)
    {

        $sieve = $_REQUEST;
        $sieve = array_merge($sieve, $extra_sieve);

        $query = User::latest();
        // ->where('status', 1);  //in review
        $sieve = array_merge($sieve);
        $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $per_page = 50;
        $skip = (($page - 1) * $per_page);

        $filter = new  UserFilter($sieve);

        $data = $query->Filter($filter)->count();

        $sql = $query->Filter($filter);

        $users = $query->Filter($filter)
            ->offset($skip)
            ->take($per_page)
            ->get();  //filtered


        $note = MIS::filter_note($users->count(), $data, User::count(),  $sieve, 1);


        return compact('users', 'sieve', 'data', 'per_page', 'note');
    }



    public function choose_membership($user_id = null, $subscription_id = null)
    {

        if (($user_id == null) || ($subscription_id == null)) {
            Session::putFlash("danger", "Invalid Request");
            Redirect::back();
        }

        $response = SubscriptionPlan::create_subscription_request($subscription_id, $user_id, null, true);
        Redirect::back();
    }



    public function user($user_id = null, $action = null)
    {


        if (($user_id == null) || ($action == null)) {
            Session::putFlash("danger", "Invalid Request");
            Redirect::back();
        }

        switch ($action) {
            case 'subscription':

                $user = User::find($user_id);
                $this->view('admin/user_subscription', compact('user'));
                return;
                break;

            default:
                # code...
                break;
        }


        Session::putFlash("danger", "Invalid Request");
        Redirect::back();
    }



    public function users()
    {


        $compact = $this->users_matters([]);
        extract($compact);
        $page_title = 'Users';

        $this->view('admin/users', compact('users', 'sieve', 'data', 'per_page', 'page_title', 'note'));
    }


    private function ticket_matters($extra_sieve)
    {

        $sieve = $_REQUEST;
        $sieve = array_merge($sieve, $extra_sieve);

        $query = SupportTicket::latest();
        $total_set = $query->count();
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


        $note = MIS::filter_note($tickets->count(), $data, $total_set,  $sieve, 1);

        return compact('tickets', 'sieve', 'data', 'per_page', "note");
    }


    public function support_tickets()
    {
        $compact = $this->ticket_matters([]);
        extract($compact);
        $page_title = 'Tickets';


        $this->view('admin/all_tickets', compact('tickets', 'sieve', 'data', 'per_page', 'page_title', "note"));
    }


    public function closed_tickets()
    {
        $sieve = ['status' => 1];
        $compact = $this->ticket_matters($sieve);
        extract($compact);
        $page_title = 'Closed Tickets';

        $this->view('admin/all_tickets', compact('tickets', 'sieve', 'data', 'per_page', 'page_title'));
    }


    public function update_cms()
    {

        DB::beginTransaction();

        try {

            CMS::updateOrCreate([
                'criteria' => $_POST['criteria']
            ], [
                'settings' => $_POST['settings'],
            ]);


            DB::commit();
            Session::putFlash("success", "Changes Saved");
        } catch (Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
        }

        Redirect::back();
    }


    public function cms()
    {
        $this->view('admin/cms');
    }


    public function simulate_packages()
    {
        $this->view('admin/simulate_packages');
    }


    public function package_invoice($order_id = null)
    {

        $order = SubscriptionOrder::where('id', $order_id)->first();

        if ($order == null) {
            Redirect::back();
        }

        $order->invoice();
    }



    public function product_status()
    {

        if ($_GET['status'] == 'approved') {
            $product = Products::find($_GET['id']);
            $rules = Products::getValidationRule($product->user_id, $product);
            $validator = new Validator;
            $validator->check($product->toArray(), $rules);


            if (!file_exists($product->downloadable_files)  && Input::get('type_of_product') == 'digital') {
                $validator->addError("Digital file", "Downloadable file must be provided.");
            }

            if (!$validator->passed()) {

                Session::putFlash("danger", Input::inputErrors());
                Redirect::back();
            }
        }

        Products::pushStatus($_GET['id'], $_GET['status']);
        Redirect::back();
    }


    function order_download_request($order_id = null)
    {
        $order = Orders::where('id', $order_id)
            ->where('paid_at', '!=', null)
            ->first();

        $item_id = $order->order_detail()[0]['id'];
        $item = Products::find($item_id);

        if (($order_id == null) || ($item_id == null) || ($order == null) || (!$order->has_item($item_id))) {
            Redirect::back();
        }

        $item->download();
        Redirect::back();
    }

    function mark_order_as_delivered($order_id = null)
    {

        //seller only
        $order = Orders::where('id', $order_id)->Paid()->first();

        if ($order == null) {
            Session::putFlash("danger", "Order not found.");
            Redirect::back();
        }

        $order->mark_order_as_delivered();

        Redirect::back();
    }

    function mark_order_as_received($order_id = null)
    {
        //buyer only
        $order = Orders::where('id', $order_id)->Paid()->first();

        if ($order == null) {
            Session::putFlash("danger", "Order not found.");
            Redirect::back();
        }


        $order->mark_order_as_received();
        Redirect::back();
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
        Redirect::to($ticket->adminLink);
    }

    public function products_orders()
    {

        $query = Orders::Paid()->latest();

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

        $this->view('admin/products_orders', get_defined_vars());
    }


    public function fetch_subscription()
    {

        header("content-type:application/json");
        echo SubscriptionPlan::all();
    }


    public function order($order_id = null)
    {

        $order = Orders::where('id', $order_id)->first();


        if ($order == null) {
            Redirect::back();
        }


        $this->view('admin/order_detail', compact('order'));
    }


    public function update_subscription_plans()
    {


        foreach ($_POST['plan'] as $plan_id => $plan) {

            $subscription_plan = SubscriptionPlan::find($plan_id);
            $subscription_plan->update(['availability' => '']);
            print_r($subscription_plan->toArray());
            $subscription_plan->update($plan);
        }

        Session::putFlash("success", "Updated Succesfully.");

        Redirect::back();
    }

    public function products()
    {

        $auth = $this->auth();

        $query = Products::latest();

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



        $this->view('admin/products', get_defined_vars());
    }



    public function account_plans()
    {

        $this->view('admin/account_plans');
    }


    public function investment_ranges()
    {

        $this->view('admin/investment_ranges');
    }

    public function update_account_plans()
    {


        print_r($_POST);

        // return;
        $this->validator()->check(Input::all(), array(
            'name' => [
                'required' => true,
            ],

            'id' => [
                'required' => true,
            ],

            'price' => [
                // 'required'=> true,
            ],

        ));


        if (!$this->validator->passed()) {

            Session::putFlash('danger', Input::inputErrors());
            Redirect::back();
        }


        $plan = Input::all();

        $account_plan = SubscriptionPlan::find($_POST['id']);


        if ($account_plan == null) {
            Session::putFlash('danger', "Invalid Request");
            Redirect::back();
        }

        $account_plan->update(['availability' => null]);
        print_r($account_plan->toArray());
        $account_plan->update([
            'name' => $_POST['name'],
            'price' => $_POST['price'],
            'hierarchy' => $_POST['hierarchy'],
            'details' => json_encode($_POST['details']),
            'features' => $_POST['features'],
            'availability' => $_POST['availability'],
        ]);

        Session::putFlash('success', "$account_plan->name updated successfully ");

        Redirect::back();
    }


    public function update_investment_package()
    {


        $this->validator()->check(Input::all(), array(
            'name' => [
                'required' => true,
            ],

            'id' => [
                'required' => true,
            ],

            'category' => [
                'required' => true,
            ],

        ));


        if (!$this->validator->passed()) {

            Session::putFlash('danger', Input::inputErrors());
            Redirect::back();
        }


        $plan = Input::all();
        $category = InvestmentPackage::$categories[$plan['category']]['name'];

        $investment = InvestmentPackage::find($_POST['id']);


        if ($investment == null) {
            Session::putFlash('danger', "Invalid Request");
            Redirect::back();
        }


        $investment->update(['availability' => '']);
        print_r($investment->toArray());
        $investment->update([
            'name' => $_POST['name'],
            'details' => json_encode($_POST['details']),
            'features' => $_POST['features'],
            'availablity' => $_POST['availablity'],
            'category' => $category
        ]);

        Session::putFlash('success', "$investment->name updated successfully ");

        Redirect::back();
    }


    public function add_book()
    {

        $ebook = Ebooks::create([]);

        Redirect::to("admin/edit_book/{$ebook->id}");
    }


    public function download_request($product_id)
    {
        $product = Products::find($product_id);
        $product->download();
        Redirect::back();
    }







    public function fetch_documents_list()
    {


        $documents_settings = SiteSettings::documents_settings();

        header("content-type:application/json");

        $documents = ($documents_settings);


        echo json_encode(compact('documents'));
    }


    public function upload_supporting_document()
    {


        $documents_settings = SiteSettings::where('criteria', 'documents_settings')->first();

        $files = MIS::refine_multiple_files($_FILES['files']);


        foreach ($files as $key => $value) {
            $value['category'] = $_POST['category'][$key];
            $files[$key] = $value;
        }

        $combined_files = array_combine($_POST['label'], $files);

        Document::upload_documents($combined_files);
        // $response = $documents_settings->upload_documents($combined_files);
        Redirect::back();
    }


    public function delete_doc($id)
    {
        $document = Document::find($id);
        if ($document == null) {
            Session::putFlash("danger", "Document not found");
            Redirect::back();
        }

        DB::beginTransaction();
        try {

            $document->delete();
            DB::commit();
            Session::putFlash("success", "Document deleted succesfully");
        } catch (Exception $e) {
            Session::putFlash("danger", "Something went wrong");
        }

        Redirect::back();
    }


    public function delete_document($key)
    {

        $documents_settings = SiteSettings::where('criteria', 'documents_settings')->first();
        $response = $documents_settings->delete_document($key);
        header("content-type:application/json");

        echo json_encode(compact('response'));
    }


    public function confirm_payment($order_id)
    {

        $order = SubscriptionOrder::find($order_id);
        $status = $order->mark_paid();
        Redirect::back();
    }


    public function testimony()
    {

        $this->view('admin/testimony');
    }

    public function documents()
    {

        $all_documents = Document::all();
        // $documents_categories = Document::groupBy('category')->get()->pluck('category')->toArray();
        $documents_categories = Document::$categories;

        $show = true;
        $this->view('admin/documents', compact('show', 'all_documents', 'documents_categories'));
    }

    public function edit_testimony($testimony_id = null)
    {
        if (($testimony_id != null)) {
            $testimony = Testimonials::find($testimony_id);
            if (($testimony != null)) {

                $this->view('admin/edit_testimony', ['testimony' => $testimony]);
                return;
            } else {
                Redirect::to();
            }
        }
    }


    public function suspending_admin($admin_id = null)
    {

        $admin = Admin::find($admin_id);
        if ($admin == null) {
            Redirect::back();
        }


        if ($admin->is_owner()) {
            Session::putFlash('danger', "Invalid Request");
            Redirect::back();
        } else {

            $admin->delete();
            Session::putFlash('success', "Deleted Succesfully");
        }
        Redirect::back();
    }


    public function create_admin()
    {

        if (Input::exists()) {
        }

        $this->validator()->check(Input::all(), array(

            'firstname' => [

                'required' => true,
                'min' => 2,
                'max' => 20,
            ],
            'lastname' => [

                'required' => true,
                'min' => 2,
                'max' => 20,
            ],

            'email' => [

                'required' => true,
                'email' => true,
                'unique' => 'Admin'
            ],

            'username' => [

                'required' => true,

                'min' => 3,
                // 'one_word'=> true,
                'no_special_character' => true,
                'unique' => 'Admin',
            ],

            'phone' => [

                'required' => true,
                'min' => 9,
                'max' => 14,
                'unique' => 'Admin'

            ],

        ));

        if ($this->validator->passed()) {
            $admin = Admin::create([
                'firstname' => Input::get('firstname'),
                'lastname' => Input::get('lastname'),
                'email' => Input::get('email'),
                'phone' => Input::get('phone'),
                'username' => Input::get('username'),

            ]);
            if ($admin) {


                Session::putFlash('success', "Admin Created Succesfully.");
            }
        } else {


            Session::putFlash('info', Input::inputErrors());
        }
    }



    public function administrators()
    {

        $this->view('admin/administrators');
    }


    public function accounts()
    {
        $this->view('admin/accounts');
    }


    public function profile($admin_id = null)
    {

        $admin = Admin::where('id', $admin_id)->first();
        if (($admin == null) || (($admin->is_owner()) && (!$this->admin()->is_owner()))) {

            // Session::putFlash('danger','unauthorised access');
            Redirect::back();
        }

        $this->view('admin/profile', compact('admin'));
    }


    public function toggle_news($new_id)
    {

        $news = BroadCast::find($new_id);
        if ($news->status) {

            $update = $news->update(['status' => 0]);
            Session::putFlash('success', 'News unpublished succesfully');
        } else {

            $update = $news->update(['status' => 1]);

            Session::putFlash('success', 'News published succesfully');
        }

        Redirect::back();
    }


    public function delete_news($new_id)
    {

        $news = BroadCast::find($new_id);
        if ($news != null) {

            $update = $news->delete();
            Session::putFlash('success', 'Deleted succesfully');
        }


        Redirect::back("admin/news");
    }


    public function create_news()
    {

        print_r(Input::all());
        BroadCast::create([
            'broadcast_message' => Input::get('news'),
            'admin_id' => $this->admin()->id
        ]);
        Session::putFlash('success', 'News Created succesfully');

        Redirect::back();
    }


    public function broadcast()
    {
        $this->view('admin/broadcast');
    }


    public function viewSupportTicket($ticket_id)
    {

        $support_ticket_messages = SupportTicket::find($ticket_id)->messages;
        $support_ticket = SupportTicket::find($ticket_id);

        $this->view('admin/support-ticket-messages', [
            'support_ticket_messages' => $support_ticket_messages,
            'support_ticket' => $support_ticket
        ]);
    }


    public function create_testimonial()
    {

        if (Input::exists() || true) {

            $testimony = Testimonials::create([
                'attester' => Input::get('attester'),
                'content' => Input::get('testimony')
            ]);
        }
        Redirect::to("admin/edit_testimony/{$testimony->id}");
    }


    public function testimonials()
    {
        $this->view('admin/testimonials');
    }

    public function approve_testimonial($testimonial_id)
    {

        $testimony = Testimonials::find($testimonial_id);
        if ($testimony->approval_status) {

            $update = $testimony->update(['approval_status' => 0]);
            Session::putFlash('success', 'Testimonial disapproved succesfully');
        } else {

            $update = $testimony->update(['approval_status' => 1]);

            Session::putFlash('success', 'Testimonial approved succesfully');
        }


        Redirect::back();
    }

    public function delete_testimonial($testimonial_id)
    {

        $testimony = Testimonials::find($testimonial_id);
        if ($testimony != null) {

            $testimony->delete();
            Session::putFlash('success', 'Testimonial deleted succesfully');
        }


        Redirect::back();
    }


    public function update_testimonial()
    {

        echo "<pre>";
        $testimony_id = Input::get('testimony_id');
        $testimony = Testimonials::find($testimony_id);

        $testimony->update([
            'attester' => Input::get('attester'),
            'content' => Input::get('testimony'),
            'approval_status' => 0
        ]);


        Session::putFlash('success', 'Testimonial updated successfully. Awaiting approval');

        Redirect::back();
    }



    public function companies()
    {
        $this->view('admin/companies');
    }


    public function testing()
    {
        $this->view('admin/sales');
    }


    public function settings()
    {
        $this->view('admin/settings');
    }


    public function user_profile($user_id = null)
    {

        if ($user_id == null) {
            Redirect::back();
        }


        $_SESSION[$this->auth_user()] = $user_id;

        $domain = Config::domain();
        $e = <<<EOL


				<style type="text/css">
					body {
	  				 margin: 0;
	   				overflow: hidden;
					}
					#iframe1 {
	   				 position:absolute;
	    				left: 0px;
	    				width: 100%;
	    				top: 0px;
	    				height: 100%;
					}
				</style>


	 		<iframe  id="iframe1" src="$domain/user/dashboard"></iframe>
EOL;

        echo "$e";
        // $this->view('admin/accessing_user_profile');
    }


    public function suspending_user($user_id)
    {


        if (User::find($user_id)->blocked_on) {

            $update = User::find($user_id)->update(['blocked_on' => null]);
            Session::putFlash('success', 'Ban lifted succesfully');
        } else {

            $update = User::find($user_id)->update(['blocked_on' => date("Y-m-d")]);

            Session::putFlash('success', 'User Blocked succesfully');
        }


        if ($update) {
        } else {
            Session::putFlash('flash', 'Could not Block this User');
        }


        Redirect::back();
    }


    public function index()
    {
        $this->users();
    }


    public function dashboard()
    {
        $this->view('admin/dashboard');
    }


    public function library()
    {
        $this->view('admin/library');
    }


    public function membership_orders()
    {


        $sieve = $_REQUEST;
        // $sieve = array_merge($sieve, $extra_sieve);

        $query = SubscriptionOrder::latest();
        // ->where('status', 1);  //in review
        $sieve = array_merge($sieve);
        $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $per_page = 50;
        $skip = (($page - 1) * $per_page);

        $filter = new  SubscriptionOrderFilter($sieve);

        $data = $query->Filter($filter)->count();

        $subscription_orders = $query->Filter($filter)
            ->offset($skip)
            ->take($per_page)
            ->get();  //filtered

        $this->view('admin/subscription_orders', compact('subscription_orders', 'sieve', 'data', 'per_page'));
    }




    public function journals()
    {
        $sieve = $_REQUEST;
        extract(Journals::InvokeQuery($sieve));
        $this->view('admin/journals', get_defined_vars());
    }


    public function withdrawals_requests()
    {
        $sieve = array_merge($_REQUEST, [
            "tag" => "withdrawal",
            "notes" => "withdrawal",
        ]);

        extract(Journals::InvokeQuery($sieve));
        $this->view('admin/withdrawals_requests', get_defined_vars());
    }





    public function edit_journal($journal_id = '')
    {
        $journal = Journals::where('id', $journal_id)->where('company_id', 1)->first();


        if (!$journal->is_editable()) {
            Session::putFlash('info', "This Journal cannot be edited");
            Redirect::back();
        }

        $this->view('admin/edit_journal', get_defined_vars());
    }

    public function complete_journal($journal_id = '')
    {
        $journal = Journals::where('id', $journal_id)->where('company_id', 1)->first();


        if (!$journal->is_pending()) {
            Session::putFlash('info', "This Journal cannot be completed");
            Redirect::back();
        }
        $journal->completePending();

        Redirect::back();
    }

    public function decline_journal($journal_id = '')
    {
        $journal = Journals::where('id', $journal_id)->where('company_id', 1)->first();


        if (!$journal->is_pending()) {
            Session::putFlash('info', "This Journal cannot be completed");
            Redirect::back();
        }
        $journal->declinePending();

        Redirect::back();
    }


    public function reverse_journal($journal_id = '')
    {
        $journal = Journals::where('id', $journal_id)->where('company_id', 1)->first();


        if (!$journal->is_reversible()) {
            Session::putFlash('info', "This Journal cannot be completed");
            Redirect::back();
        }
        $journal->reverseJournal();

        Redirect::back();
    }



    public function view_journal($journal_id = '')
    {
        $journal = Journals::where('id', $journal_id)->where('company_id', 1)->first();


        if ($journal == null) {
            Session::putFlash('info', "Invalid request");
            Redirect::back();
        }

        $this->view('admin/view_journal', compact('journal'));
    }

    public function transactions($chart_of_account_id = null)
    {

        $sieve = $_REQUEST;
        $chart_of_account = ChartOfAccount::where('id', $chart_of_account_id)
            ->where('company_id', 1)
            ->first();

        $per_page = 50;
        $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $journal_sieve  = $sieve['journal'] ?? [];
        $line_items_sieve  = $sieve['line_items'] ?? [];

        $transactions = $chart_of_account->transactions($per_page, $page, $journal_sieve, $line_items_sieve);

        $this->view('admin/transactions', get_defined_vars());
    }

    public function e_wallets()
    {
        $sieve = $_REQUEST;
        extract(ChartOfAccount::InvokeQuery($sieve));
        $this->view('admin/bank_accounts', get_defined_vars());
    }
}
