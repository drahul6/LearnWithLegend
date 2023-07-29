<?php

use Illuminate\Database\Capsule\Manager as DB;
use v2\Filters\Filters\ProductsFilters;
use v2\Shop\Shop;


/**
 * this class is the default controller of our application,
 *
 */
class shopController extends controller
{



    public function __construct()
    {
        $this->current_user = $this->middleware('current_user');
    }

    public function re_confirm_order()
    {
        $shop = new Shop();
        $item_purchased = $shop->available_type_of_orders[$_REQUEST['item_purchased']];
        $full_class_name = $item_purchased['namespace'] . '\\' . $item_purchased['class'];
        $order = $full_class_name::where('id', $_REQUEST['order_unique_id'])->where('paid_at', null)->first();

        $shop->setOrder($order)->reVerifyPayment();

        Redirect::back();
    }


    public function cancel_agreement()
    {
        $order = $this->auth()->subscription;
        $order->cancelAgreement();
        Redirect::back();
    }


    //for subscription payment
    public function execute_agreement()
    {
        $shop = new Shop();
        $item_purchased = $shop->available_type_of_orders[$_REQUEST['item_purchased']];
        $full_class_name = $item_purchased['namespace'] . '\\' . $item_purchased['class'];
        $order_id = $_REQUEST['order_unique_id'];
        $order = $full_class_name::where('id', $order_id)->where('paid_at', null)->first();

        DB::beginTransaction();
        try {

            $shop->setOrder($order)->executeAgreement();

            DB::commit();
        } catch (Exception $e) {
        }

        switch ($_REQUEST['item_purchased']) {
            case 'deposit':
                Redirect::to('user/make-deposit');
                break;
            default:
                # code...
                break;
        }

        Redirect::to('user/package');
    }


    public function callback()
    {

        $shop = new Shop();
        $item_purchased = $shop->available_type_of_orders[$_REQUEST['item_purchased']];


        $full_class_name = $item_purchased['class'];
        $order_id = $_REQUEST['order_unique_id'];
        $order = $full_class_name::where('id', $order_id)->first();

        $shop->setOrder($order)->verifyPayment();



        switch ($_REQUEST['item_purchased']) {
            case 'deposit':
                Redirect::to('user/wallet');
                break;
            default:
                # code...
                break;
        }

        // Redirect::to('user/package');
    }


    public function checkout()
    {

        $checkout_type = $_GET['checkout_type'] ?? 'standard';
        $auth = $this->auth();

        $shop = new Shop();

        $item_purchased = $shop->available_type_of_orders[$_REQUEST['item_purchased']];

        $full_class_name = $item_purchased['class'];

        $order_id = $_REQUEST['order_unique_id'];
        $order = $full_class_name::where('id', $order_id)->Unpaid()->first();

        $payment_method = $_REQUEST['payment_method'] ?? $_REQUEST['gateway'];

        if ($order == null) {
            Session::putFlash("info", "Invalid Request");
            return;
        }

        $shop = new Shop();
        $attempt =   $shop
            ->setOrder($order)
            ->setPaymentMethod($payment_method)
            ->initializePayment()
            ->attemptPayment();


        if ($attempt == false) {
            Redirect::back();
        }

        switch ($checkout_type) {
            case 'standard':
                $shop->goToGateway();
                break;

            case 'inline':
                header("content-type:application/json");
                echo json_encode($attempt);
                break;

            default:
                # code...
                break;
        }
    }


    public function make_livepay_payment()
    {

        $this->current_user->mustbe_loggedin();


        $shop = new Shop();

        $item_purchased = $shop->available_type_of_orders[$_REQUEST['item_purchased']];

        $full_class_name = $item_purchased['namespace'] . '\\' . $item_purchased['class'];
        $order_id = $_REQUEST['order_unique_id'];
        $order = $full_class_name::where('id', $order_id)->where('user_id', $this->auth()->id)->where('paid_at', null)->first();



        $payment_details = $order->PaymentDetailsArray;
        $livepay_order_id = $payment_details['approval']['order_id'];

        if ($livepay_order_id == "HuUbdGddVTYS") {

            $shop = new Shop();
            $attempt = $shop
                ->setOrder($order)
                ->setPaymentMethod($order->payment_method)
                ->setPaymentType('one_time')
                ->initializePayment()
                ->attemptPayment();
            $order = $full_class_name::where('id', $order_id)->first();
        }


        $this->view("auth/make_livepay_payment", compact('order'));
    }


    public function capture_payment($razorpay_payment_id, $order_id)
    {

        $order = Orders::find($order_id);

        if ($order->razorpay_response == '') {

            $order->update(['razorpay_response' => $_POST['razorpay_response']]);
        }
        $api = Orders::razorpay_api();

        // $razorpay_response = json_decode($_POST['razorpay_response'], true);

        $razorpay_response = json_decode($order->razorpay_response, true);


        $razorpay_signature = $razorpay_response['razorpay_signature'];


        print_r($razorpay_response);

        // $payment = $api->payment->fetch("$razorpay_payment_id");
        // $response =  $payment->capture(array('amount' => $order->razorpay_amount_payable()));


        $settings = SiteSettings::site_settings();
        $api_key = $settings['razorpay_public_key'];
        $api_secret = $settings['razorpay_secret_key'];


        $generated_signature =
            hash_hmac('sha256', $razorpay_response['razorpay_order_id'] . "|" . $razorpay_response['razorpay_payment_id'], $api_secret);


        if ($generated_signature == $razorpay_signature) {

            DB::beginTransaction();

            try {

                $order->mark_paid();
                DB::commit();
                Session::putFlash("success", "Payment received successfully");
            } catch (Exception $e) {

                DB::rollback();
            }
        }
    }


    public function capture_sub_payment($razorpay_payment_id, $order_id)
    {

        echo "<pre>";

        $order = SubscriptionOrder::find($order_id);

        if ($order->razorpay_response == '') {
            $order->update(['razorpay_response' => $_POST['razorpay_response']]);
        }

        $api = Orders::razorpay_api();

        // $razorpay_response = json_decode($_POST['razorpay_response'], true);

        $razorpay_response = json_decode($order->razorpay_response, true);


        $razorpay_signature = $razorpay_response['razorpay_signature'];


        print_r($razorpay_response);


        $settings = SiteSettings::site_settings();
        $api_key = $settings['razorpay_public_key'];
        $api_secret = $settings['razorpay_secret_key'];


        $generated_signature =
            hash_hmac('sha256', $razorpay_response['razorpay_order_id'] . "|" . $razorpay_response['razorpay_payment_id'], $api_secret);


        if ($generated_signature == $razorpay_signature) {

            DB::beginTransaction();

            try {

                $order->mark_as_paid();
                DB::commit();
                Session::putFlash("success", "Payment received successfully");
            } catch (Exception $e) {

                DB::rollback();
            }
        }
    }



    public function complete_order($action = 'breakdown')
    {

        $cart = json_decode($_POST['cart'], true);
        header("content-type:application/json");

        // echo "<pre>";


        DB::beginTransaction();

        try {

            $auth = $this->auth();
            $total = $cart['$total'];
            $amount_payable = $total;

            $product = Products::find($cart['$items'][0]['id']);



            //validate shipping details
            $validator = new Validator;

            $checks = [
                "digital" => [
                    /* "files_exists" => [
                        "files" => [$product->downloadable_files],
                        "name" => "Product",
                    ] */],
                "physical" =>   [
                    "firstname" => [
                        'required' => true,
                        'min' => "2",
                    ],
                    "lastname" => [
                        'required' => true,
                        'min' => "2",
                    ],
                    "email" => [
                        'required' => true,
                        'email' => true,
                    ],
                    "address" => [
                        'required' => true,
                    ],
                ]
            ];


            $type_of_product = ($cart['$items'][0]['type_of_product']);

            $validator->check(
                $cart['$shipping_details'],
                $checks[$type_of_product] ?? []
            );


            //
            if ($type_of_product == 'physical' && $cart['$shipping_details']['delivery'] == null) {
                $validator->addError("Delivery", "Please select a delivery method.");
            }


            //check stock availability
            $qty_ordered = $cart['$items'][0]['qty'];
            if ($product->stock != null  && $qty_ordered > $product->stock) {
                $validator->addError("Stock", "Only {$product->stock} stock is left. Pls reduce the qty to place order.");
            }



            $settings = SiteSettings::ecommerceSettings();

            //prevent accidental order
            $identifier = Orders::generateIdentifier($cart);
            $x_mins_ago = date("Y-m-d H:i:s",  strtotime("-{$settings['unique_order_interval_period']}"));

            $similar_orders = Orders::where('user_id', $auth->id)
                ->where('identifier', $identifier)
                ->whereRaw("updated_at >= '$x_mins_ago'")
                ->count();

            if ($similar_orders > 0) {
                $validator->addError("Duplicate order", "You already made a similar order.");
            }


            if (!$validator->passed()) {
                Session::putFlash("danger", Input::inputErrors());
                echo json_encode([]);
                return;
            }


            $sellers_ids = collect($cart['$items'])->pluck('user_id')->unique()->implode(",");

            $new_order = Orders::updateOrCreate(
                ['id' => $_SESSION['s'] ?? null],
                [
                    'user_id' => $auth->id,
                    'identifier' => $identifier,
                    'buyer_order' => json_encode($cart),
                    'amount_payable' => $amount_payable,
                    'sellers_ids' => $sellers_ids,
                ]
            );

            $shop = new Shop();
            $shop
                // ->setOrderType('order') //what is being bought
                ->setOrder($new_order)
                ->setPaymentMethod($_POST['payment_method'])
                ->setPaymentType();


            $_SESSION['shop_checkout_id'] = $new_order->id;

            switch ($action) {
                case 'get_breakdown':

                    $breakdown = $shop->fetchPaymentBreakdown();
                    echo json_encode(compact('breakdown'));
                    break;

                case 'make_payment':

                    $payment_details = $shop->initializePayment()->attemptPayment();
                    if ($payment_details == false) {
                        throw new Exception("Payment process error", 1);
                    }
                    echo json_encode($payment_details);
                    break;

                default:
                    # code...
                    break;
            }


            $product->reduceStock($qty_ordered);

            DB::commit();
            return;
        } catch (Exception $e) {
            DB::rollback();
            Session::putFlash('danger', "We could not create your order.");
            // Redirect::back();
        }
        // header("content-type:application/json");
        echo json_encode([]);
    }


    public function submit_for_review($id = null)
    {

        $auth = $this->auth();
        $product = Products::where('id', $id)->where('user_id', $auth->id)->first();

        if ($product == null) {
            Redirect::back();
        }

        //do some checks
        $validator = new Validator;
        $rules = Products::getValidationRule($auth->id, $product);
        $validator->check($product->toArray(), $rules);


        if (!file_exists($product->downloadable_files)  && Input::get('type_of_product') == 'digital') {
            $validator->addError("Digital file", "Downloadable file must be provided.");
        }

        if (!$validator->passed()) {

            Session::putFlash("danger", Input::inputErrors());
            Redirect::back();
        }

        /* 
        if ($product->isAt('in review')) {
            Session::putFlash("danger", "This product is under review");
            Redirect::back();
        } */


        $product->markAs('in review');
        Redirect::back();
    }


    /**
     * this is the default landing point for all request to our application base domain
     * @return a view from the current active template use: Config::views_template()
     * to find out current template
     */
    public function index($category = null)
    {
        echo "string";

        // $this->view('guest/shop', ['default_category'=>$category]);
    }


    public function order_detail($order_id)
    {
        $order = Orders::find($order_id);
        if ($order == null) {

            Redirect::back();
        }
        $this->view('guest/order_detail', ['order' => $order]);
    }


    public function cart()
    {
        $shipping_rate = '1500.00';
        $this->view('guest/cart', ['shipping_rate' => $shipping_rate]);
    }


    public function delete_stored_order($order_id)
    {
        Orders::delete_order([$order_id]);
        echo "deletededeed";
    }


    public function product_detail($product_id = null)
    {
        $product = Products::find($product_id);

        if ($product == null) {
            Session::putFlash("danger", "Item not found");
            Redirect::back();
        }


        $this->view('guest/product_detail', get_defined_vars());
    }


    public function retrieve_cart_in_session()
    {

        // echo "<pre>";
        header("content-type:application/json");

        $cart = json_decode($_SESSION['cart'], true);

        foreach ($cart['$items'] as $key => $item) {

            // $item_array =  json_decode($item, true);
            unset($cart['$items'][$key]['$$hashKey']);
            $items[] = $item;
        }

        if (!isset($_SESSION['cart'])) {
            $cart = [];
        }

        print_r(json_encode($cart));
    }


    public function update_cart()
    {

        $_SESSION['cart'] = ($_POST['cart']);
    }


    public function all_categories($page = 1)
    {
        header("Content-type: application/json");
        // $per_page = 100;
        echo ProductsCategory::all();
        // ->forPage($page , $per_page);
    }

    public function empty_cart_in_session()
    {
        unset($_SESSION['cart']);
        unset($_SESSION['shop_checkout_id']);
    }


    public function send_order_notification_email($order_id)
    {
        $order = Orders::find($order_id);

        $notification_email = CmsPages::where('page_unique_name', 'notification')->first()->page_content;
        $notification_email = json_decode($notification_email, true);


        $subject = Config::project_name() . ' NEW ORDER NOTIFICATION';
        $email_body = $this->buildView('emails/order_notification', ['order' => $order]);

        $mailer = new Mailer();
        $mailer->sendMail($notification_email['notification_email'], $subject, $email_body);
        ob_end_clean();
    }


    public function send_order_confirmation_email($order_id)
    {
        $order = Orders::find($order_id);
        $to = $order->billing_email;
        $subject = Config::project_name() . ' ORDER CONFIRMATION';
        $email_body = $this->buildView('emails/order_confirmation', ['order' => $order]);

        $mailer = new Mailer();
        $mailer->sendMail($to, $subject, $email_body);
        ob_end_clean();
    }




    public function get_single_item_on_market($item_id)
    {

        $single_good = Products::find($item_id);

        $single_good['market_details'] = $single_good->market_details();

        header("content-type:application/json");

        echo json_encode(compact('single_good'));
    }


    public function full_view($item_id)
    {

        $product = Products::find($item_id);
        if ($product == null) {
            Session::putFlash("danger", "Item not found");
            Redirect::back();
        }

        $this->view('auth/product_view', compact('product'));
    }




    public function fetch_products()
    {

        $input = Input::all();
        $sieve = $input ?? [];
        $query = Products::latest()->OnSale();

        $total = $query->count();

        $sieve = array_merge($sieve);
        $page = (isset($input['page'])) ? $input['page'] : 1;
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



        foreach ($products as $key => $product) {
            $product['market_details'] = $product->market_details();
        }




        $domain = Config::domain();
        $shop_link = "$domain/user/marketplace";
        $model = 'product';
        $register = [
            'product' => [
                'per_page' => $per_page,
                'model' => Products::class,
                'currency' => '$',
                'shop_link' => $shop_link,
            ],

        ];


        $config = $register[$model];

        $items = $products;
        $shop = compact('items', 'config');


        header("Content-type: application/json");
        echo json_encode($shop);
    }


    public function retrieve_shipping_settings()
    {
        header("Content-type: application/json");
        // echo CmsPages::where('page_unique_name', 'shipping_details')->first()->page_content;

    }


    public function find($course_id)
    {

        header("Content-type: application/json");
        // $per_page = 100;
        $course = Products::find($course_id);

        $course->by = $course->instructor->lastname . ' ' . $course->instructor->firstname;
        $course->category = $course->category;
        $course->short_title = substr($course->title, 0, 34);
        $course->last_updated = $course->updated_at->diffForHumans();
        $course->thumbnail = $course->image;
        $course->url_link = $course->url_link();
        $course->images = $course->images;
        $course->mainimage = $course->mainimage;
        $course->quickdescription = $course->quickdescription();

        echo $course;
    }
}
