<?php

namespace v2\Traits;

use User;

/**
 * 
 */
trait OrderableJournal
{
    public $name_in_shop = "deposit";


    public function getPaymentDescriptionAttribute()
    {
        return "Deposit";
    }


    public function scopeUnpaid($query)
    {
        return $query->Drafts();
    }


    public function getBuyerAttribute()
    {
        return User::find($this->details['payment_details']['user_id']);
    }

    public function getpaymentmethodAttribute()
    {
        return $this->details['payment_details']['payment_method'];
    }

    public function getpaymentdetailsAttribute()
    {
        return $this->details['payment_details'];
    }


    public function getPaymentDetails()
    {
        return $this->details['payment_details'];
    }


    public function create_order(array $cart)
    {
    }


    public function generateOrderID()
    {

        $substr = substr(strval(time()), 7);
        $order_id = "L{$this->id}D{$substr}";

        return $order_id;
    }

    public function setPayment($payment_method, array $payment_details)
    {

        $e = $this->details['payment_details'];
        $payment_details = array_merge($e, $payment_details);

        $this->updateDetailsByKey('payment_details', $payment_details);

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
        return $this->is_published();
    }


    public function total_price()
    {
        return $this->amount;
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
            $this->publish();
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }
}
