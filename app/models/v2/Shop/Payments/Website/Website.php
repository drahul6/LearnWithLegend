<?php

namespace v2\Shop\Payments\Website;

use User;
use v2\Models\Commission;
use v2\Shop\Contracts\OrderInterface;
use v2\Models\Wallet\Classes\AccountManager;
use v2\Shop\Contracts\PaymentMethodInterface;
use Illuminate\Database\Capsule\Manager as DB;
use Exception, SiteSettings, Config, MIS, Session, Redirect;
use v2\Models\Wallet\ChartOfAccount;

/**
 * 
 */
class Website implements PaymentMethodInterface
{
    public $name = 'wallet';
    public $payment_type = 'one_time';
    public $order;



    function __construct()
    {
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
        Redirect::back();
    }



    public function paymentStatus()
    {

        return true;
    }



    public function reVerifyPayment()
    {
        $response =         $this->paymentStatus();
        if ($response['STATUS'] == "TXN_SUCCESS") {

            if (($this->amountPayable() == $response['TXNAMOUNT'])) {

                if (!$this->order->is_paid()) {
                    $this->order->mark_paid();
                } else {

                    \Session::putFlash('success', "Payment successful");
                }

                return $response;
            }
        }

        \Session::putFlash('danger', "Payment not successful");
    }


    public function verifyPayment()
    {

        $confirmation = ['status' => true];
        $result = [];
        return compact('result', 'confirmation');
    }


    public function setOrder(OrderInterface $order)
    {
        $this->order = $order;
        return $this;
    }


    public function amountPayable()
    {
        $amount =  $this->order->total_price();
        return $amount;
    }


    public function initializePayment()
    {

        $payment_method = $this->name;
        $order_ref = $this->order->generateOrderID();
        $amount = $this->amountPayable();



        $user = $this->order->user;
        $domain = Config::domain();


        $callback_param = http_build_query([
            'item_purchased' => $this->order->name_in_shop,
            'order_unique_id' => $this->order->id,
        ]);


        $payment_details = [
            'gateway' => $this->name,
            'ref' => $order_ref,
            'order_unique_id' => $this->order->id,
        ];

        $this->order->setPayment($payment_method, $payment_details);

        return $this;
    }



    private function makeEscrowPayment()
    {

        $items = $this->order->order_detail();

        $user  = $this->order->user;
        $cost = $this->amountPayable();
        $currency = Config::currency();
        DB::beginTransaction();

        try {


            //debit user and put money in internal escrow
            $ecom_settings = SiteSettings::ecommerceSettings();
            $min_pools_commission =  $ecom_settings['min_pools_commission'];


            $involved_accounts = [];
            foreach ($items as $key => $item) {


                $cost = $item['market_details']['price'] * $item['qty'];
                $pools_commission = max($item['data']['pools_commission'] ?? 0, $min_pools_commission);

                $amount_from_buyer = $cost;
                $amount_to_pools = $pools_commission * 0.01 * $cost;
                $amount_to_seller = $cost - $amount_to_pools;

                $buyer_account =  $user->getAccount('default');
                $seller = User::find($item['user_id']);
                $seller_account =  $seller->getAccount('default');


                //debit buyer
                $involved_accounts[] = [
                    "journal_id" => "",
                    "chart_of_account_id" => $buyer_account->id,
                    "chart_of_account_number" => $buyer_account->account_number,
                    "description" => "payment for order #{$this->order->id}",
                    "credit" => 0,
                    "debit" => round($cost, 2),
                ];


                $escrow_account = ChartOfAccount::find(AccountManager::journal_second_legs('ecommerce_escrow'));

                //credit escrow account
                $involved_accounts[] = [
                    "journal_id" => "",
                    "chart_of_account_id" => $escrow_account->id,
                    "chart_of_account_number" => $escrow_account->account_number,
                    "description" => "comm for {$item['market_details']['name']}#{$item['id']} on order #{$this->order->id}",
                    "credit" => round($cost, 2),
                    "debit" => 0,
                    "details" => [
                        "product_id" => $item['id'],
                    ],

                ];

                break; //only one item at a time
            }

            $today = date("Y-m-d");

            $journal = [
                "company_id" => 1,
                "notes" => "ecommerce order #{$this->order->id}",
                "currency" => "USD",
                "c_amount" => $cost,
                "amount" => $cost,
                "status" => 3,
                "journal_date" => $today,
                "tag" => "ecommerce_order",
                "identifier" => "ecommerce_order#{$this->order->BookId}",
                "user_id" => $user->id,
                "details" => [
                    "order_id" => $this->order->id,
                ],
                "involved_accounts" => $involved_accounts,
            ];


            $total_cost = collect($involved_accounts)->sum('debit');

            if (!$buyer_account->hasSufficientBalanceFor($total_cost, $currency)) {
                Session::putFlash("danger", "insufficient Funds");
                // throw new Exception("Insufficient Balance", 1);
                return false;
            }

            $journal_model = AccountManager::postJournal($journal);


            //tie this payment to this order
            $payment_details = ($this->order->paymentDetailArray);
            $payment_details['journal_id'] = $journal_model->id;

            $this->order->setPayment("$this->name", $payment_details);
            $this->order->mark_paid();




            DB::commit();
            \v2\Shop\Shop::empty_cart_in_session();
        } catch (Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
        }

        $payment_details = json_decode($this->order->payment_details, true);

        return $this;
    }

    private function makeDirectPayment()
    {

        $items = $this->order->order_detail();

        $user  = $this->order->user;
        $cost = $this->amountPayable();
        $currency = Config::currency();
        DB::beginTransaction();

        try {

            //debit user and put money in internal escrow
            $ecom_settings = SiteSettings::ecommerceSettings();
            $min_pools_commission =  $ecom_settings['min_pools_commission'];

            $involved_accounts = [];
            foreach ($items as $key => $item) {


                $cost = $item['market_details']['price'] * $item['qty'];
                $pools_commission = max($item['data']['pools_commission'] ?? 0, $min_pools_commission);

                $amount_from_buyer = $cost;
                $amount_to_pools = $pools_commission * 0.01 * $cost;
                $amount_to_seller = $cost - $amount_to_pools;

                $buyer_account =  $user->getAccount('default');
                $seller = User::find($item['user_id']);

                $seller_account =  $seller->getAccount('default');


                //debit buyer
                $involved_accounts[] = [
                    "journal_id" => "",
                    "chart_of_account_id" => $buyer_account->id,
                    "chart_of_account_number" => $buyer_account->account_number,
                    "description" => "purchase payment for order #{$this->order->id}",
                    "credit" => 0,
                    "debit" => round($cost, 2),
                ];


                //credit seller account
                $involved_accounts[] = [
                    "journal_id" => "",
                    "chart_of_account_id" => $seller_account->id,
                    "chart_of_account_number" => $seller_account->account_number,
                    "description" => "sale {$item['market_details']['name']}#{$item['id']} on order #{$this->order->id}",
                    "credit" => round($amount_to_seller, 2),
                    "debit" => 0,
                    "details" => [
                        "product_id" => $item['id'],
                    ],

                ];

                $pools_account = ChartOfAccount::find(AccountManager::journal_second_legs('monthly_pools_prize_account'));


                //credit pools_commission account
                $involved_accounts[] = [
                    "journal_id" => "",
                    "chart_of_account_id" => $pools_account->id,
                    "chart_of_account_number" => $pools_account->account_number,
                    "description" => "pools comm for {$item['market_details']['name']}#{$item['id']} on order #{$this->order->id}",
                    "credit" => round($amount_to_pools, 2),
                    "debit" => 0,
                    "details" => [
                        "product_id" => $item['id'],
                    ],

                ];

                break; //only one item at a time
            }

            $today = date("Y-m-d");

            $journal = [
                "company_id" => 1,
                "notes" => "ecommerce order #{$this->order->id}",
                "currency" => "USD",
                "c_amount" => $cost,
                "amount" => $cost,
                "status" => 3,
                "journal_date" => $today,
                "tag" => "ecommerce_order",
                "identifier" => "ecommerce_order#{$this->order->BookId}",
                "user_id" => $user->id,
                "details" => [
                    "order_id" => $this->order->id,
                ],
                "involved_accounts" => $involved_accounts,
            ];


            $total_cost = collect($involved_accounts)->sum('debit');

            if (!$buyer_account->hasSufficientBalanceFor($total_cost, $currency)) {
                Session::putFlash("danger", "insufficient Funds");
                // throw new Exception("Insufficient Balance", 1);
                return false;
            }

            $journal_model = AccountManager::postJournal($journal);


            //tie this payment to this order
            $payment_details = ($this->order->paymentDetailArray);
            $payment_details['journal_id'] = $journal_model->id;

            $this->order->setPayment("$this->name", $payment_details);
            $this->order->mark_paid();
            $this->order->mark_as_settled();


            DB::commit();
            \v2\Shop\Shop::empty_cart_in_session();
        } catch (Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
        }

        $payment_details = json_decode($this->order->payment_details, true);

        return $this;
    }
    public function attemptPayment()
    {

        if ($this->order->is_paid() ||  ($this->order->payment_method != $this->name)) {
            Session::putFlash("danger", "This Order has been paid with {$this->order->payment_method}<br> or 
            is not set to use {$this->name} payment menthod");
            return $this;
        }


        //check whether to use escrow or direct payment
        $items = $this->order->order_detail();


        if ($items[0]['type_of_product'] == 'digital') {

            return $this->makeDirectPayment();
        }

        return $this->makeEscrowPayment();
    }
}
