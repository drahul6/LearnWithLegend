<?php

namespace v2\Models\Wallet\Classes;

use DateTime;
use DatePeriod;
use DateInterval;
use JsonSerializable;
use v2\Models\Wallet\GeneratedTransaction;

class TransactionGenerator implements JsonSerializable
{

    public $account;


    public $settings = [
        'credit' => null,
        'debit' => null,
        'net_balance' => null,
        'journal_date_range' => [
            'start_date' => null,
            'end_date' => null,
        ],
        'note' => ''
    ];

    private $transactions;

    private  $post_amounts;

    private  $post_schedule;

    private  $dates_of_journal;


    /**
     * Set the value of account
     *
     * @return  self
     */
    public function setAccount($account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Set the value of settings
     *
     * @return  self
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;

        return $this;
    }

    private function determinePostAmounts()
    {
        $net_balance = $this->settings['net_balance'];
        $double_net_balance = $net_balance * 3;
        if ($this->account->is_credit_balance()) {
            $credits = random_int($net_balance, $double_net_balance);
            $debits = $credits - $net_balance;
        } else {
            $debits = random_int($net_balance, $double_net_balance);
            $credits = $debits - $net_balance;
        }
        $this->post_amounts = compact('credits', 'debits');
        return $this;
    }

    private function determinePostDates()
    {
        $date_range = $this->settings['journal_date_range'];
        $start_date = new DateTime($date_range['start_date']);
        $end_date = new DateTime($date_range['end_date']);


        $period = new DatePeriod(
            new DateTime($start_date->format('Y-m-d')),
            new DateInterval('P1D'),
            new DateTime($end_date->format('Y-m-d'))
        );

        $dates_of_journal = [];
        foreach ($period as $key => $value) {
            $dates_of_journal[] = $value->format('Y-m-d');
        }
        $dates_of_journal[] = $end_date->format('Y-m-d');
        $this->dates_of_journal = $dates_of_journal;

        return $this;
    }


    private function getCreditSchedule()
    {
        $amounts = $this->post_amounts;
        $total_credits = $amounts['credits'];

        //credit
        $credits_chunk = [];
        $no_of_credits = $this->settings['credit'];

        if ($no_of_credits == 0) {
            return [];
        }
        
        do {

            if (count($credits_chunk) == ($no_of_credits - 1)) {
                $credits_chunk[] = $total_credits;
                break;
            }
            $credits_chunk[] = $chunk =  random_int(0, $total_credits);
            $total_credits -= $chunk;

            if (array_sum($credits_chunk) >= $amounts['credits']) {
                break;
            }
        } while ($total_credits > 0);

        return $credits_chunk;
    }


    private function getDebitSchedule()
    {
        $amounts = $this->post_amounts;
        $total_debits = $amounts['debits'];

        //credit
        $debits_chunk = [];
        $no_of_debits = $this->settings['debit'];
        
        if ($no_of_debits == 0) {
            return [];
        }
        
        do {

            if (count($debits_chunk) == ($no_of_debits - 1)) {
                $debits_chunk[] = $total_debits;
                break;
            }
            $debits_chunk[] = $chunk =  random_int(0, $total_debits);
            $total_debits -= $chunk;

            if (array_sum($debits_chunk) >= $amounts['debits']) {
                break;
            }
        } while ($total_debits > 0);

        return $debits_chunk;
    }

    public function determinePostSchedule()
    {
        $credits = $this->getCreditSchedule();
        $debits = $this->getDebitSchedule();
        $this->post_schedule = compact('credits','debits');

        return $this;
    }


    public function prepareJournal()
    {
        $dates_of_journal = $this->dates_of_journal;
        shuffle($dates_of_journal);

        $no_of_dates = count($dates_of_journal);

        $post_schedule = $this->post_schedule;

        
        $no_of_posts = count(array_merge($post_schedule['credits'], $post_schedule['debits']));

        if ($no_of_dates > $no_of_posts) {
            $journal_dates = array_slice($dates_of_journal,0, $no_of_posts);
        }else{

            $used_dates = $dates_of_journal;
            do {
                shuffle($dates_of_journal);
                $used_dates[] = $dates_of_journal[0];
                
            } while (count($used_dates) < $no_of_posts);
            $journal_dates = $used_dates;
        }

        sort($journal_dates);

        $credits = AccountManager::prepareLineItems('credit', $post_schedule['credits'], $this->account);
        $debits = AccountManager::prepareLineItems('debit', $post_schedule['debits'], $this->account);
        $line_items =  array_merge($credits, $debits);
        shuffle($line_items);


        $journals = compact('journal_dates', 'line_items');
        
        $this->journals = $journals;
        return $this;
    }

    

    public function prepareToPost()
    {
        /* Array
(
    [id] => 5
    [user_id] => 
    [company_id] => 1
    [amount] => 
    [notes] => note
    [currency] => 
    [status] => 1
    [attached_files] => 
    [journal_date] => 2021-05-13T20:51:14.027Z
    [created_at] => 2021-05-13 21:51:12
    [updated_at] => 2021-05-13 21:51:12
    [tag] => 
    [identifier] => 
    [createddate] => 2021-05-13
    [involved_accounts] => Array
        (
            [0] => Array
                (
                    [journal_id] => 5
                    [chart_of_account_id] => 
                    [chart_of_account_number] => 5003974849
                    [description] => note
                    [credit] => 
                    [debit] => 1000
                )

            [1] => Array
                (
                    [journal_id] => 5
                    [chart_of_account_id] => 
                    [chart_of_account_number] => 2001175692
                    [description] => note
                    [credit] => 1000
                    [debit] => 
                )

        )

    [published_status] => 1
) */
                    
        $postable_journals = AccountManager::preparePostableJournal($this->journals, $this->account);
              
        $this->postable_journals = $postable_journals;
    }


    public function jsonSerialize()
    {
        $data = [
            'postable_journals' => $this->postable_journals,
            'settings' => $this->settings,
            'account_id' => $this->account->id,
        ];

        return $data;
    }

    public function toJson()
    {
        return json_encode($this);
    }
    

    public function toArray()
    {
        return json_decode($this->toJson(), true);
    }
    
    public function save()
    {
        GeneratedTransaction::create([
                            'details'=>$this->toArray(),
                            'status' => 'draft'
                        ]);
        return;
    }

    public function generateTransactions()
    {
        $this->determinePostAmounts();
        $this->determinePostDates();
        $this->determinePostSchedule();
        // print_r($this->post_amounts);
        $this->prepareJournal();
        $this->prepareToPost();
        $this->save();
        // $this->postJournal();
    }



    public function postJournal()
    {
        AccountManager::postJournal($this->postable_journals, $this->account);
    }
}
