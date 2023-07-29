<?php

namespace v2\Shop\Payments\CoinbaseCommerce;

use MIS;
use Config;
use Session;
use Exception;
use SiteSettings;
use CoinbaseCommerce\Webhook;
use CoinbaseCommerce\ApiClient;
use CoinbaseCommerce\Resources\Charge;
use CoinbaseCommerce\Resources\Checkout;
use Illuminate\Support\Facades\Redirect;
use v2\Shop\Contracts\PaymentMethodInterface;

/**
 * 
 */
class CoinbaseCommerce implements PaymentMethodInterface
{
    public $name = 'coinbase_commerce';
    private $payment_type;
    private $mode;

    function __construct()
    {

        $settings = SiteSettings::find_criteria('coinbase_commerce_keys')->settingsArray;

        $this->mode = $settings['mode']['mode'];

        $this->api_keys =  $settings[$this->mode];

        $apiClientObj = ApiClient::init($this->api_keys['secret_key']);
        $apiClientObj->setTimeout(6);
    }



    public function setShop($shop)
    {
        $this->shop = $shop;
        return $this;
    }


    public function setPaymentType($payment_type)
    {
        $this->payment_type = $payment_type;
        return $this;
    }


    public function goToGateway()
    {

        $payment_details = json_decode($this->order->payment_details, true);

        $callback_param = http_build_query([
            'item_purchased' => $this->order->name_in_shop,
            'order_unique_id' => $this->order->id,
            'payment_method' => $this->order->payment_method,
        ]);


        Redirect::to("shop/make_livepay_payment/?$callback_param");
    }


    public function paymentStatus()
    {
    }


    public function reVerifyPayment()
    {
        $response =         $this->paymentStatus();
        if ($response['STATUS'] == "TXN_SUCCESS") {

            if (($this->amountPayable() == $response['TXNAMOUNT'])) {

                $confirmation = ['status' => true];
                $result = $_POST;

                return compact('result', 'confirmation');
            }
        }

        \Session::putFlash('danger', "Payment not seen");
    }





    public function verifyPayment()
    {

        echo "<pre>";
        print_r($_REQUEST);
        /**
         * To run this example please read README.md file
         * Past your Webhook Secret Key from Settings/Webhook section
         * Make sure you don't store your Secret Key in your source code!
         */
        $secret = $this->api_keys['webhook_secret'];
        $headerName = 'X-Cc-Webhook-Signature';
        $headers = getallheaders();
        $signraturHeader = isset($headers[$headerName]) ? $headers[$headerName] : null;
        $payload = trim(file_get_contents('php://input'));

        try {
            $event = Webhook::buildEvent($payload, $signraturHeader, $secret);
    
            ///


            //check that it is charge:confirmed

            if($event->type != 'charge:confirmed'){
                return false;
            }

            
                
            $payment_details = $this->order->PaymentDetailsArray;
            $result = $event;
            $confirmation = ['status' => true];
            return compact('result', 'confirmation');

            $myfile = fopen("coinbase.txt", "w") or die("Unable to open file!");
            fwrite($myfile, ($event->addresses));
            fwrite($myfile, ($event->id));
            fwrite($myfile, ($event->type));

            http_response_code(200);
            echo sprintf('Successully verified event with id %s and type %s.', $event->id, $event->type);
        } catch (\Exception $exception) {
            http_response_code(400);
            echo 'Error occured. ' . $exception->getMessage();
        }



    }


    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }



    public function amountPayable()
    {
        $amount = $this->order->total_price();

        return $amount;
    }

    private function makeOneTimePayment()
    {
        $payment_method = $this->name;
        $order_ref = $this->order->generateOrderID();
        $amount = $this->amountPayable();


        $callback_param = http_build_query([
            'item_purchased' => $this->order->name_in_shop,
            'order_unique_id' => $this->order->id,
        ]);


        $domain = Config::domain();
        if ($_ENV['APP_ENV'] == 'local') {
            $callback_url = "https://domain.com/shop/callback?$callback_param";
        } else {
            $callback_url = "{$domain}/shop/callback?$callback_param";
        }


        $user = $this->order->Buyer;
        $code = \Config::currency('code');

        $payment_details = [
            'gateway' => $this->name,
            'ref' => $order_ref,
            'order_unique_id' => $this->order->id,
            'email' => $user->email,
            'phone' => $user->phone ?? '09134567891',
            'currency' => $code,
            'fullname' => $user->fullname,
            "invoice_id" => $this->order->InvoiceID
        ];

        $project_name = \Config::project_name();

        $checkoutData = [
            'name' => $order_ref,
            'description' => "{$this->order->total_item()} Tip(s) from $project_name",
            'pricing_type' => 'fixed_price',
            'local_price' => [
                'amount' =>  $this->amountPayable(),
                'currency' => strtoupper($code),
            ],
            "metadata" => $payment_details,
            "requested_info" => [],
        ];


        // $chargeObj = Checkout::create($checkoutData);
        $chargeObj = Charge::create($checkoutData);


        $payment_details['checkout_url'] = "$chargeObj->hosted_url";
        $payment_details['custom'] = $checkoutData;

        $this->order->setPayment($payment_method, $payment_details);

        return $this;
    }

    public function makeSubscriptionPayment()
    {
        Session::putFlash("danger", "$this->name is unable to process subscription(Automatic) based payment.");

        $this->order->setPayment($payment_method, $payment_details);
        return $this;
    }

    public function initializePayment()
    {
        $actions = [
            'one_time' => 'makeOneTimePayment',
            'subscription' => 'makeSubscriptionPayment',
        ];

        $method = $actions[$this->payment_type];
        return $this->$method();
    }

    public function attemptPayment()
    {


        if ($this->order->is_paid()) {
            throw new Exception("This Order has been paid with {$this->order->payment_method}", 1);
        }


        if ($this->order->payment_method != $this->name) {
            throw new Exception("This Order is not set to use {$this->name} payment method", 1);
        }

        $payment_details = json_decode($this->order->payment_details, true);

        return $payment_details;
    }
}
