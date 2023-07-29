<?php

namespace v2\Classes;



class ExchangeRate
{
    /**
     * currency to covert from
     *
     * @var string
     */
    public $from;


    /**
     * currency to convert to
     *
     * @var string
     */
    public $to;

    public $amount = 1;

    /**
     * USD/NGN --381
     *
     * @var array
     */
    public $usd_rates = [
        'USD' => 1,
        'NGN' => 499,
        'EUR' => 0.83,
        'GBP' => 0.72,
        'GHS' => 5.98,
        'KES' => 108,
        'ZAR' => 14.59,
        'TZS' => 2318,
        'UGX' => 3549,
        'XOF' => 552,
    ];


    /**
     *  returns the rate of exchange of a currency pair
     *
     * @param string $symbol
     * @return int
     */
    public function getRate($symbol = "USDNGN")
    {
        $array = str_split($symbol, 3);
        $from = $array[0];
        $to = $array[1];
        $rate = 1 / $this->usd_rates[$from] * $this->usd_rates[$to];

        return $rate;
    }

    public function getConversion()
    {
        //peg from base currency to dollar first
        $symbol = "{$this->from}{$this->to}";
        $exchange_rate = $this->getRate($symbol);
        $destination_value = $exchange_rate * $this->amount;

        $r_exchange_symbol = "{$this->to}{$this->from}";
        $r_exchange_rate = $this->getRate($r_exchange_symbol);


        $response = [
            'from' => $this->from,
            'to' => $this->to,
            "$symbol" => $exchange_rate,
            "{$this->from}" => $this->amount,
            "{$this->to}" => $destination_value,
            "destination_value" => $destination_value,
            "$r_exchange_symbol" => $r_exchange_rate,
        ];

        return $response;
    }


    /**
     * Set the value of to
     *
     * @return  self
     */
    public function setTo(string $to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Set the value of from
     *
     * @return  self
     */
    public function setFrom(string $from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Set the value of amount
     *
     * @return  self
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }
}
