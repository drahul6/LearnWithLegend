<?php

namespace v2\Filters\Filters;

use User;
use Filters\QueryFilter;
use Filters\Traits\RangeFilterable;
use v2\Models\Wallet\ChartOfAccount;

/**
 *
 */
class JournalsFilter extends QueryFilter
{
    use RangeFilterable;

    public function tag($ref = null)
    {

        if ($ref == null) {
            return;
        }

        $ref = explode(',', $ref);

        $this->builder->where('tag', $ref);
    }


    public function sort($column = null, $type = null)
    {

        if ($column == null) {
            return;
        }

        $this->builder->orderBy($column, $type);
    }

    public function status($status = null)
    {
        if ($status == null) {
            return;
        }

        $this->builder->whereIn('status', explode(",", $status));


        if ($status == 1) {
            $this->builder->orWhere('status', null);
        }
    }

    public function balance_mode($status = null)
    {
        if ($status == null) {
            return;
        }

        switch ($status) {
            case 'book_balance':
                $allowed = [3];
                break;

            case 'available_balance':
                $allowed = [2, 3];
                break;

            default:
                # code...
                break;
        }

        $this->builder->whereIn('status', $allowed);
    }



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


    public function ref($ref = null)
    {

        if ($ref == null) {
            return;
        }

        $ref = explode(',', $ref);

        $this->builder->whereIn('id', $ref);
    }

    public function identifier($identifier = null)
    {

        if ($identifier == null) {
            return;
        }

        $this->builder->where('identifier', 'regexp', "$identifier");
    }


    public function notes($notes = null)
    {

        if ($notes == null) {
            return;
        }
        $this->builder->where('notes', 'like', "%$notes%");
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

        $this->Range($start, $end, 'amount');
    }

    public function journal_date($start_date = null, $end_date = null)
    {

        $period = ChartOfAccount::getAccountPeriod();
        if (($start_date == null) && ($end_date == null)) {
            $this->date($period, 'journal_date');
            return;
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
