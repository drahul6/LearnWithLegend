<?php

namespace app\models;

use User;

use Redirect;
use controller;
use SubscriptionPlan;
use SubscriptionOrder;
use RegisterController;
use v2\Shop\Contracts\OrderInterface;
use v2\Models\Wallet\Classes\AccountManager;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;

class ProspectiveUser extends Eloquent implements OrderInterface
{

    protected $fillable = [
        'data',
        'paid_at',
    ];

    protected $table = 'prospective_users';
    public $name_in_shop = 'prospective_user';




    public function getBuyerAttribute()
    {
        return new User($this->data);
    }

    public function getpaymentmethodAttribute()
    {
        return $this->data['payment_details']['gateway'];
    }

    public function getpaymentdetailsAttribute()
    {
        return $this->data['payment_details'];
    }


    public function getPaymentDetails()
    {
        return $this->data['payment_details'];
    }


    public function create_order(array $cart)
    {
    }


    public function generateOrderID()
    {

        $substr = substr(strval(time()), 7);
        $order_id = "PRL{$this->id}D{$substr}";

        return $order_id;
    }

    public function setPayment($payment_method, array $payment_details)
    {

        $e = $this->data['payment_details'] ?? [];
        $payment_details = array_merge($e, $payment_details);

        $this->updateData(['payment_details' => $payment_details]);

        return $this;
    }



    public function setPaymentBreakdown(array $payment_breakdown, $order_id = null)
    {

        return;
        $this->update([
            'order_id' => $order_id,
            'payment_breakdown' => json_encode($payment_breakdown),
            'amount_payable' => $payment_breakdown['total_payable']['value'],
        ]);

        return $this;
    }
    public function calculate_vat()
    {

        $vat_percent =  0;

        $subtotal = $this->total_price();
        $vat = $vat_percent * 0.01 * $subtotal;


        $result = [
            'value' => $vat,
            'percent' => $vat_percent,
        ];

        $result = [
            'value' => 0,
            'percent' => 0,
        ];


        return $result;
    }


    public function is_paid()
    {
        return $this->paid_at != null;
    }


    public function total_price()
    {
        return SubscriptionPlan::find($this->data['membership']['id'])->price;
    }


    public function total_qty()
    {
    }


    public function getPriceBreakdownAttribute()
    {
        $percent_vat = 0;
        $tax = 0.01 * $percent_vat * $this->amount;
        $breakdown = [
            'before_tax' => $this->amount,
            'set_price' => $this->amount,
            'total_percent_tax' => $percent_vat,
            'tax' =>  $tax,
            'type' =>  "exclusive",
            'total_payable' =>  $this->amount,
        ];

        return $breakdown;
    }


    public function total_tax_inclusive()
    {

        $breakdown = $this->PriceBreakdown;

        $tax = [
            'price_inclusive_of_tax' => $breakdown['total_payable'],
            'price_exclusive_of_tax' => $breakdown['set_price'],
            'total_sum_tax' => $breakdown['tax'],
        ];

        return $tax;
    }


    public function mark_paid()
    {

        if ($this->is_paid()) {
            return;
        }
        try {

            $this->register();
            $this->update(['paid_at' => date("Y-m-d H:i:s")]);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }












































    public function register()
    {

        $user = User::createUser($this->data);


        DB::beginTransaction();
        try {

            $new_sub = SubscriptionPlan::find($this->data['membership']['id']);

            //deposit money and collect it for membership
            $default_account = $user->getAccount('default');

            $deposit = AccountManager::deposit([
                'receiving_account' => $default_account->id,
                'amount' => $new_sub->price,
                'status' => 1,
                'collect_deposit_fee' => false,
                'narration' => "deposit",
                'journal_date' => null,
            ]);

            $deposit->mark_paid();
            SubscriptionPlan::create_subscription_request($this->data['membership']['id'], $user->id);

            //link prospective line and unset password
            $this->updateData([
                'user_id' => $user->id,
                "password" => null
            ]);

            (new controller)->directly_authenticate($user->id);
            \Session::putFlash("success", "Account created successfully.");

            DB::commit();
        } catch (\Throwable $th) {
            print_r($th->getMessage());
            DB::rollback();
        }

        Redirect::to("login");
    }


    public function scopeUnPaid($query)
    {
        return $query->where('paid_at', '=', null);
    }

    public function scopePaid($query)
    {
        return $query->where('paid_at', '!=', null);
    }


    public function updateData(array $key_value_array)
    {
        $details = $this->data;
        $details = array_merge($details, $key_value_array);

        $this->update(["data" => $details]);
    }

    public function getDataAttribute($value)
    {
        if ($value == null) {
            return [];
        }
        return json_decode($value, true);
    }


    public function setDataAttribute($value)
    {
        $this->attributes['data'] = json_encode($value);
    }
}
