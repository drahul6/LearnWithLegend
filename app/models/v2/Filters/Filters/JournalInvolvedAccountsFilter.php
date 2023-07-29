<?php

namespace v2\Filters\Filters;

use Filters\QueryFilter;
use Filters\Traits\RangeFilterable;
use User;
use v2\Models\Wallet\ChartOfAccount;

/**
 *
 */
class JournalInvolvedAccountsFilter extends QueryFilter
{
    use RangeFilterable;


    public function type($type = null)
    {
        if ($type == null) {
            return;
        }

        switch ($type) {
            case 'credit':
                $this->builder->where('credit', '>', 0);
                break;

            case 'debit':
                $this->builder->where('debit', '>', 0);
                break;

            default:
                # code...
                break;
        }
    }


    public function notes($notes = null)
    {

        if ($notes == null) {
            return;
        }
        $this->builder->where('description', 'like', "%$notes%");
    }




    /* --------------------------------------------------------- */
    public function chart_of_account($chart_of_account = null)
    {
        if ($chart_of_account == null) {
            return;
        }

        $account_ids = ChartOfAccount::WhereRaw(
            "account_name like ?
        OR account_code like ?
        OR account_number like ?
        OR id like ?
        ",
            array(
                '%' . $chart_of_account . '%',
                '%' . $chart_of_account . '%',
                '%' . $chart_of_account . '%',
                '%' . $chart_of_account . '%',
            )
        )->get()->pluck('id')->toArray();
    }

    public function name($name = null)
    {
        if ($name == null) {
            return;
        }

        $user_ids = User::WhereRaw(
            "firstname like ?
                                      OR lastname like ?
                                      OR middlename like ?
                                      OR username like ?
                                      OR email like ?
                                      OR phone like ?
                                      ",
            array(
                '%' . $name . '%',
                '%' . $name . '%',
                '%' . $name . '%',
                '%' . $name . '%',
                '%' . $name . '%',
                '%' . $name . '%',
            )
        )->get()->pluck('id')->toArray();

        //get ChartOfAccount ids
        //join to involved accounts
        //join to this journals

        $this->builder->whereIn('owner_id', $user_ids);
    }

    public function owner_id($id = null)
    {

        if ($id == null) {
            return;
        }

        $this->builder->where('owner_id', $id);
    }

    public function ref($ref = null)
    {

        if ($ref == null) {
            return;
        }

        $ref = explode(',', $ref);

        $this->builder->whereIn('id', $ref);
    }


    public function description($description = null)
    {

        if ($description == null) {
            return;
        }
        $this->builder->where('description', 'like', "%$description%");
    }


    public function amount($start = null, $end = null)
    {

        if (($start == null) && ($end == null)) {
            return;
        }

        $volume = compact('start', 'end');

        if ($end == null) {
            $end = $start;
        }

        $end = $end;
        $start = $start;

        $this->Range($start, $end, 'credit');
    }

    public function journal_date($start_date = null, $end_date = null)
    {

        if (($start_date == null) && ($end_date == null)) {
            return;
        }


        if ($end_date == null) {
            $end_date = date("Y-m-d");
        }

        $date = compact('start_date', 'end_date');

        $this->date($date, 'journal_date');
    }

    public function created_at($start_date = null, $end_date = null)
    {

        if (($start_date == null) && ($end_date == null)) {
            return;
        }


        if ($end_date == null) {
            $end_date = date("Y-m-d");
        }

        $date = compact('start_date', 'end_date');


        $this->date($date, 'created_at');
    }
}
