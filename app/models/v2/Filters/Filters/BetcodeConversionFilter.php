<?php


namespace v2\Filters\Filters;

use User;
use Filters\QueryFilter;
use Filters\Traits\RangeFilterable;


/**
 * 
 */
class BetcodeConversionFilter extends QueryFilter
{
    use RangeFilterable;





    public function take($take = null)
    {

        if ($take == null) {
            return;
        }
        $this->builder->take($take);
    }



    public function today($id = null)
    {

        if ($id == null) {
            return;
        }
        $today = date("Y-m-d");
        $this->builder->whereDate('updated_at', $today);
    }



    public function attempted_well($id = null)
    {

        if ($id == null) {
            return;
        }

        $this->builder->where('destination_code', "!=", null)->Attempted();
    }


    public function dest_bookie($id = null)
    {

        if ($id == null) {
            return;
        }
        $this->builder->where('dest_bookie_id', "$id");
    }


    public function home_bookie($id = null)
    {

        if ($id == null) {
            return;
        }
        $this->builder->where('home_bookie_id', "$id");
    }



    public function ref($ref = null)
    {

        if ($ref == null) {
            return;
        }
        $this->builder->where('id', 'like', "%$ref%");
    }



    public function bookies_train($bookies_train = null)
    {
        if ($bookies_train == null) {
            return;
        }
        $this->builder->where('bookies_train', "=",  "$bookies_train");
    }



    public function user($name = null)
    {
        if ($name == null) {
            return;
        }

        $user_ids = User::WhereRaw(
            "firstname like ? 
            OR lastname like ? 
            OR username like ? 
            OR email like ? 
            OR phone like ? 
            ",
            array(
                '%' . $name . '%',
                '%' . $name . '%',
                '%' . $name . '%',
                '%' . $name . '%',
                '%' . $name . '%'
            )
        )->get()->pluck('id')->toArray();

        $this->builder->whereIn('user_id', $user_ids);
    }




    public function status($status = null)
    {
        if ($status == null) {
            return;
        }

        $this->builder->where('status', '=', $status);
    }


    public function created_at($start_date = null, $end_date = null)
    {

        if (($start_date == null) &&  ($end_date == null)) {
            return;
        }

        $date = compact('start_date', 'end_date');

        if ($end_date == null) {
            $date = $start_date;
        }

        $this->date($date, 'created_at');
    }
}
